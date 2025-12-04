<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\BookingRevenueShare;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Services\EnhancedImageUploadService;
use App\Services\GoogleMeetService;
use App\Services\UserGoogleDriveService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use App\Support\ImageUploadConstraints;
use App\Support\HostMeetRedirectLinkFactory;
use App\Support\Timezones;
use App\Support\NotificationCopy;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkshopController extends Controller
{
    use ResolvesWorkshopRecordings;

    public function __construct(
        protected GoogleMeetService $googleMeetService,
        protected WorkshopMeetingAttendeeSyncService $meetingAttendeeSync,
        protected UserGoogleDriveService $userDriveService
    )
    {
    }

    public function index(): \Illuminate\View\View
    {
        $baseQuery = Workshop::query()->where('user_id', Auth::id());

        $workshopsQuery = (clone $baseQuery)
            ->withCount([
                'bookings as confirmed_bookings' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->orderByDesc('start_date');

        /** @var LengthAwarePaginator $workshops */
        $workshops = $workshopsQuery
            ->paginate(8)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'online' => (clone $baseQuery)->where('is_online', true)->count(),
            'drafts' => (clone $baseQuery)->where('is_active', false)->count(),
        ];

        return view('chef.workshops.index', compact('workshops', 'stats'));
    }

    public function recordings(): \Illuminate\View\View
    {
        $chefId = Auth::id();

        $workshops = Workshop::query()
            ->where('user_id', $chefId)
            ->where(function ($query) {
                $query->whereNotNull('recording_url')
                    ->orWhereNotNull('meeting_code')
                    ->orWhereNotNull('meeting_link');
            })
            ->orderByDesc('start_date')
            ->paginate(9)
            ->withQueryString();

        $chefUser = Auth::user();
        $userDriveEnabled = $chefUser?->hasGoogleDriveCredentials() ?? false;
        $driveEnabled = $userDriveEnabled;

        foreach ($workshops as $workshop) {
            $recordingUrl = $this->resolveRecordingUrl($workshop);
            $recordingPreview = $this->buildRecordingPreviewUrl($recordingUrl);

            if (! $recordingUrl && $workshop->meeting_code && $driveEnabled) {
                $recordingUrl = $this->findDriveRecordingForWorkshop($workshop, $chefUser);
                $recordingPreview = $this->buildRecordingPreviewUrl($recordingUrl);
            }

            $workshop->setAttribute('recording_source_url', $recordingUrl);
            $workshop->setAttribute('recording_preview_url', $recordingPreview);
            $workshop->setAttribute(
                'formatted_start_date',
                $workshop->start_date
                    ? $workshop->start_date->locale(app()->getLocale())->translatedFormat(__('chef.workshops.datetime_format'))
                    : null
            );
        }

        return view('chef.workshops.recordings', [
            'workshops' => $workshops,
            'driveEnabled' => $driveEnabled,
        ]);
    }

    public function earnings(): \Illuminate\View\View
    {
        $chefId = Auth::id();

        $paidBookingsQuery = WorkshopBooking::query()
            ->where('payment_status', 'paid')
            ->whereHas('workshop', fn ($query) => $query->where('user_id', $chefId));

        $lifetimeGross = (clone $paidBookingsQuery)->sum('payment_amount');
        $paidSeats = (clone $paidBookingsQuery)->count();
        $averageSeat = $paidSeats > 0 ? $lifetimeGross / $paidSeats : 0;

        $chefShareQuery = BookingRevenueShare::query()
            ->where('recipient_type', BookingRevenueShare::TYPE_CHEF)
            ->where('status', BookingRevenueShare::STATUS_DISTRIBUTED)
            ->where('recipient_id', $chefId);

        $lifetimeNet = (clone $chefShareQuery)->sum('amount');
        $averageNetSeat = $paidSeats > 0 ? $lifetimeNet / $paidSeats : 0;

        $now = now();
        $currentMonthRange = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $previousMonth = $now->copy()->subMonthNoOverflow();
        $previousMonthRange = [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()];

        $currentMonthGross = (clone $paidBookingsQuery)
            ->whereBetween('created_at', $currentMonthRange)
            ->sum('payment_amount');

        $previousMonthGross = (clone $paidBookingsQuery)
            ->whereBetween('created_at', $previousMonthRange)
            ->sum('payment_amount');

        $currentMonthNet = (clone $chefShareQuery)
            ->whereBetween('distributed_at', $currentMonthRange)
            ->sum('amount');

        $previousMonthNet = (clone $chefShareQuery)
            ->whereBetween('distributed_at', $previousMonthRange)
            ->sum('amount');

        $workshopBreakdown = Workshop::query()
            ->where('user_id', $chefId)
            ->withCount([
                'bookings as paid_seats' => fn ($query) => $query->where('payment_status', 'paid'),
            ])
            ->withSum([
                'bookings as paid_total' => fn ($query) => $query->where('payment_status', 'paid'),
            ], 'payment_amount')
            ->withSum([
                'revenueShares as chef_net_total' => function ($query) use ($chefId) {
                    $query->where('booking_revenue_shares.recipient_type', BookingRevenueShare::TYPE_CHEF)
                        ->where('booking_revenue_shares.status', BookingRevenueShare::STATUS_DISTRIBUTED)
                        ->where('booking_revenue_shares.recipient_id', $chefId);
                },
            ], 'amount')
            ->orderByDesc('paid_total')
            ->take(10)
            ->get();

        return view('chef.workshops.earnings', [
            'lifetimeGross' => $lifetimeGross,
            'lifetimeNet' => $lifetimeNet,
            'paidSeats' => $paidSeats,
            'averageSeat' => $averageSeat,
            'averageNetSeat' => $averageNetSeat,
            'currentMonthGross' => $currentMonthGross,
            'previousMonthGross' => $previousMonthGross,
            'currentMonthNet' => $currentMonthNet,
            'previousMonthNet' => $previousMonthNet,
            'workshopBreakdown' => $workshopBreakdown,
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        $calendarContext = $this->hostCalendarContext();

        return view('chef.workshops.create', [
            'forceAutoMeetingLinks' => $this->shouldForceAutoMeetingLinks(),
            'timezoneOptions' => Timezones::hostOptions(),
            'hostCalendarConnected' => $calendarContext['connected'],
            'hostCalendarEmail' => $calendarContext['email'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->enforceMeetingLinkPrivacyPolicy($request);

        $data = $this->validateWorkshop($request);
        $timezone = $this->resolveWorkshopTimezone($request);

        $workshop = new Workshop();
        $workshop->user_id = Auth::id();
        $this->fillWorkshopData($workshop, $data, $timezone);
        $this->handleImageUpload($request, $workshop);
        $this->applyMeetingProvider($request, $workshop, $data['meeting_link'] ?? null);
        $workshop->instructor = $data['instructor'] ?? Auth::user()->name;
        $workshop->instructor_bio = $data['instructor_bio'] ?? Auth::user()->chef_specialty_description;
        $workshop->instructor_avatar = Auth::user()->avatar;
        $workshop->is_featured = false;
        $workshop->save();
        $this->meetingAttendeeSync->sync($workshop);
        $this->notifyAdminsIfReviewRequired($workshop);

        $successMessage = $workshop->is_online
            ? __('flash.success.workshop.created_online_no_link')
            : __('flash.success.workshop.created_for_chef');

        return redirect()
            ->route('chef.workshops.index')
            ->with('success', $successMessage);
    }

    public function edit(Workshop $workshop): \Illuminate\View\View
    {
        $this->authorizeWorkshop($workshop);

        $calendarContext = $this->hostCalendarContext();

        return view('chef.workshops.edit', [
            'workshop' => $workshop,
            'forceAutoMeetingLinks' => $this->shouldForceAutoMeetingLinks(),
            'timezoneOptions' => Timezones::hostOptions(),
            'hostCalendarConnected' => $calendarContext['connected'],
            'hostCalendarEmail' => $calendarContext['email'],
        ]);
    }

    public function update(Request $request, Workshop $workshop): RedirectResponse
    {
        $this->authorizeWorkshop($workshop);

        $this->enforceMeetingLinkPrivacyPolicy($request);

        $data = $this->validateWorkshop($request, $workshop->id);
        $timezone = $this->resolveWorkshopTimezone($request, $workshop);
        $this->fillWorkshopData($workshop, $data, $timezone);
        $this->handleImageUpload($request, $workshop);
        $this->applyMeetingProvider($request, $workshop, $data['meeting_link'] ?? $workshop->meeting_link);
        $workshop->instructor = $data['instructor'] ?? $workshop->instructor ?? Auth::user()->name;
        $workshop->instructor_bio = $data['instructor_bio'] ?? $workshop->instructor_bio ?? Auth::user()->chef_specialty_description;
        $workshop->save();
        $this->meetingAttendeeSync->sync($workshop);

        return redirect()
            ->route('chef.workshops.index')
            ->with('success', __('workshops.flash.updated'));
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $this->authorizeWorkshop($workshop);

        if ($workshop->image) {
            EnhancedImageUploadService::deleteImage($workshop->image);
        }

        $workshop->delete();

        return redirect()
            ->route('chef.workshops.index')
            ->with('success', __('workshops.flash.deleted'));
    }

    public function join(Request $request, Workshop $workshop)
    {
        $this->authorizeWorkshop($workshop);

        if (!$workshop->is_online || !$workshop->meeting_link) {
            return redirect()
                ->route('chef.workshops.index')
                ->with('error', 'هذه الورشة ليست أونلاين أو أن رابط الاجتماع غير متاح.');
        }

        if ($workshop->meeting_provider !== 'google_meet') {
            return redirect()->away($workshop->meeting_link);
        }

        $currentUser = Auth::user();

        if ($redirect = $this->enforceHostJoinDeviceLock($request, $workshop)) {
            return $redirect;
        }

        $recentParticipants = $workshop->bookings()
            ->with('user:id,name,email')
            ->where('status', 'confirmed')
            ->orderByDesc('confirmed_at')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return view('chef.workshops.join', [
            'workshop' => $workshop,
            'user' => $currentUser,
            'meetingLink' => $workshop->meeting_link,
            'recentParticipants' => $recentParticipants,
            'startsAtIso' => optional($workshop->start_date)->toIso8601String(),
            'hostRedirectUrl' => HostMeetRedirectLinkFactory::make($workshop),
        ]);
    }

    public function startMeeting(Request $request, Workshop $workshop)
    {
        $this->authorizeWorkshop($workshop);
        $meetingLockSupported = $this->meetingLockSupported();

        if (!$request->expectsJson() && !$workshop->meeting_started_at) {
            $request->validate([
                'confirm_host' => ['accepted'],
            ], [
                'confirm_host.accepted' => 'يرجى تأكيد أنك المضيف قبل بدء الاجتماع.',
            ]);
        }

        if (!$workshop->is_online || !$workshop->meeting_link) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن بدء اجتماع لورشة غير أونلاين أو بدون رابط جاهز.',
            ], 422);
        }

        $alreadyStarted = (bool) $workshop->meeting_started_at;

        if (!$alreadyStarted) {
            $workshop->meeting_started_at = now();
            $workshop->meeting_started_by = Auth::id();
        }

        if (!$workshop->meeting_started_by) {
            $workshop->meeting_started_by = Auth::id();
        }

        if ($meetingLockSupported && $workshop->meeting_locked_at !== null) {
            $workshop->meeting_locked_at = null;
        }

        $dirtyAttributes = ['meeting_started_at', 'meeting_started_by'];

        if ($meetingLockSupported) {
            $dirtyAttributes[] = 'meeting_locked_at';
        }

        if ($workshop->isDirty($dirtyAttributes)) {
            $workshop->save();
        }

        $payload = [
            'success' => true,
            'already_started' => $alreadyStarted,
            'started_at' => $workshop->meeting_started_at->toIso8601String(),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $alreadyStarted ? 'تم بدء الاجتماع مسبقاً.' : 'تم فتح الغرفة ويمكن للمشاركين الدخول الآن.');
    }

    public function resetHostDeviceLock(Request $request, Workshop $workshop): RedirectResponse
    {
        $this->authorizeWorkshop($workshop);

        if (!$this->hostDeviceLockSupported()) {
            return redirect()
                ->route('chef.workshops.join', $workshop)
                ->with('info', 'لا يتطلب هذا الاجتماع إعادة تعيين للجهاز الموثوق.');
        }

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'يرجى إدخال كلمة المرور لتأكيد الهوية.',
            'password.current_password' => 'كلمة المرور المدخلة غير صحيحة.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('chef.workshops.index')
                ->withErrors($validator)
                ->withInput($request->except('password'))
                ->with([
                    'error' => 'تعذر تأكيد الهوية. يرجى المحاولة مرة أخرى.',
                    'host_join_device_reset_workshop_slug' => $workshop->slug,
                    'host_join_device_reset_workshop_title' => $workshop->title,
                    'host_join_device_reset_reason' => 'manual_reset_validation_failed',
                ]);
        }

        $this->clearHostDeviceLock($workshop);

        $cookieName = $this->getHostJoinDeviceCookieName($workshop);
        Cookie::queue(Cookie::forget(
            $cookieName,
            '/',
            config('session.domain')
        ));

        Log::info('Chef reset host workshop device lock.', [
            'workshop_id' => $workshop->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('chef.workshops.join', $workshop)
            ->with('success', 'تمت إعادة تعيين الجهاز الموثوق. يمكنك الآن فتح غرفة الورشة من هذا الجهاز.');
    }

    public function updatePresence(Request $request, Workshop $workshop)
    {
        $this->authorizeWorkshop($workshop);

        $validated = $request->validate([
            'state' => ['required', Rule::in(['online', 'offline'])],
        ]);

        $state = $validated['state'];
        $meetingStarted = (bool) $workshop->meeting_started_at;
        $meetingLockSupported = $this->meetingLockSupported();

        if (!$meetingLockSupported) {
            $dirty = false;

            if ($state === 'online' && !$meetingStarted) {
                $workshop->meeting_started_at = now();
                $workshop->meeting_started_by = Auth::id();
                $dirty = true;
            }

            if ($state === 'online' && !$workshop->meeting_started_by) {
                $workshop->meeting_started_by = Auth::id();
                $dirty = true;
            }

            if ($dirty) {
                $workshop->save();
            }

            return response()->json([
                'success' => true,
                'meeting_started' => (bool) $workshop->meeting_started_at,
                'meeting_locked' => false,
                'locked_at' => null,
            ]);
        }

        if (!$meetingStarted) {
            if ($state === 'offline' && $workshop->meeting_locked_at !== null) {
                $workshop->meeting_locked_at = null;
                $workshop->save();
            }

            return response()->json([
                'success' => true,
                'meeting_started' => false,
                'meeting_locked' => (bool) $workshop->meeting_locked_at,
                'locked_at' => optional($workshop->meeting_locked_at)->toIso8601String(),
            ]);
        }

        $lockChanged = false;

        if ($state === 'online') {
            if ($workshop->meeting_locked_at !== null) {
                $workshop->meeting_locked_at = null;
                $lockChanged = true;
            }
        } else {
            if ($workshop->meeting_locked_at === null) {
                $workshop->meeting_locked_at = now();
                $lockChanged = true;
            }
        }

        $starterChanged = false;

        if (!$workshop->meeting_started_by && $state === 'online') {
            $workshop->meeting_started_by = Auth::id();
            $starterChanged = true;
        }

        if ($lockChanged || $starterChanged) {
            $workshop->save();
        }

        return response()->json([
            'success' => true,
            'meeting_started' => true,
            'meeting_locked' => (bool) $workshop->meeting_locked_at,
            'locked_at' => optional($workshop->meeting_locked_at)->toIso8601String(),
        ]);
    }

    public function syncRecording(Request $request, Workshop $workshop): JsonResponse
    {
        $this->authorizeWorkshop($workshop);

        if (!$workshop->is_online) {
            return $this->recordingJsonError(
                __('chef.dashboard.workshops.host_room.recording.messages.offline'),
                422
            );
        }

        $user = Auth::user();
        $userDriveEnabled = $user?->hasGoogleDriveCredentials() ?? false;

        if (! $userDriveEnabled) {
            return $this->recordingJsonError(
                __('chef.dashboard.workshops.host_room.recording.messages.disabled'),
                503
            );
        }

        $meetingCode = $workshop->meeting_code ?: Workshop::extractMeetingCode($workshop->meeting_link);

        if (!$meetingCode) {
            return $this->recordingJsonError(
                __('chef.dashboard.workshops.host_room.recording.messages.missing_code'),
                422
            );
        }

        $attendeeEmails = $this->resolveAttendeeEmails($workshop);
        $recordingUrl = null;
        $file = null;

        if ($userDriveEnabled) {
            $file = $this->userDriveService->findRecordingByMeetingCode($user, $meetingCode);

            if ($file) {
                $this->userDriveService->shareWithEmails($user, $file, $attendeeEmails, true);
                $recordingUrl = $file->getWebViewLink()
                    ?: sprintf('https://drive.google.com/file/d/%s/preview', $file->getId());
            }
        }

        if (!$recordingUrl) {
            return $this->recordingJsonError(
                __('chef.dashboard.workshops.host_room.recording.messages.not_found'),
                404
            );
        }

        $normalizedUrl = trim($recordingUrl);
        $updated = false;

        if ($normalizedUrl !== '' && $workshop->recording_url !== $normalizedUrl) {
            $workshop->forceFill(['recording_url' => $normalizedUrl])->save();
            $updated = true;
        }

        $message = $updated
            ? __('chef.dashboard.workshops.host_room.recording.messages.synced')
            : __('chef.dashboard.workshops.host_room.recording.messages.already_synced');

        return response()->json([
            'success' => true,
            'recording_url' => $normalizedUrl,
            'updated' => $updated,
            'message' => $message,
        ]);
    }

    protected function findDriveRecordingForWorkshop(Workshop $workshop, ?User $owner = null): ?string
    {
        $owner = $owner ?: $workshop->chef;
        $meetingCode = $workshop->meeting_code ?: Workshop::extractMeetingCode($workshop->meeting_link);

        if (! $meetingCode) {
            return null;
        }

        if ($owner && $owner->hasGoogleDriveCredentials()) {
            $url = $this->userDriveService->findRecordingUrl($owner, $meetingCode);

            if ($url) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Resolve confirmed attendee emails for sharing a Drive file.
     *
     * @return array<int, string>
     */
    protected function resolveAttendeeEmails(Workshop $workshop): array
    {
        return $workshop->bookings()
            ->where('status', 'confirmed')
            ->with(['user:id,email,google_email,google_drive_email,google_calendar_email'])
            ->get()
            ->map(function (WorkshopBooking $booking) {
                return $booking->user?->preferredGoogleDriveEmail() ?? $booking->user?->email;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function recordingJsonError(string $message, int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public function generateMeetingLink(Request $request)
    {
        $currentUser = Auth::user();

        abort_unless($currentUser, 403);

        if (!$currentUser->hasGoogleCalendarCredentials()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى ربط حساب Google Calendar أولاً قبل توليد رابط الاجتماع.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'host_timezone' => [
                'nullable',
                'string',
                Rule::in(array_keys(Timezones::hostOptions())),
            ],
        ]);

        $startsAt = null;

        if (! empty($validated['start_date'])) {
            try {
                $timezone = $this->resolveWorkshopTimezone($request);
                $startsAt = Carbon::parse($validated['start_date'], $timezone);
            } catch (\Throwable $exception) {
                $startsAt = null;
            }
        }

        $hostCredentials = $currentUser->googleMeetCredentials();

        try {
            $meeting = $this->googleMeetService->createMeeting(
                $validated['title'],
                Auth::id(),
                $startsAt,
                null,
                null,
                [],
                [
                    'email' => $currentUser->preferredGoogleEmail(),
                    'displayName' => $currentUser->name,
                ],
                $hostCredentials
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'تعذر إنشاء اجتماع Google Meet في الوقت الحالي.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'meeting_link' => $meeting['meeting_link'],
            'event_id' => $meeting['event_id'] ?? null,
            'starts_at' => optional($meeting['starts_at'] ?? null)?->toIso8601String(),
            'room' => $meeting['room'] ?? null,
            'passcode' => $meeting['passcode'] ?? null,
        ]);
    }

    protected function enforceHostJoinDeviceLock(Request $request, Workshop $workshop): ?RedirectResponse
    {
        if (!$this->hostDeviceLockSupported()) {
            return null;
        }

        $currentUser = Auth::user();

        if ($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin()) {
            return null;
        }

        $cookieName = $this->getHostJoinDeviceCookieName($workshop);
        $storedTokenHash = $workshop->host_join_device_token;
        $fingerprint = $this->makeDeviceFingerprint($request);

        if ($storedTokenHash) {
            $cookieToken = $request->cookie($cookieName);

            if (!is_string($cookieToken) || $cookieToken === '') {
                $this->rememberHostJoinDevice($workshop, $request, $fingerprint);
                return null;
            }

            $hashedCookieToken = hash('sha256', $cookieToken);

            if (!hash_equals($storedTokenHash, $hashedCookieToken)) {
                $this->rememberHostJoinDevice($workshop, $request, $fingerprint);
                return null;
            }

            if (!empty($workshop->host_join_device_fingerprint) && !hash_equals($workshop->host_join_device_fingerprint, $fingerprint)) {
                $this->rememberHostJoinDevice($workshop, $request, $fingerprint);
                return null;
            }

            return null;
        }

        $this->rememberHostJoinDevice($workshop, $request, $fingerprint);

        return null;
    }

    protected function getHostJoinDeviceCookieName(Workshop $workshop): string
    {
        return 'wasfah_host_workshop_device_' . strtolower((string) $workshop->id);
    }

    protected function makeDeviceFingerprint(Request $request): string
    {
        $userAgent = (string) $request->userAgent();
        $acceptLanguage = (string) $request->header('accept-language', '');

        return hash('sha256', $userAgent . '|' . $acceptLanguage);
    }

    protected function truncateUserAgent(?string $userAgent): string
    {
        $agent = (string) $userAgent;

        if (function_exists('mb_substr')) {
            return mb_substr($agent, 0, 1024);
        }

        return substr($agent, 0, 1024);
    }

    protected function rememberHostJoinDevice(Workshop $workshop, Request $request, string $fingerprint): void
    {
        $plainToken = Str::random(64);
        $firstJoinedAt = $workshop->host_first_joined_at ?: now();
        $cookieName = $this->getHostJoinDeviceCookieName($workshop);

        $workshop->forceFill([
            'host_first_joined_at' => $firstJoinedAt,
            'host_join_device_token' => hash('sha256', $plainToken),
            'host_join_device_fingerprint' => $fingerprint,
            'host_join_device_ip' => $request->ip(),
            'host_join_device_user_agent' => $this->truncateUserAgent($request->userAgent()),
        ])->save();

        Cookie::queue(
            cookie(
                $cookieName,
                $plainToken,
                60 * 24 * 365,
                '/',
                config('session.domain'),
                config('session.secure', false),
                true,
                false,
                config('session.same_site', 'lax')
            )
        );
    }

    protected function hostDeviceLockSupported(): bool
    {
        static $supported;

        if ($supported === null) {
            $supported = Schema::hasColumns('workshops', [
                'host_join_device_token',
                'host_join_device_fingerprint',
                'host_join_device_ip',
                'host_join_device_user_agent',
            ]);
        }

        return $supported;
    }

    protected function clearHostDeviceLock(Workshop $workshop, bool $persist = true): void
    {
        if (!$this->hostDeviceLockSupported()) {
            return;
        }

        $workshop->host_first_joined_at = null;
        $workshop->host_join_device_token = null;
        $workshop->host_join_device_fingerprint = null;
        $workshop->host_join_device_ip = null;
        $workshop->host_join_device_user_agent = null;

        if ($persist) {
            $workshop->save();
        }
    }

    protected function meetingLockSupported(): bool
    {
        static $supported;

        if ($supported === null) {
            $supported = Schema::hasColumns('workshops', [
                'meeting_started_at',
                'meeting_locked_at',
            ]);
        }

        return $supported;
    }

    protected function validateWorkshop(Request $request, ?int $workshopId = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'content' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:120'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration' => ['required', 'integer', 'min:30', 'max:180'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['USD'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_online' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'instructor' => ['nullable', 'string', 'max:255'],
            'instructor_bio' => ['nullable', 'string'],
            'what_you_will_learn' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'materials_needed' => ['nullable', 'string'],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'recording_url' => ['nullable', 'url', 'max:512'],
            'image' => array_merge(['nullable'], ImageUploadConstraints::rules()),
            'remove_image' => ['sometimes', 'boolean'],
            'auto_generate_meeting' => ['sometimes', 'boolean'],
            'host_timezone' => [
                'nullable',
                'string',
                Rule::in(array_keys(Timezones::options())),
            ],
        ];

        $messages = ImageUploadConstraints::messages('image', [
            'ar' => 'صورة الورشة',
            'en' => 'workshop image',
        ]);

        $data = $request->validate($rules, $messages);

        $autoGenerateRequested = $request->boolean('auto_generate_meeting');
        $autoGenerationAvailable = $autoGenerateRequested
            && ($this->googleMeetIntegrationEnabled() || (Auth::user()?->hasGoogleCalendarCredentials() ?? false));

        if (!empty($data['is_online']) && empty($data['meeting_link']) && !$autoGenerationAvailable) {
            throw ValidationException::withMessages([
                'meeting_link' => 'يرجى إدخال رابط اجتماع أو اختيار خيار توليد رابط Google Meet تلقائياً.',
            ]);
        }

        if (empty($data['is_online']) && empty($data['location'])) {
            throw ValidationException::withMessages([
                'location' => 'يرجى تحديد موقع الورشة الحضورية.',
            ]);
        }

        return $data;
    }

    protected function notifyAdminsIfReviewRequired(Workshop $workshop): void
    {
        if (!$this->workshopRequiresAdminReview($workshop)) {
            return;
        }

        $adminIds = User::query()
            ->where(function ($query) {
                $query->where('is_admin', true)
                    ->orWhere('role', User::ROLE_ADMIN);
            })
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            Log::warning('No admin recipients found for workshop review notification.', [
                'workshop_id' => $workshop->id,
            ]);

            return;
        }

        $workshop->loadMissing('chef');
        $chefName = $workshop->chef?->name ?? 'One of our chefs';
        $reviewUrl = route('admin.workshops.show', $workshop);
        [$title, $message] = NotificationCopy::workshopReviewRequired($chefName, $workshop);

        foreach ($adminIds as $adminId) {
            Notification::createNotification(
                $adminId,
                'workshop_review_required',
                $title,
                $message,
                [
                    'workshop_id' => $workshop->id,
                    'workshop_slug' => $workshop->slug,
                    'review_url' => $reviewUrl,
                    'chef_id' => $workshop->user_id,
                    'action_url' => $reviewUrl,
                ]
            );
        }
    }

    protected function workshopRequiresAdminReview(Workshop $workshop): bool
    {
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->isAdmin()) {
            return false;
        }

        return !$workshop->is_active;
    }

    protected function fillWorkshopData(Workshop $workshop, array $data, string $hostTimezone): void
    {
        $startDateUtc = $this->convertLocalInputToUtc($data['start_date'], $hostTimezone);
        $endDateUtc = $this->convertLocalInputToUtc($data['end_date'], $hostTimezone);
        $registrationDeadlineUtc = $this->calculateRegistrationDeadline($startDateUtc);

        $workshop->fill([
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'] ?? null,
            'category' => $data['category'],
            'level' => $data['level'],
            'duration' => $data['duration'],
            'max_participants' => $data['max_participants'],
            'price' => $data['price'],
            'currency' => $data['currency'],
            'host_timezone' => $hostTimezone,
            'start_date' => $startDateUtc,
            'end_date' => $endDateUtc,
            'registration_deadline' => $registrationDeadlineUtc,
            'location' => $data['location'] ?? null,
            'address' => $data['address'] ?? null,
            'what_you_will_learn' => $data['what_you_will_learn'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'materials_needed' => $data['materials_needed'] ?? null,
            'recording_url' => $data['recording_url'] ?? null,
            'is_online' => (bool) $data['is_online'],
            'is_active' => isset($data['is_active'])
                ? (bool) $data['is_active']
                : true,
        ]);

        if (!$workshop->is_online && empty($workshop->location)) {
            $workshop->location = 'سيتم تحديد الموقع لاحقاً';
        }
    }

    protected function convertLocalInputToUtc(null|string $value, string $timezone): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value, $timezone)->setTimezone('UTC');
        } catch (\Throwable $exception) {
            Log::warning('Failed to convert workshop datetime to UTC.', [
                'input' => $value,
                'timezone' => $timezone,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function calculateRegistrationDeadline(?Carbon $startDateUtc): ?Carbon
    {
        if (! $startDateUtc instanceof Carbon) {
            return null;
        }

        return $startDateUtc->copy()->subMinutes(2);
    }

    protected function resolveWorkshopTimezone(Request $request, ?Workshop $workshop = null): string
    {
        $user = Auth::user();
        $timezone = null;
        $candidates = [
            $request->input('host_timezone'),
            $user?->timezone,
            $workshop?->host_timezone,
            $request->cookie('user_timezone'),
        ];

        foreach ($candidates as $candidate) {
            if (Timezones::isAllowedHostTimezone($candidate)) {
                $timezone = $candidate;
                break;
            }
        }

        if (! $timezone) {
            $timezone = Timezones::defaultHostTimezone();
        }

        if (
            $user
            && method_exists($user, 'isChef')
            && $user->isChef()
            && $user->timezone !== $timezone
            && $this->userTimezoneColumnExists($user)
        ) {
            $user->forceFill(['timezone' => $timezone])->save();
        }

        return $timezone;
    }

    protected function userTimezoneColumnExists(?User $user): bool
    {
        static $checked = false;
        static $exists = false;

        if ($checked) {
            return $exists;
        }

        if (! $user) {
            return false;
        }

        try {
            $exists = Schema::hasColumn($user->getTable(), 'timezone');
        } catch (\Throwable $exception) {
            Log::warning('Failed to verify timezone column existence on users table.', [
                'error' => $exception->getMessage(),
            ]);
            $exists = false;
        }

        $checked = true;

        return $exists;
    }

    protected function handleImageUpload(Request $request, Workshop $workshop): void
    {
        if ($request->hasFile('image')) {
            if ($workshop->image) {
                EnhancedImageUploadService::deleteImage($workshop->image);
            }

            $uploadResult = EnhancedImageUploadService::uploadImage(
                $request->file('image'),
                'workshops',
                85,
                1200,
                1200
            );

            if (!$uploadResult['success']) {
                throw ValidationException::withMessages([
                    'image' => $uploadResult['error'] ?? 'تعذر رفع الصورة، يرجى المحاولة مرة أخرى.',
                ]);
            }

            $workshop->image = $uploadResult['path'];
        } elseif ($request->boolean('remove_image') && $workshop->image) {
            EnhancedImageUploadService::deleteImage($workshop->image);
            $workshop->image = null;
        }
    }

    protected function applyMeetingProvider(Request $request, Workshop $workshop, ?string $inputLink = null): void
    {
        $supportsHostLock = $this->hostDeviceLockSupported();
        $supportsMeetingLock = $this->meetingLockSupported();
        $hostUser = $workshop->chef ?? Auth::user();
        $hostCredentials = $hostUser?->googleMeetCredentials($workshop->meeting_calendar_id);
        $hasHostCredentials = !empty($hostCredentials);

        if (!$workshop->is_online) {
            $workshop->meeting_link = null;
            $workshop->meeting_provider = 'manual';
            $workshop->meeting_started_at = null;
            $workshop->meeting_started_by = null;
            $workshop->meeting_code = null;
            $workshop->meeting_event_id = null;
            $workshop->meeting_calendar_id = null;
            $workshop->meeting_conference_id = null;
            if ($supportsMeetingLock) {
                $workshop->meeting_locked_at = null;
            }

            if ($supportsHostLock) {
                $workshop->host_first_joined_at = null;
                $workshop->host_join_device_token = null;
                $workshop->host_join_device_fingerprint = null;
                $workshop->host_join_device_ip = null;
                $workshop->host_join_device_user_agent = null;
            }
            return;
        }

        $googleMeetEnabled = $this->googleMeetIntegrationEnabled() || $hasHostCredentials;
        $autoGenerate = $googleMeetEnabled && $request->boolean('auto_generate_meeting');

        if ($this->shouldForceAutoMeetingLinks() && $googleMeetEnabled && $workshop->is_online) {
            $autoGenerate = true;
            $inputLink = null;
        }

        $workshop->meeting_started_at = null;
        $workshop->meeting_started_by = null;
        if ($supportsMeetingLock) {
            $workshop->meeting_locked_at = null;
        }

        if ($supportsHostLock) {
            $workshop->host_first_joined_at = null;
            $workshop->host_join_device_token = null;
            $workshop->host_join_device_fingerprint = null;
            $workshop->host_join_device_ip = null;
            $workshop->host_join_device_user_agent = null;
        }

        if ($autoGenerate) {
            $hostAttendee = $workshop->hostAttendeePayload();
            $durationMinutes = is_numeric($workshop->duration) ? (int) $workshop->duration : null;

            if (!$hasHostCredentials) {
                throw ValidationException::withMessages([
                    'meeting_link' => 'يرجى ربط حساب Google Calendar الخاص بك لإنشاء رابط الاجتماع من حسابك.',
                ]);
            }

            try {
                $meeting = $this->googleMeetService->createMeeting(
                    $workshop->title,
                    Auth::id(),
                    $workshop->start_date instanceof Carbon
                        ? $workshop->start_date
                        : Carbon::parse($workshop->start_date),
                    $durationMinutes,
                    null,
                    $hostAttendee ? [$hostAttendee] : [],
                    $hostAttendee,
                    $hostCredentials
                );
            } catch (\Throwable $exception) {
                Log::warning('Failed to auto-create Google Meet link.', [
                    'workshop_id' => $workshop->id ?? null,
                    'user_id' => Auth::id(),
                    'error' => $exception->getMessage(),
                ]);

                $error = Str::lower($exception->getMessage() ?? '');
                $message = 'تعذّر إنشاء اجتماع Google Meet. أضف الرابط يدويًا أو أعد المحاولة لاحقًا. ' . $exception->getMessage();

                if (Str::contains($error, ['invalid_grant', 'unauthorized_client'])) {
                    Auth::user()?->disconnectGoogleCalendar();
                    $message = 'انتهت صلاحية ربط Google Calendar. لقد قمنا بفصل الحساب، يرجى إعادة ربطه من النموذج أدناه ثم المحاولة مرة أخرى.';
                }

                throw ValidationException::withMessages([
                    'meeting_link' => $message,
                ]);
            }

            $workshop->meeting_link = $meeting['meeting_link'];
            $workshop->meeting_provider = $meeting['provider'] ?? 'google_meet';
            $workshop->location = $workshop->location ?: 'أونلاين عبر Google Meet';
            $workshop->meeting_code = Workshop::extractMeetingCode($workshop->meeting_link);
            $workshop->meeting_event_id = $meeting['event_id'] ?? null;
            $workshop->meeting_calendar_id = $meeting['calendar_id'] ?? ($hostCredentials['calendar_id'] ?? null);
            $workshop->meeting_conference_id = $meeting['conference_id'] ?? null;
        } elseif (!empty($inputLink)) {
            $workshop->meeting_link = $inputLink;
            $workshop->meeting_provider = str_contains((string) $inputLink, 'meet.google.com') ? 'google_meet' : 'manual';
            $workshop->meeting_code = Workshop::extractMeetingCode($workshop->meeting_link);
            $workshop->meeting_event_id = null;
            $workshop->meeting_calendar_id = null;
            $workshop->meeting_conference_id = null;
        } else {
            throw ValidationException::withMessages([
                'meeting_link' => 'يرجى إدخال رابط الاجتماع أو تفعيل خيار توليد رابط Google Meet.',
            ]);
        }
    }

    protected function authorizeWorkshop(Workshop $workshop): void
    {
        if ($workshop->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الورشة.');
        }
    }

    protected function enforceMeetingLinkPrivacyPolicy(Request $request): void
    {
        $googleMeetEnabled = $this->googleMeetIntegrationEnabled()
            || (Auth::user()?->hasGoogleCalendarCredentials() ?? false);

        if (!$googleMeetEnabled) {
            $request->merge([
                'auto_generate_meeting' => 0,
            ]);

            return;
        }

        if ($request->boolean('is_online') && $this->shouldForceAutoMeetingLinks()) {
            $request->merge([
                'auto_generate_meeting' => 1,
                'meeting_link' => null,
            ]);
        }
    }

    protected function hostCalendarContext(): array
    {
        $user = Auth::user();

        return [
            'connected' => $user?->hasGoogleCalendarCredentials() ?? false,
            'email' => $user?->google_calendar_email ?? $user?->preferredGoogleEmail(),
        ];
    }

    protected function googleMeetIntegrationEnabled(): bool
    {
        return $this->googleMeetService->isEnabled();
    }

    protected function shouldForceAutoMeetingLinks(): bool
    {
        return $this->googleMeetIntegrationEnabled()
            || (Auth::user()?->hasGoogleCalendarCredentials() ?? false);
    }
}
