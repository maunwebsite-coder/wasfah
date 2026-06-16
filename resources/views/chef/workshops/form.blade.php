@php
    use App\Support\Timezones;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    /** @var \App\Models\Workshop|null $workshop */
    $workshop = $workshop ?? null;
    $levels = [
        'beginner' => __('chef.workshops.levels.beginner'),
        'intermediate' => __('chef.workshops.levels.intermediate'),
        'advanced' => __('chef.workshops.levels.advanced'),
    ];
    $currencies = [
        'JOD' => __('chef.workshop_form.currencies.jod'),
    ];
    $isOnline = true;
    $autoGenerateMeeting = old(
        'auto_generate_meeting',
        $workshop
            ? (($workshop->meeting_provider ?? null) === 'google_meet')
            : 1
    );
    $startDateValue = old('start_date', optional(optional($workshop)->start_date)->format('Y-m-d\TH:i'));
    $endDateValue = old('end_date', optional(optional($workshop)->end_date)->format('Y-m-d\TH:i'));
    $timezoneOptions = $timezoneOptions ?? Timezones::hostOptions();
    $detectedTimezone = request()->cookie('user_timezone');
    $hostTimezoneValue = old(
        'host_timezone',
        $workshop->host_timezone
            ?? auth()->user()?->timezone
            ?? $detectedTimezone
            ?? Timezones::defaultHostTimezone()
    );
    if (! Timezones::isAllowedHostTimezone($hostTimezoneValue)) {
        $hostTimezoneValue = Timezones::defaultHostTimezone();
    }
    $durationMinutes = (int) old('duration', $workshop->duration ?? 90);
    if ($durationMinutes <= 0) {
        $durationMinutes = 90;
    }
    $locale = app()->getLocale();
    $isArabic = $locale === 'ar';
    $coverImageUrl = null;
    if ($workshop && $workshop->image) {
        $coverImageUrl = Str::startsWith($workshop->image, ['http://', 'https://'])
            ? $workshop->image
            : Storage::disk('public')->url($workshop->image);
    }

    $currentUser = auth()->user();
    $hostCalendarConnected = $hostCalendarConnected ?? false;
    $hostCalendarEmail = $hostCalendarEmail ?? $currentUser?->google_calendar_email ?? $currentUser?->preferredGoogleEmail();
    $calendarConnectUrl = route('chef.google.calendar.connect', ['redirect' => url()->current()]);

    $forceAutoMeetingLinks = $forceAutoMeetingLinks ?? false;
    $hostsCanOverride = (bool) config('workshop-links.allow_host_meeting_link_override', true);
    $isAdminUser = $currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin();
    $canManageMeetingLinks = !$forceAutoMeetingLinks && ($isAdminUser || $hostsCanOverride);

    if ($forceAutoMeetingLinks || !$canManageMeetingLinks) {
        $autoGenerateMeeting = 1;
    }

    $smartSuggestionTitle = $isArabic ? 'مواعيد مقترحة للخبير' : 'Suggested expert slots';
    $smartSuggestionSub = $isArabic
        ? 'اختر وقتاً جاهزاً وسيتم ملء البداية والنهاية بناءً على مدة الورشة.'
        : 'Pick a ready time and we’ll fill start & end based on your workshop duration.';
    $smartSuggestionHint = $isArabic
        ? 'توقيت المضيف سيتم احترامه، مع عرض توقيت متصفحك عند إدخال الحجز.'
        : 'We respect the host timezone and mirror it with your browser time.';
    $smartSuggestionTimezoneLabel = $isArabic ? 'توقيت المضيف' : 'Host timezone';
    $baseNow = Carbon::now($hostTimezoneValue);
    $smartSuggestions = collect([
        [
            'label' => $isArabic ? 'غداً في المساء' : 'Tomorrow evening',
            'start' => $baseNow->copy()->addDay()->setTime(19, 0),
        ],
        [
            'label' => $isArabic ? 'بعد 3 أيام • صباحاً' : 'In 3 days • Morning',
            'start' => $baseNow->copy()->addDays(3)->setTime(11, 0),
        ],
        [
            'label' => $isArabic ? 'نهاية الأسبوع القادمة' : 'Next weekend',
            'start' => $baseNow->copy()->next(Carbon::SATURDAY)->setTime(16, 0),
        ],
        [
            'label' => $isArabic ? 'خيار مرن • مساء' : 'Flexible • Evening',
            'start' => $baseNow->copy()->addDays(5)->setTime(20, 0),
        ],
    ])
        ->filter(fn ($slot) => $slot['start']->gt($baseNow))
        ->map(function ($slot) use ($locale, $isArabic) {
            $label = $slot['label'] . ' • ' . $slot['start']->copy()->locale($locale)->translatedFormat($isArabic ? 'j F • h:i a' : 'D, M j • h:i a');

            return [
                'label' => $label,
                'value' => $slot['start']->format('Y-m-d\TH:i'),
            ];
        })
        ->values();
