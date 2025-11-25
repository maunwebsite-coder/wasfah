<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Workshop;
use App\Services\EnhancedImageUploadService;
use App\Services\GoogleMeetService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Support\ImageUploadConstraints;
use App\Support\HostMeetRedirectLinkFactory;
use App\Support\Timezones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopController extends Controller
{
    public function __construct(
        protected GoogleMeetService $googleMeetService,
        protected WorkshopMeetingAttendeeSyncService $meetingAttendeeSync
    )
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * عرض قائمة الورشات
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => $request->get('status', 'all'),
            'mode' => $request->get('mode', 'all'),
            'featured' => $request->get('featured', 'all'),
            'time' => $request->get('time', 'all'),
        ];

        $workshopsQuery = Workshop::query()
            ->withCount(['bookings' => function ($query) {
                $query->where('status', 'confirmed');
            }])
            ->withCount('bookings as total_bookings');

        if ($filters['search'] !== '') {
            $workshopsQuery->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('instructor', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('location', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['status'] === 'active') {
            $workshopsQuery->where('is_active', true);
        } elseif ($filters['status'] === 'inactive') {
            $workshopsQuery->where('is_active', false);
        }

        if ($filters['mode'] === 'online') {
            $workshopsQuery->where('is_online', true);
        } elseif ($filters['mode'] === 'offline') {
            $workshopsQuery->where('is_online', false);
        }

        if ($filters['featured'] === 'featured') {
            $workshopsQuery->where('is_featured', true);
        } elseif ($filters['featured'] === 'regular') {
            $workshopsQuery->where('is_featured', false);
        }

        if ($filters['time'] === 'upcoming') {
            $workshopsQuery->where('start_date', '>=', now());
        } elseif ($filters['time'] === 'past') {
            $workshopsQuery->where('start_date', '<', now());
        }

        $sortDirection = $filters['time'] === 'past' ? 'desc' : 'asc';

        $workshops = $workshopsQuery
            ->orderBy('start_date', $sortDirection)
            ->paginate(10)
            ->appends($request->query());

        $featuredWorkshop = Workshop::where('is_featured', true)->first();

        $aggregates = Workshop::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_online = 1 THEN 1 ELSE 0 END) as online,
                COALESCE(SUM(bookings_count), 0) as confirmed_bookings
            ')
            ->first();

        $stats = [
            'total' => (int) ($aggregates?->total ?? 0),
            'active' => (int) ($aggregates?->active ?? 0),
            'online' => (int) ($aggregates?->online ?? 0),
            'confirmed_bookings' => (int) ($aggregates?->confirmed_bookings ?? 0),
        ];

        $hasActiveFilters = ($filters['search'] !== '')
            || collect($filters)->except('search')->contains(function ($value) {
                return $value !== 'all';
            });

        return view('admin.workshops.index', compact(
            'workshops',
            'featuredWorkshop',
            'stats',
            'filters',
            'hasActiveFilters'
        ));
    }

    /**
     * عرض نموذج إنشاء ورشة جديدة
     */
    public function create()
    {
        $recipes = Recipe::approved()->public()->orderBy('title')->get();
        return view('admin.workshops.create', [
            'recipes' => $recipes,
            'forceAutoMeetingLinks' => $this->shouldForceAutoMeetingLinks(),
            'timezoneOptions' => Timezones::hostOptions(),
        ]);
    }

    /**
     * حفظ ورشة جديدة
     */
    public function store(Request $request)
    {
        $this->enforceAutoMeetingLinkPolicy($request);

        $request->validate(
            Workshop::validationRules(),
            ImageUploadConstraints::messages('image', [
                'ar' => 'صورة الورشة',
                'en' => 'workshop image',
            ])
        );

        $timezone = $this->resolveWorkshopTimezone($request);

        $workshop = new Workshop();
        $workshop->user_id = Auth::id();
        $workshop->title = $request->title;
        $workshop->description = $request->description;
        $workshop->instructor = $request->instructor;
        $workshop->host_timezone = $timezone;
        $workshop->start_date = $this->convertLocalInputToUtc($request->start_date, $timezone);
        $workshop->end_date = $this->convertLocalInputToUtc($request->end_date, $timezone);
        $workshop->price = $request->price;
        $workshop->currency = $request->currency;
        $workshop->max_participants = $request->max_participants;
        $workshop->is_online = $request->boolean('is_online');
        $workshop->is_active = $request->boolean('is_active');
        $workshop->featured_description = $request->featured_description;

        // الحقول الجديدة
        $workshop->category = $request->category;
        $workshop->level = $request->level;
        $workshop->duration = $request->duration;
        $workshop->registration_deadline = $this->calculateRegistrationDeadline($workshop->start_date);
        $workshop->address = $request->address ?? '';
        $workshop->content = $request->content ?? '';
        $workshop->what_you_will_learn = $request->what_you_will_learn ?? '';
        $workshop->requirements = $request->requirements ?? '';
        $workshop->materials_needed = $request->materials_needed ?? '';
        $workshop->instructor_bio = $request->instructor_bio ?? '';

        $this->syncMeetingDetails($request, $workshop);

        // التعامل مع الورشة المميزة بعد ضبط الحقول الإلزامية
        if ($request->has('is_featured') && $request->is_featured) {
            $workshop->makeFeatured();
        } else {
            $workshop->is_featured = false;
        }
        
        // رفع الصورة مع الضغط
        if ($request->hasFile('image')) {
            $uploadResult = EnhancedImageUploadService::uploadImage(
                $request->file('image'),
                'workshops',
                85, // جودة 85%
                1200, // أقصى عرض
                1200  // أقصى ارتفاع
            );
            
            if ($uploadResult['success']) {
                $workshop->image = $uploadResult['path'];
                \Log::info('Image uploaded successfully for new workshop', [
                    'image_path' => $uploadResult['path'],
                    'compressed' => $uploadResult['compressed'] ?? false,
                    'original_size' => $uploadResult['original_size'] ?? null,
                    'compressed_size' => $uploadResult['compressed_size'] ?? null
                ]);
            } else {
                \Log::error('Failed to upload image for new workshop', [
                    'error' => $uploadResult['error'],
                    'file_name' => $request->file('image')->getClientOriginalName(),
                    'file_size' => $request->file('image')->getSize()
                ]);
                return redirect()->back()
                    ->with('error', $uploadResult['error']);
            }
        }

        $workshop->save();
        $this->meetingAttendeeSync->sync($workshop);

        // ربط الوصفات المختارة بالورشة
        if ($request->has('recipe_ids') && is_array($request->recipe_ids)) {
            $recipeData = [];
            foreach ($request->recipe_ids as $index => $recipeId) {
                $recipeData[$recipeId] = ['order' => $index + 1];
            }
            $workshop->recipes()->sync($recipeData);
        }

        return redirect()->route('admin.workshops.index')
            ->with('success', 'تم إنشاء الورشة بنجاح!');
    }

    /**
     * عرض تفاصيل ورشة
     */
    public function show($id)
    {
        $workshop = Workshop::withCount(['bookings' => function ($query) {
            $query->where('status', 'confirmed');
        }])
        ->withCount('bookings as total_bookings')
        ->findOrFail($id);
        return view('admin.workshops.show', compact('workshop'));
    }

    /**
     * عرض غرفة الاجتماع الخاصة بالإدمن مع كامل أدوات التحكم.
     */
    public function meeting(Workshop $workshop)
    {
        if (!$workshop->is_online || !$workshop->meeting_link) {
            return redirect()
                ->route('admin.workshops.show', $workshop)
                ->with('error', 'هذه الورشة لا تحتوي على رابط اجتماع نشط بعد.');
        }

        if ($workshop->meeting_provider !== 'google_meet') {
            return redirect()
                ->route('admin.workshops.show', $workshop)
                ->with('error', 'غرفة الإدارة متاحة فقط لاجتماعات Google Meet التي يتم توليدها من داخل النظام.');
        }

        return view('admin.workshops.meeting', [
            'workshop' => $workshop,
            'user' => Auth::user(),
            'meetingLink' => $workshop->meeting_link,
            'startsAtIso' => optional($workshop->start_date)->toIso8601String(),
            'hostRedirectUrl' => HostMeetRedirectLinkFactory::make($workshop),
        ]);
    }

    /**
     * عرض نموذج تعديل ورشة
     */
    public function edit($id)
    {
        $workshop = Workshop::with('recipes')->findOrFail($id);
        $recipes = Recipe::approved()->public()->orderBy('title')->get();
        return view('admin.workshops.edit', [
            'workshop' => $workshop,
            'recipes' => $recipes,
            'forceAutoMeetingLinks' => $this->shouldForceAutoMeetingLinks(),
            'timezoneOptions' => Timezones::hostOptions(),
        ]);
    }

    /**
     * تحديث ورشة
     */
    public function update(Request $request, $id)
    {
        $workshop = Workshop::findOrFail($id);

        // Log the request data for debugging
        \Log::info('Workshop update request', [
            'workshop_id' => $id,
            'has_image' => $request->hasFile('image'),
            'image_size' => $request->hasFile('image') ? $request->file('image')->getSize() : null,
            'image_mime' => $request->hasFile('image') ? $request->file('image')->getMimeType() : null,
            'all_files' => $request->allFiles(),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $this->enforceAutoMeetingLinkPolicy($request);

        try {
        $request->validate(
            Workshop::validationRules($id),
            ImageUploadConstraints::messages('image', [
                'ar' => 'صورة الورشة',
                'en' => 'workshop image',
            ])
        );
            \Log::info('Validation passed for workshop update', ['workshop_id' => $id]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for workshop update', [
                'workshop_id' => $id,
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        $timezone = $this->resolveWorkshopTimezone($request, $workshop);

        $workshop->title = $request->title;
        $workshop->description = $request->description;
        $workshop->instructor = $request->instructor;
        $workshop->host_timezone = $timezone;
        $workshop->start_date = $this->convertLocalInputToUtc($request->start_date, $timezone);
        $workshop->end_date = $this->convertLocalInputToUtc($request->end_date, $timezone);
        $workshop->price = $request->price;
        $workshop->currency = $request->currency;
        $workshop->max_participants = $request->max_participants;
        $workshop->is_online = $request->boolean('is_online');
        $workshop->is_active = $request->boolean('is_active');
        $workshop->featured_description = $request->featured_description;
        
        // الحقول الجديدة
        $workshop->category = $request->category;
        $workshop->level = $request->level;
        $workshop->duration = $request->duration;
        $workshop->registration_deadline = $this->calculateRegistrationDeadline($workshop->start_date);
        $workshop->address = $request->address ?? '';
        $workshop->content = $request->content ?? '';
        $workshop->what_you_will_learn = $request->what_you_will_learn ?? '';
        $workshop->requirements = $request->requirements ?? '';
        $workshop->materials_needed = $request->materials_needed ?? '';
        $workshop->instructor_bio = $request->instructor_bio ?? '';

        $this->syncMeetingDetails($request, $workshop);

        // التعامل مع الورشة المميزة بعد ضبط الحقول الإلزامية
        if ($request->has('is_featured') && $request->is_featured) {
            $workshop->makeFeatured();
        } else {
            $workshop->is_featured = false;
        }
        
        // تحديث الصورة
        if ($request->hasFile('image')) {
            \Log::info('Processing image upload for workshop', [
                'workshop_id' => $id,
                'old_image' => $workshop->image,
                'new_image_size' => $request->file('image')->getSize(),
                'new_image_mime' => $request->file('image')->getMimeType(),
                'file_name' => $request->file('image')->getClientOriginalName(),
            ]);

            // حذف الصورة القديمة
            if ($workshop->image) {
                EnhancedImageUploadService::deleteImage($workshop->image);
            }
            
            $uploadResult = EnhancedImageUploadService::uploadImage(
                $request->file('image'),
                'workshops',
                85, // جودة 85%
                1200, // أقصى عرض
                1200  // أقصى ارتفاع
            );
            
            if ($uploadResult['success']) {
                $workshop->image = $uploadResult['path'];
                \Log::info('Image uploaded successfully for workshop', [
                    'workshop_id' => $id,
                    'image_path' => $uploadResult['path'],
                    'compressed' => $uploadResult['compressed'] ?? false,
                    'original_size' => $uploadResult['original_size'] ?? null,
                    'compressed_size' => $uploadResult['compressed_size'] ?? null
                ]);
            } else {
                \Log::error('Failed to upload image for workshop', [
                    'workshop_id' => $id,
                    'error' => $uploadResult['error'],
                    'file_name' => $request->file('image')->getClientOriginalName(),
                    'file_size' => $request->file('image')->getSize()
                ]);
                return redirect()->back()
                    ->with('error', $uploadResult['error']);
            }
        } elseif ($request->has('remove_image') && $request->remove_image) {
            // حذف الصورة الحالية
            if ($workshop->image) {
                EnhancedImageUploadService::deleteImage($workshop->image);
                $workshop->image = null;
                \Log::info('Image removed for workshop', [
                    'workshop_id' => $id
                ]);
            }
        }

        $workshop->save();
        $this->meetingAttendeeSync->sync($workshop);
        
        \Log::info('Workshop updated successfully', [
            'workshop_id' => $id,
            'new_image' => $workshop->image,
            'title' => $workshop->title
        ]);

        // تحديث الوصفات المختارة للورشة
        if ($request->has('recipe_ids') && is_array($request->recipe_ids)) {
            $recipeData = [];
            foreach ($request->recipe_ids as $index => $recipeId) {
                $recipeData[$recipeId] = ['order' => $index + 1];
            }
            $workshop->recipes()->sync($recipeData);
        } else {
            // إذا لم يتم اختيار أي وصفات، احذف جميع الروابط
            $workshop->recipes()->detach();
        }

        return redirect()->route('admin.workshops.index')
            ->with('success', __('workshops.flash.updated'));
    }

    public function generateMeetingLink(Request $request)
    {
        abort_unless(Auth::check() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
        ]);

        $startsAt = null;
        if (!empty($validated['start_date'])) {
            try {
                $startsAt = Carbon::parse($validated['start_date']);
            } catch (\Throwable $exception) {
                $startsAt = null;
            }
        }

        $attendees = [];
        $adminUser = Auth::user();

        if ($adminUser && method_exists($adminUser, 'preferredGoogleEmail')) {
            $preferred = $adminUser->preferredGoogleEmail();

            if ($preferred) {
                $attendees[] = [
                    'email' => $preferred,
                    'displayName' => $adminUser->name,
                    'organizer' => true,
                ];
            }
        }

        try {
            $meeting = $this->googleMeetService->createMeeting(
                $validated['title'],
                Auth::id() ?? 0,
                $startsAt,
                null,
                null,
                $attendees
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'تعذر إنشاء اجتماع Google Meet. يرجى التحقق من الإعدادات والمحاولة مجدداً.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'meeting_link' => $meeting['meeting_link'],
            'event_id' => $meeting['event_id'] ?? null,
            'starts_at' => optional($meeting['starts_at'] ?? null)?->toIso8601String(),
            'provider' => $meeting['provider'] ?? 'google_meet',
        ]);
    }

    /**
     * حذف ورشة
     */
    public function destroy($id)
    {
        $workshop = Workshop::findOrFail($id);
        
        // حذف الصورة
        if ($workshop->image) {
            EnhancedImageUploadService::deleteImage($workshop->image);
        }
        
        $workshop->delete();

        return redirect()->route('admin.workshops.index')
            ->with('success', 'تم حذف الورشة بنجاح!');
    }

    /**
     * تفعيل/إلغاء تفعيل ورشة
     */
    public function toggleStatus($id)
    {
        $workshop = Workshop::findOrFail($id);
        $workshop->is_active = !$workshop->is_active;
        $workshop->save();

        $status = $workshop->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        return redirect()->back()
            ->with('success', $status . ' الورشة بنجاح!');
    }

    /**
     * جعل ورشة هي الورشة القادمة (المميزة)
     */
    public function toggleFeatured($id)
    {
        $workshop = Workshop::findOrFail($id);
        
        // استخدام الـ method الجديد من الـ model
        $workshop->makeFeatured();

        return redirect()->back()
            ->with('success', 'تم جعل "' . $workshop->title . '" هي الورشة القادمة بنجاح!');
    }

    /**
     * التحقق من وجود ورشة مميزة
     */
    public function checkFeatured(Request $request)
    {
        $excludeId = $request->get('exclude');
        
        $query = Workshop::where('is_featured', true);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $hasFeatured = $query->exists();
        
        return response()->json([
            'hasFeatured' => $hasFeatured
        ]);
    }

    protected function resolveWorkshopTimezone(Request $request, ?Workshop $workshop = null): string
    {
        $user = Auth::user();
        $timezone = null;
        $candidates = [
            $request->input('host_timezone'),
            $workshop?->host_timezone,
            $user?->timezone,
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

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin() && $user->timezone !== $timezone) {
            $user->forceFill(['timezone' => $timezone])->save();
        }

        return $timezone;
    }

    protected function convertLocalInputToUtc(?string $value, string $timezone): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value, $timezone)->setTimezone('UTC');
        } catch (\Throwable $exception) {
            \Log::warning('Failed to convert admin workshop datetime to UTC.', [
                'input' => $value,
                'timezone' => $timezone,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function shouldForceAutoMeetingLinks(): bool
    {
        return $this->googleMeetService->isEnabled();
    }

    protected function calculateRegistrationDeadline(?Carbon $startDateUtc): ?Carbon
    {
        if (! $startDateUtc instanceof Carbon) {
            return null;
        }

        return $startDateUtc->copy()->subMinutes(2);
    }

    protected function enforceAutoMeetingLinkPolicy(Request $request): void
    {
        $googleMeetEnabled = $this->googleMeetService->isEnabled();

        if (!$googleMeetEnabled) {
            $request->merge([
                'auto_generate_meeting' => 0,
            ]);

            return;
        }

        if ($request->boolean('is_online')) {
            $request->merge([
                'auto_generate_meeting' => 1,
                'meeting_link' => null,
            ]);
        }
    }

    protected function syncMeetingDetails(Request $request, Workshop $workshop): void
    {
        $locationValue = trim((string) ($request->location ?? ''));

        if (!$workshop->is_online) {
            $workshop->meeting_link = null;
            $workshop->meeting_provider = 'manual';
            $workshop->location = $locationValue;
            $workshop->meeting_code = null;
            $workshop->meeting_event_id = null;
            $workshop->meeting_calendar_id = null;
            $workshop->meeting_conference_id = null;
            return;
        }

        $meetingLinkInput = trim((string) ($request->meeting_link ?? ''));
        $googleMeetEnabled = $this->googleMeetService->isEnabled();

        if ($googleMeetEnabled) {
            $meeting = $this->generateAdminGoogleMeetMeeting($workshop);
            $workshop->meeting_link = $meeting['meeting_link'];
            $workshop->meeting_provider = $meeting['provider'] ?? 'google_meet';
            $workshop->location = $locationValue !== '' ? $locationValue : 'أونلاين عبر Google Meet';
            $workshop->meeting_code = Workshop::extractMeetingCode($workshop->meeting_link);
            $workshop->meeting_event_id = $meeting['event_id'] ?? null;
            $workshop->meeting_calendar_id = $meeting['calendar_id'] ?? null;
            $workshop->meeting_conference_id = $meeting['conference_id'] ?? null;
            return;
        }

        $workshop->meeting_link = $meetingLinkInput !== '' ? $meetingLinkInput : null;
        $workshop->meeting_provider = $this->detectMeetingProvider($workshop->meeting_link);
        $workshop->location = $locationValue !== '' ? $locationValue : 'أونلاين';
        $workshop->meeting_code = Workshop::extractMeetingCode($workshop->meeting_link);
        $workshop->meeting_event_id = null;
        $workshop->meeting_calendar_id = null;
        $workshop->meeting_conference_id = null;
    }

    protected function generateAdminGoogleMeetMeeting(Workshop $workshop): array
    {
        $startsAt = null;

        if ($workshop->start_date instanceof Carbon) {
            $startsAt = $workshop->start_date;
        } elseif (!empty($workshop->start_date)) {
            try {
                $startsAt = Carbon::parse($workshop->start_date);
            } catch (\Throwable $exception) {
                $startsAt = null;
            }
        }

        $attendees = [];

        $hostAttendee = $workshop->hostAttendeePayload();

        if ($hostAttendee) {
            $attendees[] = $hostAttendee;
        }

        try {
            return $this->googleMeetService->createMeeting(
                $workshop->title ?: 'ورشة جديدة',
                Auth::id() ?? 0,
                $startsAt,
                null,
                null,
                $attendees,
                $hostAttendee
            );
        } catch (\Throwable $exception) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'meeting_link' => 'تعذر إنشاء اجتماع Google Meet، يرجى إدخال الرابط يدوياً أو المحاولة لاحقاً. ' . $exception->getMessage(),
            ]);
        }
    }

    protected function detectMeetingProvider(?string $url): string
    {
        if (!$url) {
            return 'manual';
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (is_string($host) && str_contains($host, 'meet.google.com')) {
            return 'google_meet';
        }

        return 'manual';
    }
}
