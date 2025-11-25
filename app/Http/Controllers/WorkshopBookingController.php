<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Models\Notification;
use App\Services\GoogleMeetService;
use App\Services\WorkshopLinkSecurityService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Support\GoogleMeetAccountChooser;
use App\Support\NotificationCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkshopBookingController extends Controller
{
    public function __construct(
        protected WorkshopLinkSecurityService $linkSecurity,
        protected GoogleMeetService $googleMeetService,
        protected WorkshopMeetingAttendeeSyncService $meetingAttendeeSyncService,
    ) {
    }

    public function store(Request $request)
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $workshop = Workshop::findOrFail($request->workshop_id);

        // التحقق من أن المستخدم مسجل دخول
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // التحقق من أن الورشة نشطة ومتاحة للحجز
        if (!$workshop->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'الورشة غير متاحة للحجز'
            ], 400);
        }

        if ($workshop->is_fully_booked) {
            return response()->json([
                'success' => false,
                'message' => 'الورشة مكتملة العدد'
            ], 400);
        }

        if (!$workshop->is_registration_open) {
            return response()->json([
                'success' => false,
                'message' => 'انتهى موعد التسجيل'
            ], 400);
        }

        // التحقق من عدم وجود حجز سابق لنفس المستخدم
        $existingBooking = WorkshopBooking::where('workshop_id', $workshop->id)
                                         ->where('user_id', Auth::id())
                                         ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'لديك حجز سابق في هذه الورشة'
            ], 400);
        }

        // إنشاء الحجز
        $booking = WorkshopBooking::create([
            'workshop_id' => $workshop->id,
            'user_id' => Auth::id(),
            'status' => 'pending',
            'booking_date' => now(),
            'payment_status' => 'pending',
            'payment_amount' => $workshop->price,
            'payment_currency' => $workshop->currency,
            'notes' => $request->notes,
        ]);

        [$notificationTitle, $notificationMessage] = NotificationCopy::bookingPending($booking, $workshop);

        // إنشاء إشعار للمستخدم
        Notification::createNotification(
            Auth::id(),
            'workshop_booking',
            $notificationTitle,
            $notificationMessage,
            [
                'workshop_id' => $workshop->id,
                'workshop_slug' => $workshop->slug,
                'booking_id' => $booking->id,
                'action_url' => route('bookings.show', ['booking' => $booking->id]),
            ]
        );

        // سيتم تحديث عدد الحجوزات تلقائياً عبر event listeners

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الحجز بنجاح. يمكنك الآن الدخول إلى حسابك الشخصي لرؤية الورشات المحجوزة.',
            'booking' => $booking
        ]);
    }

    public function index()
    {
        $bookings = WorkshopBooking::with(['workshop', 'user'])
                                  ->where('user_id', Auth::id())
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function show(WorkshopBooking $booking)
    {
        $this->ensureBookingOwner($booking);
        return view('bookings.show', compact('booking'));
    }

    public function cancel(WorkshopBooking $booking)
    {
        $this->ensureBookingOwner($booking);

        // التحقق من إمكانية الإلغاء
        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'الحجز ملغي بالفعل'
            ], 400);
        }

        if ($booking->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء حجز مؤكد'
            ], 400);
        }

        // إلغاء الحجز
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'تم الإلغاء من قبل المستخدم'
        ]);

        // سيتم تحديث عدد الحجوزات تلقائياً عبر event listeners

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الحجز بنجاح'
        ]);
    }

    /**
     * عرض غرفة الاجتماع داخل موقع وصفة دون مشاركة الرابط الخارجي.
     */
    public function join(Request $request, WorkshopBooking $booking)
    {
        if (!$request->hasValidSignature()) {
            if (!$request->query->has('signature')) {
                return redirect()->to(
                    $this->linkSecurity->makeParticipantJoinUrl($booking)
                );
            }

            abort(403, 'رابط الانضمام غير صالح أو منتهي الصلاحية.');
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'يجب تسجيل الدخول للوصول إلى غرفة الورشة.');
        }

        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        $booking->load('workshop');
        $workshop = $booking->workshop;

        if ($booking->status !== 'confirmed') {
            if ($user && $booking->user_id === $user->id) {
                return redirect()
                    ->route('bookings.show', $booking)
                    ->with('error', 'لا يمكنك الدخول للورشة قبل تأكيد الحجز.');
            }

            abort(403, 'لا يمكنك الدخول للورشة قبل تأكيد الحجز.');
        }

        if (!$workshop || !$workshop->is_online || !$workshop->meeting_link) {
            if ($user && $booking->user_id === $user->id) {
                return redirect()
                    ->route('bookings.show', $booking)
                    ->with('error', 'هذه الورشة ليست أونلاين أو أن رابط الاجتماع غير متاح حالياً.');
            }

            abort(404, 'هذه الورشة ليست أونلاين أو أن رابط الاجتماع غير متاح حالياً.');
        }

        if ($workshop->meeting_provider !== 'google_meet') {
            return redirect()->away($workshop->meeting_link);
        }

        $workshop->loadMissing('chef');
        $workshop->loadCount([
            'bookings as confirmed_bookings_count' => fn ($query) => $query->where('status', 'confirmed'),
        ]);

        if ($redirect = $this->enforceJoinDeviceLock($request, $booking)) {
            return $redirect;
        }

        $hostName = $workshop->instructor ?: optional($workshop->chef)->name;
        $requestedName = trim((string) $request->query('name', ''));
        $guestDisplayName = $requestedName !== '' ? $requestedName : 'ضيف وصفة';
        $shouldPromptForDisplayName = !$user;
        $effectiveName = $user?->name ?? $guestDisplayName;

        $meetingLockSupported = $this->meetingLockSupported();
        $meetingStarted = (bool) $workshop->meeting_started_at;
        $meetingLocked = $meetingLockSupported ? (bool) $workshop->meeting_locked_at : false;

        return view('bookings.join', [
            'booking' => $booking,
            'workshop' => $workshop,
            'user' => $user,
            'hostName' => $hostName,
            'startsAtIso' => optional($workshop->start_date)->toIso8601String(),
            'meetingStartedAtIso' => optional($workshop->meeting_started_at)->toIso8601String(),
            'meetingLockedAtIso' => $meetingLockSupported
                ? optional($workshop->meeting_locked_at)->toIso8601String()
                : null,
            'isMeetingLocked' => $meetingLocked,
            'meetingLocked' => $meetingLocked,
            'meetingStarted' => $meetingStarted,
            'participantName' => $effectiveName,
            'participantEmail' => $user?->email,
            'shouldPromptForDisplayName' => $shouldPromptForDisplayName,
            'guestDisplayName' => $guestDisplayName,
            'supportsMeetingLock' => $meetingLockSupported,
            'secureLaunchUrl' => $this->linkSecurity->makeParticipantLaunchUrl($booking),
            'secureJoinUrl' => $this->linkSecurity->makeParticipantJoinUrl($booking),
            'secureStatusUrl' => $this->linkSecurity->makeParticipantStatusUrl($booking),
        ]);
    }

    /**
     * Redirect the participant to the external meeting provider without exposing the raw URL.
     */
    public function launch(Request $request, WorkshopBooking $booking)
    {
        if (!$request->hasValidSignature()) {
            if (!$request->query->has('signature')) {
                return redirect()->to(
                    $this->linkSecurity->makeParticipantLaunchUrl($booking)
                );
            }

            abort(403, 'رابط فتح الاجتماع غير صالح أو منتهي الصلاحية.');
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'يجب تسجيل الدخول للوصول إلى غرفة الورشة.');
        }

        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        $booking->load(['workshop', 'user']);
        $workshop = $booking->workshop;

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'لا يمكنك الدخول للورشة قبل تأكيد الحجز.');
        }

        if (!$workshop || !$workshop->is_online || !$workshop->meeting_link) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'هذه الورشة ليست أونلاين أو أن رابط الاجتماع غير متاح حالياً.');
        }

        if ($redirect = $this->enforceJoinDeviceLock($request, $booking)) {
            return $redirect;
        }

        $isGoogleMeet = $workshop->meeting_provider === 'google_meet';

        if ($isGoogleMeet) {
            $meetingLockSupported = $this->meetingLockSupported();
            $meetingLocked = $meetingLockSupported ? (bool) $workshop->meeting_locked_at : false;

            if ($meetingLocked) {
                return redirect()
                    ->to($this->linkSecurity->makeParticipantJoinUrl($booking))
                    ->with('error', 'تم قفل الاجتماع من قبل المضيف. يرجى انتظار السماح بالدخول.');
            }

            if (!$workshop->meeting_started_at) {
                return redirect()
                    ->to($this->linkSecurity->makeParticipantJoinUrl($booking))
                    ->with('error', 'لم يبدأ المضيف الاجتماع بعد.');
            }

            if ($trustedRedirect = $this->attemptTrustedGoogleRedirect($booking, $workshop)) {
                return $trustedRedirect;
            }
        }

        return redirect()->away($workshop->meeting_link);
    }

    public function status(Request $request, WorkshopBooking $booking)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'meeting_started' => false,
                'started_at' => null,
                'message' => 'يجب تسجيل الدخول للوصول إلى حالة الورشة.',
            ], 401);
        }

        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'meeting_started' => false,
                'started_at' => null,
            ], 403);
        }

        $booking->load('workshop');
        $workshop = $booking->workshop;

        return response()->json([
            'meeting_started' => (bool) ($workshop?->meeting_started_at),
            'started_at' => $workshop?->meeting_started_at?->toIso8601String(),
            'meeting_locked' => $this->meetingLockSupported() && $workshop
                ? (bool) $workshop->meeting_locked_at
                : false,
            'locked_at' => $this->meetingLockSupported() && $workshop
                ? $workshop->meeting_locked_at?->toIso8601String()
                : null,
        ]);
    }

    protected function attemptTrustedGoogleRedirect(
        WorkshopBooking $booking,
        Workshop $workshop
    ): ?\Illuminate\Http\RedirectResponse {
        $hostCredentials = $workshop->hostGoogleMeetCredentials();

        if (
            (!$this->googleMeetService->isEnabled() && !$hostCredentials)
            || !filter_var($workshop->meeting_link, FILTER_VALIDATE_URL)
        ) {
            return null;
        }

        $meetHost = parse_url($workshop->meeting_link, PHP_URL_HOST);

        if (!is_string($meetHost) || !str_contains($meetHost, 'meet.google.com')) {
            return null;
        }

        $participant = $booking->user;

        if (!$participant) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'لا يمكن التحقق من بيانات الحجز. يرجى تسجيل الدخول من جديد.');
        }

        $participantEmail = $participant->preferredGoogleEmail();

        if (!$participantEmail) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'يرجى تحديث بريدك الإلكتروني المخصص لـ Google قبل محاولة الانضمام.');
        }

        $eventId = trim((string) $workshop->meeting_event_id);
        $calendarId = $workshop->meeting_calendar_id
            ?: ($hostCredentials['calendar_id'] ?? null)
            ?: config('services.google_meet.calendar_id')
            ?: config('services.google_meet.organizer_email');

        if ($eventId === '' || !$calendarId) {
            return null;
        }

        $attendeeStatus = $this->googleMeetService->eventHasAttendee(
            $eventId,
            $participantEmail,
            $calendarId,
            $hostCredentials
        );

        if ($attendeeStatus === false) {
            $ensured = $this->googleMeetService->ensureAttendeePresent(
                $eventId,
                [
                    'email' => $participantEmail,
                    'displayName' => $participant->name,
                ],
                $calendarId,
                $hostCredentials
            );

            if (!$ensured) {
                $this->meetingAttendeeSyncService->sync($workshop);
                $attendeeStatus = $this->googleMeetService->eventHasAttendee(
                    $eventId,
                    $participantEmail,
                    $calendarId,
                    $hostCredentials
                );
            } else {
                $attendeeStatus = true;
            }
        }

        if ($attendeeStatus === false) {
            return redirect()
                ->to($this->linkSecurity->makeParticipantJoinUrl($booking))
                ->with('error', 'بريدك الإلكتروني غير موجود بعد ضمن قائمة الحضور. تمت إعادة مزامنة الورشة تلقائياً، يرجى المحاولة لاحقاً.');
        }

        if ($attendeeStatus === null) {
            return null;
        }

        return redirect()->away(
            GoogleMeetAccountChooser::build($participantEmail, $workshop->meeting_link, app()->getLocale())
        );
    }

    protected function enforceJoinDeviceLock(Request $request, WorkshopBooking $booking): ?\Illuminate\Http\RedirectResponse
    {
        $currentUser = $request->user();
        $booking->loadMissing('user');

        $bookingEmail = strtolower((string) optional($booking->user)->email);
        $currentEmail = strtolower((string) ($currentUser?->email ?? ''));
        $deviceLockSupported = $this->bookingDeviceLockSupported();
        $allowSameEmailBypass = $this->allowsSameEmailMultiDevice();

        // Allow multiple devices when the booking owner authenticates with the same email and the feature is enabled.
        if ($allowSameEmailBypass && $bookingEmail !== '' && $currentEmail !== '' && hash_equals($bookingEmail, $currentEmail)) {
            if ($deviceLockSupported) {
                $updates = [];

                if (!$booking->first_joined_at) {
                    $updates['first_joined_at'] = now();
                }

                if (!$booking->join_device_ip) {
                    $updates['join_device_ip'] = $request->ip();
                }

                if (!$booking->join_device_user_agent) {
                    $updates['join_device_user_agent'] = $this->truncateUserAgent($request->userAgent());
                }

                if (!empty($updates)) {
                    $booking->forceFill($updates)->save();
                }
            }

            return null;
        }

        if (!$deviceLockSupported) {
            return null;
        }

        $cookieName = $this->getJoinDeviceCookieName($booking);
        $storedTokenHash = $booking->join_device_token;
        $fingerprint = $this->makeDeviceFingerprint($request);

        if ($storedTokenHash) {
            $cookieToken = $request->cookie($cookieName);

            if (!is_string($cookieToken) || $cookieToken === '') {
                return $this->denyJoinFromUnrecognizedDevice($booking, $request, 'missing_cookie');
            }

            $hashedCookieToken = hash('sha256', $cookieToken);

            if (!hash_equals($storedTokenHash, $hashedCookieToken)) {
                return $this->denyJoinFromUnrecognizedDevice($booking, $request, 'cookie_mismatch');
            }

            if (!empty($booking->join_device_fingerprint) && !hash_equals($booking->join_device_fingerprint, $fingerprint)) {
                return $this->denyJoinFromUnrecognizedDevice($booking, $request, 'fingerprint_mismatch');
            }

            return null;
        }

        $plainToken = Str::random(64);
        $firstJoinedAt = $booking->first_joined_at ?: now();

        $booking->forceFill([
            'first_joined_at' => $firstJoinedAt,
            'join_device_token' => hash('sha256', $plainToken),
            'join_device_fingerprint' => $fingerprint,
            'join_device_ip' => $request->ip(),
            'join_device_user_agent' => $this->truncateUserAgent($request->userAgent()),
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

        return null;
    }

    protected function getJoinDeviceCookieName(WorkshopBooking $booking): string
    {
        $code = $booking->public_code ?: $booking->id;

        return 'wasfah_booking_device_' . strtolower((string) $code);
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

    protected function denyJoinFromUnrecognizedDevice(WorkshopBooking $booking, Request $request, string $reason): \Illuminate\Http\RedirectResponse
    {
        Log::warning('Blocked workshop booking join from unrecognized device.', [
            'booking_id' => $booking->id,
            'booking_public_code' => $booking->public_code,
            'user_id' => $booking->user_id,
            'reason' => $reason,
            'request_ip' => $request->ip(),
            'request_user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('error', 'لا يمكن فتح رابط الورشة من جهاز مختلف. يرجى التواصل مع فريق الدعم لتحديث الوصول.');
    }

    protected function bookingDeviceLockSupported(): bool
    {
        static $supported;

        if ($supported === null) {
            $supported = Schema::hasColumns('workshop_bookings', [
                'join_device_token',
                'join_device_fingerprint',
                'join_device_ip',
                'join_device_user_agent',
            ]);
        }

        return $supported;
    }

    protected function allowsSameEmailMultiDevice(): bool
    {
        return (bool) config('workshop-links.allow_same_email_multi_device', true);
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

    protected function ensureBookingOwner(WorkshopBooking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