@endphp

@push('styles')
<style>
    .workshop-hero-visual {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 260px;
        padding: 2rem;
        border-radius: 1.5rem;
        overflow: hidden;
        isolation: isolate;
        background: linear-gradient(135deg, #4d1f22 0%, #6b2e30 60%, #0f172a 100%);
        box-shadow: 0 18px 46px -28px rgba(0, 0, 0, 0.6);
    }

    .hero-image-glow {
        position: absolute;
        inset: 12%;
        background: radial-gradient(circle at 20% 20%, rgba(245, 158, 11, 0.45), transparent 45%),
                    radial-gradient(circle at 85% 30%, rgba(56, 189, 248, 0.4), transparent 45%),
                    radial-gradient(circle at 40% 80%, rgba(16, 185, 129, 0.35), transparent 50%);
        filter: blur(38px);
        opacity: 0.9;
        z-index: 0;
    }

    .hero-image-card {
        position: relative;
        width: 100%;
        max-width: 420px;
        aspect-ratio: 4 / 5;
        border-radius: 1.3rem;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.06));
        border: 1px solid rgba(255, 255, 255, 0.24);
        box-shadow: 0 32px 70px -32px rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(10px);
        z-index: 1;
    }

    .hero-image-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.02);
        transition: transform 0.45s ease;
        display: block;
    }

    .hero-image-card:hover img {
        transform: scale(1.06);
    }

    .hero-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        height: 100%;
        width: 100%;
        background: linear-gradient(135deg, rgba(107, 46, 48, 0.6), rgba(15, 23, 42, 0.8));
        color: #e2e8f0;
        font-weight: 600;
        text-align: center;
    }

    .hero-image-placeholder i {
        color: #f59e0b;
        font-size: 1.75rem;
    }

    .featured-media-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.9rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #fbbf24 0%, #f97316 60%, #ea580c 100%);
        color: #0f172a;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.01em;
        box-shadow: 0 16px 38px -18px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.32);
        z-index: 3;
    }

    .featured-media-badge i {
        color: #fff8e1;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.3);
    }

    .featured-media-badge span {
        color: #0f172a;
    }
</style>
@endpush

@if (! $hostCalendarConnected)
    <div class="mb-6 rounded-3xl border border-emerald-100 bg-emerald-50/80 px-4 py-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1 text-sm text-emerald-800">
                <p class="font-semibold">{{ __('chef.workshop_form.messages.calendar_connect_required_title') }}</p>
                <p class="text-xs text-emerald-700">{{ __('chef.workshop_form.messages.calendar_connect_required_body') }}</p>
            </div>
            <a href="{{ $calendarConnectUrl }}"
               class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <i class="fas fa-link"></i>
                {{ __('chef.workshop_form.messages.calendar_connect_button') }}
            </a>
        </div>
    </div>
@endif

