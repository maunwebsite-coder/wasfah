<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Models\Notification;
use App\Models\Recording;
use App\Services\GoogleMeetService;
use App\Services\UserGoogleDriveService;
use App\Services\WorkshopLinkSecurityService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Support\Concerns\ResolvesWorkshopRecordings;
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
    use ResolvesWorkshopRecordings;

    public function __construct(
        protected Recording $recordingModel,
        protected WorkshopLinkSecurityService $linkSecurity,
        protected GoogleMeetService $googleMeetService,
        protected WorkshopMeetingAttendeeSyncService $meetingAttendeeSyncService,
        protected UserGoogleDriveService $userGoogleDriveService,
    ) {
    }

    public function toggleRecordingVisibility(Request $request, Workshop $workshop)
    {
        $user = Auth::user();

        if (! $user || ! ($user->isChef() && $workshop->user_id === $user->id)) {
            abort(403);
        }

        $request->validate([
            'hidden' => ['required', 'boolean'],
            'scope' => ['nullable', 'in:public,platform'],
        ]);

        $hidden = $request->boolean('hidden');
        $scope = $request->input('scope', 'public');

        $updates = [];

        if ($scope === 'platform' && Schema::hasColumn('workshops', 'hide_recording_everywhere')) {
            $updates['hide_recording_everywhere'] = $hidden;
        } elseif (Schema::hasColumn('workshops', 'hide_public_recording')) {
            $updates['hide_public_recording'] = $hidden;
        }

        if (! empty($updates)) {
            $workshop->forceFill($updates)->save();
        }

        $message = $hidden
            ? __('bookings.recordings.hidden_success')
            : __('bookings.recordings.shown_success');

        return back()->with('success', $message);
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
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $bookingsQuery = WorkshopBooking::with([
            'workshop' => function ($query) {
                $query->with(['chef']);
            },
        ])
            ->where('user_id', $user->id);

        $bookings = (clone $bookingsQuery)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Resolve the most useful recording link (prefer Drive if available)
        $bookings->getCollection()->each(function (WorkshopBooking $booking) {
            $workshop = $booking->workshop;

            if (! $workshop) {
                return;
            }

            $resolved = $this->resolveRecordingUrl($workshop);
            $hasMeetingContext = $workshop->meeting_code || $workshop->meeting_link;
            $resolvedIsRecording = $this->isGoogleDriveUrl((string) $resolved) || $this->isDirectVideoUrl($resolved);

            // Prefer Drive recording if we only have a meeting link/code
            if ($hasMeetingContext && ! $resolvedIsRecording) {
                $driveUrl = $this->resolveDriveRecordingForWorkshop($workshop);

                if ($driveUrl) {
                    $resolved = $driveUrl;
                }
            }

            if ($resolved) {
                $workshop->setAttribute('recording_resolved_url', $resolved);

                // Ensure the attendee has access to the resolved Drive recording
                if ($booking->status === 'confirmed' && $this->isGoogleDriveUrl($resolved)) {
                    app(\App\Services\WorkshopRecordingAccessService::class)->shareWithAttendee($booking);
                }
            }
        });

        $statusCounts = (clone $bookingsQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $showEmptyState = $bookings->total() === 0;

        return view('bookings.index', [
            'bookings' => $bookings,
            'statusCounts' => $statusCounts,
            'showEmptyState' => $showEmptyState,
            'canManageRecordings' => $user->canAccessRecordManagement(),
        ]);
    }

    public function recordings()
    {
        $viewer = Auth::user();

        if (! $viewer) {
            return redirect()->route('login');
        }

        if (! $viewer->canAccessRecordManagement()) {
            return redirect()
                ->route('onboarding.show')
                ->with('error', 'هذه الصفحة مخصصة للشيفات المعتمدين. يرجى إكمال بياناتك للمتابعة.');
        }

        $bookings = WorkshopBooking::with(['workshop', 'user'])
                                  ->where('user_id', Auth::id())
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(10);

        $recordingEntries = collect();
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $dateFormat = __('chef.workshops.datetime_format');
        $isChefOwner = $viewer && method_exists($viewer, 'isChef') && $viewer->isChef();
        $viewerDriveEnabled = $viewer?->hasGoogleDriveCredentials() ?? false;
        $bookingWorkshopMap = $bookings->keyBy('workshop_id');
        $bookingWorkshopIds = $bookingWorkshopMap->keys()->filter()->values();
        $normalizedExistingUrls = collect();

        foreach ($bookings as $booking) {
            $workshop = $booking->workshop;

            if (! $workshop) {
                continue;
            }

            $isOwner = $isChefOwner && $workshop->user_id === $viewer->id;
            $isHiddenEverywhere = (bool) ($workshop->hide_recording_everywhere ?? false);

            if ($isHiddenEverywhere && ! $isOwner) {
                continue;
            }

            $resolvedRecordingUrl = $this->resolveRecordingUrl($workshop);

            if (! $resolvedRecordingUrl && $workshop->meeting_code) {
                $resolvedRecordingUrl = $this->resolveDriveRecordingForWorkshop($workshop);
            }

            $previewUrl = $this->buildRecordingPreviewUrl($resolvedRecordingUrl);

            // If the stored link isn't embeddable (e.g., Meet URL), fall back to Drive recording lookup
            if (! $previewUrl && ! $this->isDirectVideoUrl($resolvedRecordingUrl) && $workshop->meeting_code) {
                $driveUrl = $this->resolveDriveRecordingForWorkshop($workshop);

                if ($driveUrl) {
                    $resolvedRecordingUrl = $driveUrl;
                    $previewUrl = $this->buildRecordingPreviewUrl($driveUrl);
                }
            }

            if (! $resolvedRecordingUrl && ! $previewUrl) {
                continue;
            }

            $workshop->setAttribute('recording_resolved_url', $resolvedRecordingUrl);
            $workshop->setAttribute('recording_preview_url', $previewUrl);
            $workshop->setAttribute('recording_is_direct_video', $this->isDirectVideoUrl($resolvedRecordingUrl));

            $startDateLabel = $workshop->start_date
                ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateFormat)
                : __('chef.workshops.unscheduled_time');

            $locationLabel = $workshop->is_online
                ? __('chef.workshops.online_live')
                : ($workshop->location ?? __('chef.workshops.location_tbd'));

            $recordingEntries->push([
                'id' => 'booking-' . $booking->id,
                'title' => $workshop->title,
                'excerpt' => Str::limit(strip_tags((string) $workshop->description), 140),
                'date_label' => $startDateLabel,
                'location_label' => $locationLabel,
                'watch_url' => $resolvedRecordingUrl,
                'preview_url' => $previewUrl,
                'details_url' => route('bookings.show', $booking),
                'badge' => $previewUrl
                    ? __('chef.recordings.badges.available')
                    : __('chef.recordings.badges.drive'),
                'type' => 'booking',
                'sort_timestamp' => (int) ($workshop->start_date?->getTimestamp() ?? 0),
                'poster' => $workshop->image
                    ? asset('storage/' . ltrim($workshop->image, '/'))
                    : null,
                'is_direct_video' => $this->isDirectVideoUrl($resolvedRecordingUrl),
                'access' => __('bookings.recordings.access.booking'),
                'is_owner' => $isOwner,
                'hidden' => (bool) ($workshop->hide_public_recording ?? false),
                'hidden_global' => $isHiddenEverywhere,
                'viewer_count' => $workshop->confirmedBookings()->count(),
                'viewer_names' => $workshop->confirmedBookings()
                    ->with('user:id,name')
                    ->take(6)
                    ->get()
                    ->pluck('user.name')
                    ->filter()
                    ->values()
                    ->all(),
                'workshop_id' => $workshop->id,
            ]);

            if ($resolvedRecordingUrl) {
                $normalizedExistingUrls->push(strtolower(trim($resolvedRecordingUrl)));
            }
        }

        // Add owner recordings that may not be in their bookings (for chefs managing their own workshops)
        if ($isChefOwner) {
            $ownerWorkshops = Workshop::query()
                ->where('user_id', $viewer->id)
                ->whereNotNull('recording_url')
                ->orderByDesc('start_date')
                ->limit(8)
                ->get();

            foreach ($ownerWorkshops as $workshop) {
                $resolvedRecordingUrl = $this->resolveRecordingUrl($workshop);
                $previewUrl = $this->buildRecordingPreviewUrl($resolvedRecordingUrl);

                if (! $resolvedRecordingUrl && $workshop->meeting_code) {
                    $resolvedRecordingUrl = $this->resolveDriveRecordingForWorkshop($workshop);
                    $previewUrl = $this->buildRecordingPreviewUrl($resolvedRecordingUrl);
                }

                if (! $resolvedRecordingUrl && ! $previewUrl) {
                    continue;
                }

                $startDateLabel = $workshop->start_date
                    ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateFormat)
                    : __('chef.workshops.unscheduled_time');

                $recordingEntries->push([
                    'id' => 'owner-workshop-' . $workshop->id,
                    'title' => $workshop->title,
                    'excerpt' => Str::limit(strip_tags((string) $workshop->description), 140),
                    'date_label' => $startDateLabel,
                    'location_label' => $workshop->is_online
                        ? __('chef.workshops.online_live')
                        : ($workshop->location ?? __('chef.workshops.location_tbd')),
                    'watch_url' => $resolvedRecordingUrl,
                    'preview_url' => $previewUrl,
                    'details_url' => route('chef.workshops.edit', $workshop),
                    'badge' => $previewUrl
                        ? __('chef.recordings.badges.available')
                        : __('chef.recordings.badges.drive'),
                    'type' => 'workshop',
                    'sort_timestamp' => (int) ($workshop->start_date?->getTimestamp() ?? 0),
                    'poster' => $workshop->image
                        ? asset('storage/' . ltrim($workshop->image, '/'))
                        : null,
                    'is_direct_video' => $this->isDirectVideoUrl($resolvedRecordingUrl),
                    'access' => __('bookings.recordings.access.booking'),
                    'is_owner' => true,
                    'hidden' => (bool) ($workshop->hide_public_recording ?? false),
                    'hidden_global' => (bool) ($workshop->hide_recording_everywhere ?? false),
                    'viewer_count' => $workshop->confirmedBookings()->count(),
                    'viewer_names' => $workshop->confirmedBookings()
                        ->with('user:id,name')
                        ->take(6)
                        ->get()
                        ->pluck('user.name')
                        ->filter()
                        ->values()
                        ->all(),
                    'workshop_id' => $workshop->id,
                ]);

                if ($resolvedRecordingUrl) {
                    $normalizedExistingUrls->push(strtolower(trim($resolvedRecordingUrl)));
                }
            }
        }

        // Show Drive library entries only to chefs managing their own workshops
        $driveEntries = collect();

        if ($isChefOwner && $viewerDriveEnabled) {
            $driveFiles = $this->userGoogleDriveService->listRecordings($viewer, 12);

            $driveEntries = collect($driveFiles)
                ->filter(fn ($file) => $file instanceof \Google\Service\Drive\DriveFile)
                ->map(function (\Google\Service\Drive\DriveFile $file) use ($locale, $dateFormat): array {
                    $modifiedAt = $file->getModifiedTime()
                        ? Carbon::parse($file->getModifiedTime())->locale($locale)
                        : null;

                    $fileId = $file->getId();
                    $previewUrl = $fileId
                        ? sprintf('https://drive.google.com/file/d/%s/preview', $fileId)
                        : null;

                    $watchUrl = $file->getWebViewLink() ?: $previewUrl ?: $file->getWebContentLink();
                    $description = $file->getDescription();

                    return [
                        'id' => 'drive-' . ($fileId ?: uniqid('drive-', true)),
                        'title' => $file->getName() ?: __('chef.recordings.untitled'),
                        'excerpt' => $description
                            ? Str::limit($description, 130)
                            : __('chef.recordings.drive_default_description'),
                        'date_label' => $modifiedAt
                            ? $modifiedAt->translatedFormat($dateFormat)
                            : __('chef.recordings.updated_unknown'),
                        'location_label' => __('chef.recordings.library_label'),
                        'watch_url' => $watchUrl,
                        'preview_url' => $previewUrl,
                        'details_url' => $watchUrl,
                        'badge' => __('chef.recordings.badges.available'),
                        'type' => 'drive',
                        'sort_timestamp' => $modifiedAt ? $modifiedAt->getTimestamp() : 0,
                        'poster' => $file->getIconLink(),
                        'is_direct_video' => false,
                        'access' => __('bookings.recordings.access.drive'),
                    ];
                });
        }

        // Inject Drive recordings that were saved to the recordings table for the user's workshops/bookings
        $savedRecordings = collect();

        if ($bookingWorkshopIds->isNotEmpty()) {
            $savedRecordings = $this->recordingModel
                ->newQuery()
                ->whereIn('workshop_id', $bookingWorkshopIds)
                ->with('workshop')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        foreach ($savedRecordings as $recording) {
            $workshop = $recording->workshop;
            $watchUrl = $recording->recording_url;
            $previewUrl = $recording->preview_url ?: $this->buildRecordingPreviewUrl($watchUrl);

            if (! $watchUrl && ! $previewUrl) {
                continue;
            }

            $normalizedWatch = $watchUrl ? strtolower(trim($watchUrl)) : null;

            if ($normalizedWatch && $normalizedExistingUrls->contains($normalizedWatch)) {
                continue;
            }

            $startDateLabel = $workshop?->start_date
                ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateFormat)
                : optional($recording->created_at)?->copy()->locale($locale)->translatedFormat($dateFormat);

            $locationLabel = $workshop?->is_online
                ? __('chef.workshops.online_live')
                : ($workshop?->location ?? __('chef.workshops.location_tbd'));

            $booking = $bookingWorkshopMap->get($recording->workshop_id);
            $detailsUrl = $booking
                ? route('bookings.show', $booking)
                : ($workshop ? route('workshop.show', $workshop) : null);

            $recordingEntries->push([
                'id' => 'saved-recording-' . $recording->id,
                'title' => $recording->title ?: ($workshop?->title ?? __('chef.recordings.untitled')),
                'excerpt' => $workshop
                    ? Str::limit(strip_tags((string) $workshop->description), 140)
                    : null,
                'date_label' => $startDateLabel,
                'location_label' => $locationLabel,
                'watch_url' => $watchUrl ?: $previewUrl,
                'preview_url' => $previewUrl,
                'details_url' => $detailsUrl,
                'badge' => __('chef.recordings.badges.available'),
                'type' => 'recording',
                'sort_timestamp' => $workshop?->start_date
                    ? (int) $workshop->start_date->getTimestamp()
                    : (int) ($recording->created_at?->getTimestamp() ?? 0),
                'poster' => $workshop?->image
                    ? asset('storage/' . ltrim($workshop->image, '/'))
                    : ($recording->meta['thumbnailLink'] ?? $recording->meta['iconLink'] ?? null),
                'is_direct_video' => $this->isDirectVideoUrl($watchUrl),
                'access' => __('bookings.recordings.access.booking'),
                'is_owner' => $isChefOwner && $workshop && $workshop->user_id === $viewer->id,
                'hidden' => (bool) ($workshop->hide_public_recording ?? false),
                'hidden_global' => (bool) ($workshop->hide_recording_everywhere ?? false),
                'viewer_count' => $workshop?->confirmedBookings()->count(),
                'viewer_names' => $workshop
                    ? $workshop->confirmedBookings()
                        ->with('user:id,name')
                        ->take(6)
                        ->get()
                        ->pluck('user.name')
                        ->filter()
                        ->values()
                        ->all()
                    : [],
                'workshop_id' => $recording->workshop_id,
            ]);

            if ($normalizedWatch) {
                $normalizedExistingUrls->push($normalizedWatch);
            }
        }

        $recordingEntries = $recordingEntries
            ->merge($driveEntries)
            ->sortByDesc('sort_timestamp')
            ->take(12)
            ->values();

        $showEmptyState = $bookings->total() === 0 && $recordingEntries->isEmpty();

        return view('bookings.recordings', compact('bookings', 'recordingEntries', 'showEmptyState'));
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
        $graceJoinMinutes = $this->participantJoinGracePeriodMinutes();
        $graceJoinAllowed = $this->participantJoinGracePeriodReached($workshop);
        $graceJoinAt = $this->participantJoinGraceAt($workshop);
        $meetingExpiresAt = $this->meetingExpiresAt($workshop);
        $meetingExpired = $this->hasMeetingExpired($workshop);
        $reviewWindowOpen = $workshop->reviewWindowOpen();
        $reviewUnlockAt = $workshop->reviewUnlockAt();
        $canReview = $user ? $workshop->canBeReviewedBy($user->id) : false;
        $userReview = $user ? $workshop->reviews()->where('user_id', $user->id)->first() : null;

        if ($meetingExpired) {
            $graceJoinAllowed = false;
        }

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
            'meetingReady' => ($meetingStarted || $graceJoinAllowed) && ! $meetingExpired,
            'graceJoinAllowed' => $graceJoinAllowed,
            'graceJoinAtIso' => optional($graceJoinAt)->toIso8601String(),
            'graceJoinMinutes' => $graceJoinMinutes,
            'canReview' => $canReview,
            'reviewWindowOpen' => $reviewWindowOpen,
            'reviewUnlockAtIso' => optional($reviewUnlockAt)->toIso8601String(),
            'userReview' => $userReview,
            'meetingExpiresAtIso' => optional($meetingExpiresAt)->toIso8601String(),
            'meetingExpired' => $meetingExpired,
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

        if ($this->hasMeetingExpired($workshop)) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', __('bookings.join.status.messages.expired'));
        }

        $isGoogleMeet = $workshop->meeting_provider === 'google_meet';

        if ($isGoogleMeet) {
            $meetingLockSupported = $this->meetingLockSupported();
            $meetingLocked = $meetingLockSupported ? (bool) $workshop->meeting_locked_at : false;
            $graceJoinAllowed = $meetingLocked
                ? false
                : $this->participantJoinGracePeriodReached($workshop);

            if ($meetingLocked) {
                return redirect()
                    ->to($this->linkSecurity->makeParticipantJoinUrl($booking))
                    ->with('error', 'تم قفل الاجتماع من قبل المضيف. يرجى انتظار السماح بالدخول.');
            }

            if (!$workshop->meeting_started_at && !$graceJoinAllowed) {
                return redirect()
                    ->to($this->linkSecurity->makeParticipantJoinUrl($booking))
                    ->with('error', __('bookings.join.status.messages.grace_wait', [
                        'minutes' => $this->participantJoinGracePeriodMinutes(),
                    ]));
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
        $meetingLockSupported = $this->meetingLockSupported();
        $meetingLocked = $meetingLockSupported && $workshop
            ? (bool) $workshop->meeting_locked_at
            : false;
        $graceJoinAllowed = $meetingLocked
            ? false
            : $this->participantJoinGracePeriodReached($workshop);
        $graceJoinAt = $this->participantJoinGraceAt($workshop);
        $meetingStarted = (bool) ($workshop?->meeting_started_at);
        $expiresAt = $this->meetingExpiresAt($workshop);
        $meetingExpired = $this->hasMeetingExpired($workshop);
        $reviewUnlockAt = $workshop?->reviewUnlockAt();
        $reviewWindowOpen = $workshop?->reviewWindowOpen() ?? false;

        if ($meetingExpired) {
            $graceJoinAllowed = false;
        }

        $joinUnlocked = ($meetingStarted || $graceJoinAllowed) && ! $meetingExpired;

        return response()->json([
            'meeting_started' => $meetingStarted,
            'started_at' => $workshop?->meeting_started_at?->toIso8601String(),
            'join_unlocked' => $joinUnlocked,
            'grace_join_allowed' => $graceJoinAllowed,
            'grace_join_at' => optional($graceJoinAt)->toIso8601String(),
            'meeting_locked' => $meetingLocked,
            'locked_at' => $meetingLockSupported && $workshop
                ? $workshop->meeting_locked_at?->toIso8601String()
                : null,
            'meeting_expired' => $meetingExpired,
            'expires_at' => $expiresAt?->toIso8601String(),
            'review_window_open' => $reviewWindowOpen,
            'review_unlock_at' => optional($reviewUnlockAt)->toIso8601String(),
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

    protected function meetingAccessGraceHours(): int
    {
        return (int) config('workshop-links.participant_meeting_grace_hours', 6);
    }

    protected function resolveWorkshopEndDate(?Workshop $workshop): ?Carbon
    {
        if (! $workshop) {
            return null;
        }

        if ($workshop->end_date instanceof Carbon) {
            return $workshop->end_date->copy();
        }

        if ($workshop->start_date && is_numeric($workshop->duration ?? null)) {
            return $workshop->start_date->copy()->addMinutes((int) $workshop->duration);
        }

        return null;
    }

    protected function meetingExpiresAt(?Workshop $workshop): ?Carbon
    {
        $endAt = $this->resolveWorkshopEndDate($workshop);

        if (! $endAt) {
            return null;
        }

        $graceHours = $this->meetingAccessGraceHours();

        if ($graceHours <= 0) {
            return $endAt->copy();
        }

        return $endAt->copy()->addHours($graceHours);
    }

    protected function hasMeetingExpired(?Workshop $workshop): bool
    {
        $expiresAt = $this->meetingExpiresAt($workshop);

        if (! $expiresAt) {
            return false;
        }

        return now($expiresAt->getTimezone())->greaterThanOrEqualTo($expiresAt);
    }

    protected function participantJoinGracePeriodMinutes(): int
    {
        return 15;
    }

    protected function participantJoinGraceAt(Workshop $workshop): ?Carbon
    {
        if (! $workshop->start_date) {
            return null;
        }

        return $workshop->start_date->copy()->addMinutes($this->participantJoinGracePeriodMinutes());
    }

    protected function participantJoinGracePeriodReached(Workshop $workshop): bool
    {
        if (! $workshop->start_date) {
            return false;
        }

        if ($this->meetingLockSupported() && $workshop->meeting_locked_at) {
            return false;
        }

        $graceAt = $this->participantJoinGraceAt($workshop);

        return $graceAt !== null && now()->greaterThanOrEqualTo($graceAt);
    }

    protected function ensureBookingOwner(WorkshopBooking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function resolveDriveRecordingForWorkshop(Workshop $workshop): ?string
    {
        $workshop->loadMissing('chef');
        $owner = $workshop->chef;
        $meetingCode = $workshop->meeting_code ?: Workshop::extractMeetingCode($workshop->meeting_link);

        if (! $meetingCode) {
            return null;
        }

        if ($owner && $owner->hasGoogleDriveCredentials()) {
            $url = $this->userGoogleDriveService->findRecordingUrl($owner, $meetingCode);

            if ($url) {
                return $url;
            }
        }

        return null;
    }
}