<div class="space-y-10">
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.basics.eyebrow') }}</p>
            <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.basics.title') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('chef.workshop_form.sections.basics.description') }}</p>
        </div>
        <div class="grid gap-5 lg:grid-cols-2">
            <div class="space-y-3">
                <label for="title" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.title.label') }}</label>
                <input type="text" id="title" name="title" required
                       value="{{ old('title', $workshop->title ?? '') }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('title') border-red-400 focus:ring-red-200 @enderror">
                @error('title')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-3">
                <label for="category" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.category.label') }}</label>
                <input type="text" id="category" name="category" required placeholder="{{ __('chef.workshop_form.placeholders.category') }}"
                       value="{{ old('category', $workshop->category ?? '') }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('category') border-red-400 focus:ring-red-200 @enderror">
                @error('category')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-3">
                <label for="level" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.level.label') }}</label>
                <select id="level" name="level" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:ring-4 focus:ring-orange-100 @error('level') border-red-400 focus:ring-red-200 @enderror">
                    @foreach ($levels as $value => $label)
                        <option value="{{ $value }}" @selected(old('level', $workshop->level ?? 'beginner') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('level')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-3">
                <label for="duration" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.duration.label') }}</label>
                <input type="number" id="duration" name="duration" required min="30" max="180" step="1"
                       value="{{ old('duration', $workshop->duration ?? 45) }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('duration') border-red-400 focus:ring-red-200 @enderror">
                @error('duration')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="mt-5 space-y-3">
            <label for="description" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.description.label') }}</label>
            <textarea id="description" name="description" rows="4" required
                      class="w-full rounded-3xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('description') border-red-400 focus:ring-red-200 @enderror"
                      placeholder="{{ __('chef.workshop_form.placeholders.description') }}">{{ old('description', $workshop->description ?? '') }}</textarea>
            @error('description')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <div class="space-y-3">
                <label for="content" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.content.label') }}</label>
                <textarea id="content" name="content" rows="4"
                          class="w-full rounded-3xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                          placeholder="{{ __('chef.workshop_form.placeholders.content') }}">{{ old('content', $workshop->content ?? '') }}</textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <label for="what_you_will_learn" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.learning_points.label') }}</label>
                    <textarea id="what_you_will_learn" name="what_you_will_learn" rows="3"
                              class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                              placeholder="{{ __('chef.workshop_form.placeholders.learning_points') }}">{{ old('what_you_will_learn', $workshop->what_you_will_learn ?? '') }}</textarea>
                </div>
                <div class="space-y-3">
                    <label for="requirements" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.requirements.label') }}</label>
                    <textarea id="requirements" name="requirements" rows="3"
                              class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                              placeholder="{{ __('chef.workshop_form.placeholders.requirements') }}">{{ old('requirements', $workshop->requirements ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.pricing.eyebrow') }}</p>
                <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.pricing.title') }}</h2>
            </div>
            <div class="rounded-full bg-orange-50 px-4 py-2 text-sm font-medium text-orange-600">
                {{ __('chef.workshop_form.sections.pricing.description') }}
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <div class="space-y-3">
                <label for="price" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.price.label') }}</label>
                <input type="number" id="price" name="price" min="0" step="0.5" required
                       value="{{ old('price', $workshop->price ?? 0) }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('price') border-red-400 focus:ring-red-200 @enderror">
                @error('price')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-3">
                <label for="currency" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.currency.label') }}</label>
                <select id="currency" name="currency" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    @foreach ($currencies as $value => $label)
                        <option value="{{ $value }}" @selected(old('currency', $workshop->currency ?? config('finance.default_currency', 'JOD')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-3">
                <label for="max_participants" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.max_participants.label') }}</label>
                <input type="number" id="max_participants" name="max_participants" min="1" max="500" required
                       value="{{ old('max_participants', $workshop->max_participants ?? 15) }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('max_participants') border-red-400 focus:ring-red-200 @enderror">
            </div>
            <div class="md:col-span-3">
                <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-amber-500 shadow-inner">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    @if (! auth()->user()?->shouldWaivePlatformFeeForOwnWorkshops())
                        <div class="space-y-1">
                            <p class="font-semibold text-amber-900">{{ __('chef.workshop_form.messages.pricing_notice_title') }}</p>
                            <p class="leading-relaxed">
                                {!! __('chef.workshop_form.messages.pricing_notice_body', ['fee_range' => '<strong>15% – 20%</strong>']) !!}
                                <br>
                                {{ __('chef.workshop_form.messages.pricing_notice_followup') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.schedule.eyebrow') }}</p>
                <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.schedule.title') }}</h2>
            </div>
            <p class="text-sm text-slate-500">{{ __('chef.workshop_form.sections.schedule.description') }}</p>
        </div>
        <div class="mb-6 space-y-3">
            <label for="host_timezone" class="text-sm font-semibold text-slate-700">
                {{ __('chef.workshop_form.fields.host_timezone.label') }}
            </label>
            <select
                id="host_timezone"
                name="host_timezone"
                required
                class="w-full rounded-2xl border border-slate-200/80 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:ring-4 focus:ring-orange-100 @error('host_timezone') border-red-400 focus:ring-red-200 @enderror"
            >
                @foreach($timezoneOptions as $tzValue => $tzLabel)
                    <option value="{{ $tzValue }}" @selected($hostTimezoneValue === $tzValue)>
                        {{ $tzLabel }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500">
                {{ __('chef.workshop_form.fields.host_timezone.helper', ['detected' => $detectedTimezone ?? __('chef.workshop_form.fields.host_timezone.unknown')]) }}
            </p>
            @error('host_timezone')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-5 rounded-2xl border border-slate-200/80 bg-slate-50 p-4 shadow-inner">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ $smartSuggestionTitle }}</p>
                    <p class="text-sm text-slate-600">{{ $smartSuggestionSub }}</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                    <i class="fas fa-globe"></i>
                    {{ $smartSuggestionTimezoneLabel }}: {{ $hostTimezoneValue }}
                </span>
            </div>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($smartSuggestions as $slot)
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-xl border border-orange-100 bg-white px-3 py-3 text-start text-sm font-semibold text-slate-800 shadow-sm transition hover:-translate-y-[1px] hover:border-orange-200 hover:shadow"
                        data-suggest-start="{{ $slot['value'] }}"
                        data-suggest-duration="{{ $durationMinutes }}"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                            <i class="fas fa-wand-magic-sparkles"></i>
                        </span>
                        <span class="leading-snug">{{ $slot['label'] }}</span>
                    </button>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ $smartSuggestionHint }}</p>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <div class="space-y-3">
                <label for="start_date" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.start_date.label') }}</label>
                <input type="datetime-local" id="start_date" name="start_date" required
                       value="{{ $startDateValue }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('start_date') border-red-400 focus:ring-red-200 @enderror">
            </div>
            <div class="space-y-3">
                <label for="end_date" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.end_date.label') }}</label>
                <input type="datetime-local" id="end_date" name="end_date" required
                       value="{{ $endDateValue }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('end_date') border-red-400 focus:ring-red-200 @enderror">
            </div>
            <div class="space-y-3">
                <p class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.registration_deadline.label') }}</p>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-800 shadow-inner">
                    {{ __('chef.workshop_form.messages.registration_deadline_notice') }}
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.delivery.eyebrow') }}</p>
                <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.delivery.title') }}</h2>
            </div>
            <div class="flex flex-col gap-1 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-2 text-emerald-700 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-video text-lg"></i>
                    <span class="text-sm font-medium">{{ __('chef.workshop_form.sections.delivery.highlight') }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold sm:text-sm">
                    <i class="fas fa-user-shield"></i>
                    @if($hostCalendarEmail)
                        <span>{{ __('chef.workshop_form.sections.delivery.host_account_label') }}:</span>
                        <span class="truncate max-w-[12rem] sm:max-w-xs">{{ $hostCalendarEmail }}</span>
                    @else
                        <span class="text-emerald-700">{{ __('chef.workshop_form.sections.delivery.host_account_missing') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                <input type="hidden" name="is_online" id="is_online" value="1">
                <div class="flex items-center gap-2">
                    <i class="fas fa-video text-emerald-600"></i>
                    <span class="font-semibold text-slate-800">{{ __('chef.workshop_form.options.online_label') }}</span>
                </div>
                <span class="text-sm text-slate-500">{{ __('chef.workshop_form.options.online_hint') }}</span>
            </div>

            <div id="onlineFields" class="{{ $isOnline ? '' : 'hidden' }} space-y-5 rounded-2xl border border-orange-100 bg-orange-50/60 p-4">
                <div class="rounded-2xl border border-white/60 bg-white/80 p-4 shadow-inner">
                    @if ($hostCalendarConnected)
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <i class="fas fa-check"></i>
                            </span>
                            <div class="space-y-1 text-sm text-slate-700">
                                <p class="font-semibold">{{ __('chef.workshop_form.messages.calendar_connected_title') }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $hostCalendarEmail
                                        ? __('chef.workshop_form.messages.calendar_connected_body_with_email', ['email' => $hostCalendarEmail])
                                        : __('chef.workshop_form.messages.calendar_connected_body')
                                    }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="space-y-1 text-sm text-slate-700">
                                <p class="font-semibold">{{ __('chef.workshop_form.messages.calendar_connect_required_title') }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ __('chef.workshop_form.messages.calendar_connect_required_body') }}
                                </p>
                            </div>
                            <a href="{{ $calendarConnectUrl }}"
                               class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <i class="fas fa-link"></i>
                                {{ __('chef.workshop_form.messages.calendar_connect_button') }}
                            </a>
                        </div>
                    @endif
                </div>

                @if ($canManageMeetingLinks)
                    <div class="flex flex-wrap items-center gap-4">
                        <input type="hidden" name="auto_generate_meeting" value="0">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="auto_generate_meeting" id="auto_generate_meeting" value="1" @checked($autoGenerateMeeting)>
                            {{ __('chef.workshop_form.options.auto_generate_label') }}
                        </label>
                        <button type="button" id="generateMeetLinkBtn"
                                data-url="{{ route('chef.workshops.generate-link') }}"
                                class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                                @if(!$hostCalendarConnected) disabled @endif>
                            <i class="fas fa-bolt"></i>
                            {{ __('chef.workshop_form.buttons.generate_link') }}
                        </button>
                    </div>
                    <div class="space-y-2">
                        <label for="meeting_link" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.meeting_link.label') }}</label>
                        <input type="url" id="meeting_link" name="meeting_link"
                               value="{{ $canManageMeetingLinks ? old('meeting_link', $workshop->meeting_link ?? '') : '' }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 @error('meeting_link') border-red-400 focus:ring-red-200 @enderror"
                               placeholder="https://meet.google.com/abc-defg-hij" {{ $autoGenerateMeeting ? 'disabled' : '' }}>
                        <p id="meetingLinkHint" class="text-xs text-slate-500">
                            {{ $autoGenerateMeeting ? __('chef.workshop_form.messages.meeting_hint_auto') : __('chef.workshop_form.messages.meeting_hint_manual') }}
                        </p>
                        <div id="meetingAppLinks" class="hidden space-y-2 rounded-2xl border border-emerald-100 bg-emerald-50/80 px-3 py-3">
                            <p class="text-xs font-semibold text-emerald-800">
                                {{ __('chef.workshop_form.messages.mobile_launch_title') }}
                            </p>
                            <p class="text-xs text-emerald-700">
                                {{ __('chef.workshop_form.messages.mobile_launch_body') }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <a id="androidMeetDeepLink"
                                   class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                   target="_blank"
                                   rel="noreferrer">
                                    <i class="fab fa-android"></i>
                                    {{ __('chef.workshop_form.messages.mobile_launch_android') }}
                                </a>
                                <a id="iosMeetDeepLink"
                                   class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 shadow-inner ring-1 ring-emerald-100 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                   target="_blank"
                                   rel="noreferrer">
                                    <i class="fab fa-apple"></i>
                                    {{ __('chef.workshop_form.messages.mobile_launch_ios') }}
                                </a>
                            </div>
                        </div>
                        @error('meeting_link')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="generatedMeetingInfo" class="space-y-2 text-sm text-emerald-700">
                        @if (($workshop->meeting_provider ?? null) === 'google_meet' && $workshop->meeting_link)
                            <div class="rounded-2xl bg-white/70 p-3 text-emerald-700 shadow-inner">
                                <p class="font-semibold">{{ __('chef.workshop_form.messages.google_ready') }}</p>
                                <p class="truncate text-sm">{{ $workshop->meeting_link }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <input type="hidden" name="auto_generate_meeting" id="auto_generate_meeting" value="1">
                    <div class="rounded-2xl border border-white/40 bg-white/80 px-4 py-3 text-sm text-slate-600 shadow-inner">
                        <p class="font-semibold text-slate-800">{{ __('chef.workshop_form.messages.managed_link_title') }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('chef.workshop_form.messages.managed_link_description') }}
                        </p>
                    </div>
                @endif
            </div>

            @if(!$isOnline)
                <div id="offlineFields" class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="space-y-2">
                        <label for="location" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.location.label') }}</label>
                        <input type="text" name="location" id="location"
                               value="{{ old('location', $workshop->location ?? '') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                    </div>
                    <div class="space-y-2">
                        <label for="address" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.address.label') }}</label>
                        <textarea id="address" name="address" rows="2"
                                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-slate-400 focus:ring-4 focus:ring-slate-100">{{ old('address', $workshop->address ?? '') }}</textarea>
                    </div>
                </div>
            @endif

            <div class="space-y-2 rounded-2xl border border-slate-100 bg-white/70 p-4 shadow-inner">
                <label for="recording_url" class="text-sm font-semibold text-slate-700">
                    {{ __('chef.workshop_form.fields.recording_url.label') }}
                </label>
                <input
                    type="url"
                    name="recording_url"
                    id="recording_url"
                    value="{{ old('recording_url', $workshop->recording_url ?? '') }}"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-inner focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 @error('recording_url') border-red-400 focus:ring-red-200 @enderror"
                    placeholder="https://drive.google.com/file/d/XXXX/view"
                >
                <p class="text-xs text-slate-500">
                    {{ __('chef.workshop_form.fields.recording_url.helper') }}
                </p>
                @error('recording_url')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.host.eyebrow') }}</p>
            <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.host.title') }}</h2>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="space-y-3">
                <label for="instructor" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.instructor.label') }}</label>
                <input type="text" id="instructor" name="instructor"
                       value="{{ old('instructor', $workshop->instructor ?? auth()->user()->name) }}"
                       class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">
            </div>
            <div class="space-y-3">
                <label for="instructor_bio" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.instructor_bio.label') }}</label>
                <textarea id="instructor_bio" name="instructor_bio" rows="3"
                          class="w-full rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-slate-900 shadow-inner focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100">{{ old('instructor_bio', $workshop->instructor_bio ?? auth()->user()->chef_specialty_description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.image.eyebrow') }}</p>
            <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.image.title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('chef.workshop_form.sections.image.description') }}</p>
        </div>
        <div class="grid gap-5 lg:grid-cols-[2fr,1fr]">
            <div class="space-y-3">
                <label for="image" class="text-sm font-semibold text-slate-700">{{ __('chef.workshop_form.fields.image.label') }}</label>
                <input type="file"
                       id="image"
                       name="image"
                       accept="image/*"
                       class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                       data-max-size="25600"
                       data-max-size-message="{{ __('chef.workshop_form.messages.image_max_size') }}"
                       data-error-target="#chef_workshop_image_error">
                <p id="chef_workshop_image_error" class="text-sm text-red-600 mt-2 hidden"></p>
                @error('image')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($workshop && $workshop->image)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_image" value="1">
                        {{ __('chef.workshop_form.buttons.remove_image') }}
                    </label>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-900 text-white shadow-sm">
                <div class="workshop-hero-visual">
                    <div class="featured-media-badge __web-inspector-hide-shortcut__">
                        <i class="fas fa-star"></i>
                        <span>Next experience</span>
                    </div>
                    <div class="hero-image-glow" aria-hidden="true"></div>
                    <div class="hero-image-card">
                        @if ($coverImageUrl)
                            <img src="{{ $coverImageUrl }}" alt="{{ __('chef.workshop_form.sections.image.preview_alt') }}" onerror="this.src='https://peahskill.com/image/logo.webp';" loading="lazy">
                        @else
                            <div class="hero-image-placeholder">
                                <i class="fas fa-image"></i>
                                <p class="text-sm">{{ __('chef.workshop_form.sections.image.preview_placeholder') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshop_form.sections.publish.eyebrow') }}</p>
                <h2 class="mt-1 text-lg sm:text-xl font-bold leading-6 text-slate-900">{{ __('chef.workshop_form.sections.publish.title') }}</h2>
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workshop->is_active ?? true))>
                {{ __('chef.workshop_form.sections.publish.auto_activate') }}
            </label>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isOnlineInput = document.getElementById('is_online');
        const onlineFields = document.getElementById('onlineFields');
        const offlineFields = document.getElementById('offlineFields');
        const autoGenerateInput = document.getElementById('auto_generate_meeting');
        const meetingLinkInput = document.getElementById('meeting_link');
        const meetingHint = document.getElementById('meetingLinkHint');
        const generateBtn = document.getElementById('generateMeetLinkBtn');
        const generatedInfo = document.getElementById('generatedMeetingInfo');
        const meetingAppLinks = document.getElementById('meetingAppLinks');
        const androidMeetLink = document.getElementById('androidMeetDeepLink');
        const iosMeetLink = document.getElementById('iosMeetDeepLink');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const durationInput = document.getElementById('duration');
        const meetingHints = {
            auto: @json(__('chef.workshop_form.messages.meeting_hint_auto')),
            manual: @json(__('chef.workshop_form.messages.meeting_hint_manual')),
        };
        const jsTranslations = {
            titleRequired: @json(__('chef.workshop_form.js.title_required')),
            generateFailed: @json(__('chef.workshop_form.js.generate_failed')),
            genericError: @json(__('chef.workshop_form.js.generic_error')),
            googleReady: @json(__('chef.workshop_form.messages.google_ready')),
        };

        const draftForm = document.querySelector('form[data-preserve-key]');
        const draftStorageKey = draftForm?.dataset?.preserveKey;
        const persistableFields = draftForm
            ? Array.from(draftForm.elements).filter((field) => {
                if (!field.name || field.name === '_token') {
                    return false;
                }
                if (['file', 'submit', 'button'].includes(field.type)) {
                    return false;
                }
                if (field.type === 'hidden') {
                    return false;
                }
                return true;
            })
            : [];

        const canUseLocalStorage = (() => {
            try {
                const testKey = '__workshop_draft__';
                localStorage.setItem(testKey, '1');
                localStorage.removeItem(testKey);
                return true;
            } catch (error) {
                return false;
            }
        })();

        function persistDraft() {
            if (!canUseLocalStorage || !draftStorageKey || !persistableFields.length) {
                return;
            }
            const payload = {};
            persistableFields.forEach((field) => {
                if (field.type === 'checkbox') {
                    payload[field.name] = field.checked;
                    return;
                }
                payload[field.name] = field.value;
            });
            localStorage.setItem(draftStorageKey, JSON.stringify(payload));
        }

        function restoreDraft() {
            if (!canUseLocalStorage || !draftStorageKey || !persistableFields.length) {
                return;
            }
            const rawDraft = localStorage.getItem(draftStorageKey);
            if (!rawDraft) {
                return;
            }
            try {
                const stored = JSON.parse(rawDraft);
                persistableFields.forEach((field) => {
                    if (!(field.name in stored)) {
                        return;
                    }
                    if (field.type === 'checkbox') {
                        field.checked = !!stored[field.name];
                        return;
                    }
                    field.value = stored[field.name];
                });
            } catch (error) {
                localStorage.removeItem(draftStorageKey);
            }
        }

        function toggleModeFields() {
            const isOnline = !!isOnlineInput?.checked;
            if (onlineFields) {
                onlineFields.classList.toggle('hidden', !isOnline);
            }
            if (offlineFields) {
                offlineFields.classList.toggle('hidden', isOnline);
            }
        }

        function toggleMeetingInputState() {
            if (!meetingLinkInput || !autoGenerateInput) {
                return;
            }
            const shouldDisable = autoGenerateInput.checked;
            meetingLinkInput.disabled = shouldDisable;
            if (meetingHint) {
                meetingHint.textContent = shouldDisable
                    ? meetingHints.auto
                    : meetingHints.manual;
            }
        }

        function buildMeetDeepLinks(rawUrl) {
            if (!rawUrl) {
                return null;
            }
            try {
                const parsed = new URL(rawUrl.trim());
                if (!parsed.hostname.includes('meet.google.com')) {
                    return null;
                }
                const parts = parsed.pathname.split('/').filter(Boolean);
                const code = parts[0];
                if (!code || !/^[a-z]{3}-[a-z]{4}-[a-z]{3}$/i.test(code)) {
                    return null;
                }
                const normalizedCode = code.toLowerCase();
                const canonical = `https://meet.google.com/${normalizedCode}`;
                return {
                    canonical,
                    android: `intent://meet.google.com/${normalizedCode}#Intent;scheme=https;package=com.google.android.apps.meetings;end`,
                    ios: canonical,
                };
            } catch (error) {
                return null;
            }
        }

        function refreshMeetAppLinks() {
            if (!meetingAppLinks || !androidMeetLink || !iosMeetLink || !meetingLinkInput) {
                return;
            }
            const links = buildMeetDeepLinks(meetingLinkInput.value);
            if (!links) {
                meetingAppLinks.classList.add('hidden');
                return;
            }
            androidMeetLink.href = links.android;
            iosMeetLink.href = links.ios;
            meetingAppLinks.classList.remove('hidden');
        }

        function formatDateTimeLocal(date) {
            const pad = (value) => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function resolveDurationMinutes(fallback = 90) {
            if (!durationInput) {
                return fallback;
            }
            const parsed = parseInt(durationInput.value, 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
        }

        function computeDurationFromDates() {
            if (!startInput || !endInput) {
                return null;
            }
            const startDate = new Date(startInput.value);
            const endDate = new Date(endInput.value);
            if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
                return null;
            }
            const diffMs = endDate.getTime() - startDate.getTime();
            if (diffMs <= 0) {
                return null;
            }
            return Math.round(diffMs / 60000);
        }

        function syncDurationFromDates() {
            const minutes = computeDurationFromDates();
            if (!durationInput || minutes === null) {
                return;
            }
            durationInput.value = minutes;
        }

        function syncEndFromDuration() {
            if (!startInput || !endInput) {
                return;
            }
            const startDate = new Date(startInput.value);
            if (Number.isNaN(startDate.getTime())) {
                return;
            }
            const minutes = resolveDurationMinutes();
            const endDate = new Date(startDate.getTime() + minutes * 60000);
            endInput.value = formatDateTimeLocal(endDate);
            endInput.dispatchEvent(new Event('change'));
        }

        function applySuggestedSlot(startValue, suggestedDuration) {
            if (!startInput) return;
            const minutes = Number.isFinite(suggestedDuration) && suggestedDuration > 0
                ? suggestedDuration
                : resolveDurationMinutes();

            startInput.value = startValue;

            const startDate = new Date(startValue);
            if (endInput && !Number.isNaN(startDate.getTime())) {
                const endDate = new Date(startDate.getTime() + minutes * 60000);
                endInput.value = formatDateTimeLocal(endDate);
                endInput.dispatchEvent(new Event('change'));
            }
            startInput.dispatchEvent(new Event('change'));
        }

        isOnlineInput?.addEventListener('change', toggleModeFields);
        autoGenerateInput?.addEventListener('change', () => {
            toggleMeetingInputState();
            refreshMeetAppLinks();
        });
        meetingLinkInput?.addEventListener('input', refreshMeetAppLinks);
        meetingLinkInput?.addEventListener('change', refreshMeetAppLinks);
        if (canUseLocalStorage && draftStorageKey && persistableFields.length) {
            restoreDraft();
            persistableFields.forEach((field) => {
                const eventName = field.type === 'checkbox' || field.tagName === 'SELECT' ? 'change' : 'input';
                field.addEventListener(eventName, persistDraft);
            });
            draftForm?.addEventListener('submit', () => {
                localStorage.removeItem(draftStorageKey);
            });
        }
        toggleModeFields();
        toggleMeetingInputState();
            refreshMeetAppLinks();

        document.querySelectorAll('[data-suggest-start]').forEach((button) => {
            button.addEventListener('click', () => {
                applySuggestedSlot(
                    button.dataset.suggestStart,
                    parseInt(button.dataset.suggestDuration, 10)
                );
            });
        });

        startInput?.addEventListener('change', () => {
            syncDurationFromDates();
        });

        endInput?.addEventListener('change', () => {
            syncDurationFromDates();
        });

        durationInput?.addEventListener('input', () => {
            syncEndFromDuration();
        });

        generateBtn?.addEventListener('click', async () => {
            if (!csrfToken) return;
            const title = document.getElementById('title')?.value;
            const startDate = document.getElementById('start_date')?.value;

            if (!title) {
                alert(jsTranslations.titleRequired);
                return;
            }

            generateBtn.disabled = true;
            generateBtn.classList.add('opacity-70');
            try {
                const response = await fetch(generateBtn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title,
                        start_date: startDate,
                    }),
                });

                if (!response.ok) {
                    throw new Error(jsTranslations.generateFailed);
                }

                const data = await response.json();
                if (meetingLinkInput) {
                    meetingLinkInput.value = data.meeting_link;
                    refreshMeetAppLinks();
                }

                if (generatedInfo) {
                    generatedInfo.innerHTML = `
                        <div class="rounded-2xl bg-white/70 p-3 text-emerald-700 shadow-inner">
                            <p class="font-semibold">${jsTranslations.googleReady}</p>
                            <p class="mt-1 truncate text-sm">${data.meeting_link}</p>
                        </div>
                    `;
                }

                if (autoGenerateInput) {
                    autoGenerateInput.checked = true;
                }
                toggleMeetingInputState();
            } catch (error) {
                alert(error.message || jsTranslations.genericError);
            } finally {
                generateBtn.disabled = false;
                generateBtn.classList.remove('opacity-70');
            }
        });
    });
</script>
@endpush









