@extends('layouts.app')

@section('title', __('home.meta.title'))

@php
    $primaryHeroSlide = $heroSlides[0] ?? null;
    $primaryHeroDesktop = data_get($primaryHeroSlide, 'image');
    $primaryHeroMobile = data_get($primaryHeroSlide, 'mobile_image', $primaryHeroDesktop);
    $primaryHeroIsVideo = $primaryHeroSlide && (
        \Illuminate\Support\Str::endsWith($primaryHeroDesktop, '.webm') ||
        ($primaryHeroMobile && \Illuminate\Support\Str::endsWith($primaryHeroMobile, '.webm'))
        );
    $primaryHeroHasMedia = filled($primaryHeroDesktop);
    $heroSizesAttribute = '(max-width: 640px) 100vw, (max-width: 1024px) 75vw, 60vw';
@endphp

@push('preloads')
    @if($primaryHeroHasMedia)
        @if($primaryHeroIsVideo)
            <link rel="preload" as="video" href="{{ $primaryHeroDesktop }}" type="video/webm">
            @if($primaryHeroMobile && $primaryHeroMobile !== $primaryHeroDesktop)
                <link rel="preload" as="video" href="{{ $primaryHeroMobile }}" type="video/webm" media="(max-width: 640px)">
            @endif
        @else
            @php
                $primaryHeroSrcSet = $primaryHeroMobile && $primaryHeroMobile !== $primaryHeroDesktop
                    ? "{$primaryHeroMobile} 640w, {$primaryHeroDesktop} 1280w"
                    : null;
            @endphp
            <link
                rel="preload"
                as="image"
                href="{{ $primaryHeroDesktop }}"
                fetchpriority="high"
                @if($primaryHeroSrcSet)
                    imagesrcset="{{ $primaryHeroSrcSet }}"
                    imagesizes="{{ $heroSizesAttribute }}"
                @endif
            >
        @endif
    @endif
@endpush

@push('styles')
    @include('home.partials.styles')
@endpush

@section('content')
    @php
        $showAdminMetrics = auth()->check() && auth()->user()->isAdmin();
        $showToolsForAdmin = $showToolsForAdmin ?? false;
        $canAccessRecordManagement = auth()->user()?->canAccessRecordManagement() ?? false;
        $defaultAppTimezone = config('app.timezone', 'UTC');

        $normalizeTimezone = function (?string $timezone, ?string $fallback = null) use ($defaultAppTimezone) {
            $fallbackTz = $fallback ?: $defaultAppTimezone;

            return \App\Support\Timezones::isValid($timezone) ? $timezone : $fallbackTz;
        };

        $viewerPreferredTimezone = auth()->user()->timezone ?? request()->cookie('user_timezone') ?? null;

        $formatWorkshopSchedule = function (\App\Models\Workshop $workshop) use ($normalizeTimezone, $viewerPreferredTimezone) {
            $eventTimezone = $normalizeTimezone($workshop->host_timezone);
            $viewerTimezone = $normalizeTimezone($viewerPreferredTimezone, $eventTimezone);
            $startUtc = $workshop->start_date_utc;
            $endUtc = $workshop->end_date_utc;
            $format = 'd F Y • h:i a T';

            $eventStart = $startUtc ? $startUtc->copy()->setTimezone($eventTimezone)->translatedFormat($format) : null;
            $eventEnd = $endUtc ? $endUtc->copy()->setTimezone($eventTimezone)->translatedFormat($format) : null;
            $viewerStart = $startUtc ? $startUtc->copy()->setTimezone($viewerTimezone)->translatedFormat($format) : null;
            $viewerEnd = $endUtc ? $endUtc->copy()->setTimezone($viewerTimezone)->translatedFormat($format) : null;

            return [
                'event_timezone' => $eventTimezone,
                'viewer_timezone' => $viewerTimezone,
                'event_start' => $eventStart,
                'event_end' => $eventEnd,
                'viewer_start' => $viewerStart,
                'viewer_end' => $viewerEnd,
                'has_viewer_diff' => ($eventStart && $viewerStart) && ($eventStart !== $viewerStart || $eventTimezone !== $viewerTimezone),
            ];
        };
    @endphp
    <!-- قسم المحتوى الرئيسي -->
    <main class="container mx-auto px-4 pt-0 pb-6 lg:pt-0 lg:pb-8">
        @if(request()->header('X-Mobile-Tab-Bar'))
            <div data-inline-style="home" hidden aria-hidden="true">
                @include('home.partials.styles')
            </div>
        @endif
        <div class="home-hero-shell">
            <div class="home-hero-grid">
                <!-- القسم الرئيسي -->
                <article class="hero-main-card">
                    <div class="hero-slider swiper">
                        <div class="swiper-wrapper">
                            @foreach($heroSlides as $slide)
                                <div class="swiper-slide">
                                    <div class="hero-slide">
                                        <div class="hero-media">
                                            @php
                                                $desktopMedia = $slide['image'] ?? '';
                                                $mobileMedia = $slide['mobile_image'] ?? $desktopMedia;
                                                $hasVideoMedia = \Illuminate\Support\Str::endsWith($desktopMedia, '.webm') || \Illuminate\Support\Str::endsWith($mobileMedia, '.webm');
                                                $isPrimarySlide = $loop->first;
                                                $heroImageLoading = $isPrimarySlide ? 'eager' : 'lazy';
                                                $heroFetchPriority = $isPrimarySlide ? 'high' : 'auto';
                                                $heroVideoPreload = $isPrimarySlide ? 'auto' : 'metadata';
                                                $heroSrcSet = $mobileMedia && $mobileMedia !== $desktopMedia
                                                    ? "{$mobileMedia} 640w, {$desktopMedia} 1280w"
                                                    : null;
                                            @endphp

                                            @if($hasVideoMedia)
                                                <video
                                                    class="hero-main-image"
                                                    autoplay
                                                    muted
                                                    loop
                                                    playsinline
                                                    preload="{{ $heroVideoPreload }}"
                                                    width="1280"
                                                    height="720"
                                                >
                                                    @if($mobileMedia && $mobileMedia !== $desktopMedia)
                                                        <source src="{{ $mobileMedia }}" media="(max-width: 640px)" type="video/webm">
                                                    @endif
                                                    <source src="{{ $desktopMedia }}" type="video/webm">
                                                    {{ __('home.hero.video_fallback') }}
                                                </video>
                                            @else
                                                <picture>
                                                    @if($mobileMedia && $mobileMedia !== $desktopMedia)
                                                        <source media="(max-width: 640px)" srcset="{{ $mobileMedia }}">
                                                    @endif
                                                    <img
                                                        src="{{ $desktopMedia }}"
                                                        alt="{{ $slide['image_alt'] }}"
                                                        class="hero-main-image"
                                                        loading="{{ $heroImageLoading }}"
                                                        fetchpriority="{{ $heroFetchPriority }}"
                                                        decoding="async"
                                                        width="1280"
                                                        height="720"
                                                        @if($heroSrcSet)
                                                            srcset="{{ $heroSrcSet }}"
                                                            sizes="{{ $heroSizesAttribute }}"
                                                        @endif
                                                    >
                                                </picture>
                                            @endif
                                        </div>
                                        <div class="hero-content">
                                            @if(!empty($slide['badge']))
                                                <span class="hero-badge">{{ $slide['badge'] }}</span>
                                            @endif
                                            <h1 class="hero-title">{{ $slide['title'] }}</h1>
                                            <p class="hero-description">{{ $slide['description'] }}</p>
                                            @if(!empty($slide['features']))
                                                <ul class="hero-features">
                                                    @foreach($slide['features'] as $feature)
                                                        <li class="hero-feature">
                                                            <i class="fas fa-check-circle"></i>
                                                            <span>{{ $feature }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if(!empty($slide['actions']))
                                                @php
                                                    $hasMultipleActions = count($slide['actions']) > 1;
                                                @endphp
                                                <div class="hero-actions{{ $hasMultipleActions ? ' hero-actions--balanced' : '' }}">
                                                    @foreach($slide['actions'] as $action)
                                                        @php
                                                            $actionType = $action['type'] ?? 'primary';
                                                            $actionClass = $actionType === 'secondary'
                                                                ? 'secondary-action'
                                                                : ($actionType === 'accent' ? 'accent-action' : 'primary-action');
                                                            $openInNewTab = !empty($action['open_in_new_tab']);
                                                        @endphp
                                                        <a href="{{ $action['url'] ?? '#' }}"
                                                           class="hero-action {{ $actionClass }}"
                                                           @if($openInNewTab) target="_blank" rel="noopener noreferrer" @endif>
                                                            <span>{{ $action['label'] }}</span>
                                                            @if(!empty($action['icon']))
                                                                <i class="{{ $action['icon'] }}"></i>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
                
                <!-- قسم أحدث الورشات -->
                <aside class="hero-latest-card">
                    <div class="hero-latest-header">
                        <h2>{{ __('home.latest_workshops.title') }}</h2>
                        <a href="{{ route('workshops') }}" class="hero-latest-link">
                            {{ __('home.latest_workshops.cta') }}
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                    </div>
                    <ul class="hero-latest-list">
                        @forelse($latestWorkshops as $workshop)
                            <x-latest-workshop-item :workshop="$workshop" />
                        @empty
                            <li class="hero-latest-empty">
                                <div class="hero-latest-empty-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <p>{{ __('home.latest_workshops.empty_title') }}</p>
                            </li>
                        @endforelse
                    </ul>
                </aside>
            </div>
        </div>
    </main>

    <!-- شريط احترافي لإدارة التسجيلات -->
    @if($canAccessRecordManagement)
        @php
            $heroSlidesCount = isset($heroSlides) && is_iterable($heroSlides) ? count($heroSlides) : 0;
            $latestWorkshopsCount = isset($latestWorkshops) && is_iterable($latestWorkshops) ? count($latestWorkshops) : 0;
        @endphp
        <section class="container mx-auto px-4 pb-4 sm:pb-6 lg:pb-8">
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white shadow-2xl">
                <div class="absolute inset-0 bg-gradient-to-l from-orange-600 via-amber-500/80 to-slate-900 opacity-90"></div>
                <div class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-amber-200/20 blur-3xl"></div>
                <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-4 max-w-2xl">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-amber-100 ring-1 ring-white/15">
                            <i class="fa-solid fa-shield-halved text-amber-200"></i>
                            لوحة تحكم التسجيلات
                        </div>
                        <div class="space-y-2">
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-tight">إدارة التسجيلات باحترافية وسرعة</h2>
                            <p class="text-sm sm:text-base text-amber-50/90">
                                تحكم بالتسجيلات، شاركها مع المستخدمين المصرح لهم فقط، وراقب الوصول في مكان واحد بتصميم واضح ومتدرج الألوان.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-amber-100">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/15">
                                <i class="fa-solid fa-lock-open"></i> تحكم بالصلاحيات
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/15">
                                <i class="fa-solid fa-user-shield"></i> مشاركة آمنة
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/15">
                                <i class="fa-solid fa-gauge-high"></i> واجهة سريعة
                            </span>
                        </div>
                    </div>

                    <div class="w-full max-w-xl space-y-3">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                <p class="text-xs text-amber-100 font-semibold">التسجيلات المميزة</p>
                                <div class="mt-2 flex items-end gap-2">
                                    <span class="text-3xl font-extrabold">{{ max($heroSlidesCount, 1) }}</span>
                                    <span class="text-sm text-amber-100">عرض رئيسي</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                <p class="text-xs text-amber-100 font-semibold">ورش محدثة</p>
                                <div class="mt-2 flex items-end gap-2">
                                    <span class="text-3xl font-extrabold">{{ $latestWorkshopsCount }}</span>
                                    <span class="text-sm text-amber-100">آخر الإضافات</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 sm:col-span-1">
                                <p class="text-xs text-amber-100 font-semibold">رضا المستخدمين</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-3xl font-extrabold">4.9</span>
                                    <i class="fa-solid fa-star text-amber-300"></i>
                                </div>
                                <p class="text-[11px] text-amber-100/90">تجربة مشاهدة متكاملة</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('bookings.recordings') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white text-slate-900 px-5 py-3 text-sm font-bold shadow-lg shadow-amber-500/30 transition hover:-translate-y-0.5 hover:shadow-xl">
                                <i class="fa-solid fa-display"></i>
                                إدارة التسجيلات الآن
                            </a>
                            <a href="{{ route('workshops') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/20">
                                <i class="fa-solid fa-calendar-check"></i>
                                اكتشف الورش القادمة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- قسم الورشة المميزة (الورشة القادمة) -->
    @if($featuredWorkshop)
    @php 
        $featuredIsFull = $featuredWorkshop->bookings_count >= $featuredWorkshop->max_participants; 
        $featuredIsRegistrationClosed = !$featuredWorkshop->is_registration_open;
        $featuredIsCompleted = $featuredWorkshop->is_completed;
        $featuredLocationLabel = $featuredWorkshop->is_online
            ? __('home.labels.online_workshop')
            : ($featuredWorkshop->location ?? __('home.labels.offline_workshop'));
        $featuredDeadlineLabel = $featuredWorkshop->registration_deadline ? $featuredWorkshop->registration_deadline->format('d/m/Y') : __('home.labels.unspecified');
        $featuredInstructorLabel = $featuredWorkshop->instructor ?? __('home.labels.unspecified');
        $featuredIsBooked = !empty($bookedWorkshopIds) && in_array($featuredWorkshop->id, $bookedWorkshopIds, true);
        $featuredDescriptionFull = $featuredWorkshop->featured_description ?: $featuredWorkshop->description;
        $featuredDescriptionPreview = \Illuminate\Support\Str::limit($featuredDescriptionFull, 180, '…');
        $featuredDescriptionIsTrimmed = $featuredDescriptionPreview !== $featuredDescriptionFull;
        $featuredSchedule = $formatWorkshopSchedule($featuredWorkshop);
    @endphp
    <section class="container mx-auto px-4 py-8 sm:py-12 featured-workshop-section">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-3xl overflow-hidden featured-workshop-card">
            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_1.4fr] gap-0">
                <!-- المحتوى النصي -->
                <div class="p-5 sm:p-6 lg:p-9 text-white flex flex-col justify-center">
                    <div class="mb-5">
                        <span class="bg-white/20 text-white text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full inline-block mb-3 sm:mb-4">
                            {{ __('home.featured_workshop.title') }}
                        </span>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-3 leading-tight">
                            {{ $featuredWorkshop->title }}
                        </h2>
                        <div class="text-sm sm:text-base text-amber-100 mb-4 sm:mb-5 leading-relaxed space-y-2">
                            <p id="featured-desc"
                               class="line-clamp-3"
                               data-preview-text="{{ e($featuredDescriptionPreview) }}"
                               data-full-text="{{ e($featuredDescriptionFull) }}">
                                {{ $featuredDescriptionPreview }}
                            </p>
                            @if($featuredDescriptionIsTrimmed)
                                <button type="button"
                                        class="inline-flex items-center gap-2 text-white text-xs font-semibold underline decoration-white/60 underline-offset-4 hover:decoration-white transition"
                                        data-toggle="featured-desc">
                                    <span class="toggle-label">عرض المزيد</span>
                                    <i class="fas fa-chevron-down text-[10px]" aria-hidden="true"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- تفاصيل الورشة -->
                    <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-5">
                        <div class="flex items-start gap-3 text-amber-100">
                            <i class="fas fa-calendar-alt w-5 text-center mt-0.5"></i>
                            <div class="space-y-1">
                                <div class="font-semibold text-sm sm:text-base">
                                    وقت الحدث ({{ $featuredSchedule['event_timezone'] }}):
                                </div>
                                <div class="font-medium text-sm sm:text-base">
                                    {{ $featuredSchedule['event_start'] ?? __('home.labels.unspecified') }}
                                </div>
                                @if($featuredSchedule['event_end'])
                                    <div class="text-xs sm:text-sm text-amber-50/90">
                                        ينتهي عند: {{ $featuredSchedule['event_end'] }}
                                    </div>
                                @endif
                                @if($featuredSchedule['has_viewer_diff'])
                                    <div class="text-xs sm:text-sm text-amber-50/90">
                                        وقتك المحلي ({{ $featuredSchedule['viewer_timezone'] }}): {{ $featuredSchedule['viewer_start'] }}
                                    </div>
                                    @if($featuredSchedule['viewer_end'])
                                        <div class="text-[11px] sm:text-xs text-amber-50/80">
                                            ينتهي (وقتك): {{ $featuredSchedule['viewer_end'] }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-amber-100">
                            <i class="fas {{ $featuredWorkshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }} w-5 text-center"></i>
                            <span class="font-medium text-sm sm:text-base">{{ $featuredLocationLabel }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-amber-100">
                            <i class="fas fa-user w-5 text-center"></i>
                            <span class="font-medium text-sm sm:text-base">{{ __('home.labels.with') }} {{ $featuredInstructorLabel }}</span>
                        </div>
                        @if($showAdminMetrics)
                            <div class="flex items-center gap-3 text-amber-100">
                                <i class="fas fa-users w-5 text-center"></i>
                                <span class="font-medium text-sm sm:text-base">{{ $featuredWorkshop->bookings_count }}/{{ $featuredWorkshop->max_participants }} {{ __('home.featured_workshop.participant_label') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- أزرار الإجراءات -->
                    <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 featured-workshop-actions">
                        @if($featuredIsCompleted)
                            <button type="button" class="bg-gray-400 text-gray-600 font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg w-full sm:w-auto">
                                <i class="fas fa-check-circle mr-2 rtl:ml-2 text-lg sm:text-xl"></i>
                                {{ __('home.featured_workshop.status_full') }}
                            </button>
                        @elseif($featuredIsBooked)
                            <button type="button" class="bg-green-500 text-white font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg w-full sm:w-auto" disabled>
                                <i class="fas fa-check mr-2 rtl:ml-2 text-lg sm:text-xl booking-button-icon"></i>
                                <span class="booking-button-label">{{ __('home.featured_workshop.status_booked') }}</span>
                            </button>
                        @elseif($featuredIsFull)
                            <button type="button" class="bg-gray-400 text-gray-600 font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg w-full sm:w-auto">
                                <i class="fas fa-lock mr-2 rtl:ml-2 text-lg sm:text-xl"></i>
                                {{ __('home.featured_workshop.status_full') }}
                            </button>
                        @elseif($featuredIsRegistrationClosed)
                            <button type="button" class="bg-yellow-400 text-yellow-800 font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg w-full sm:w-auto">
                                <i class="fas fa-clock mr-2 rtl:ml-2 text-lg sm:text-xl"></i>
                                {{ __('home.featured_workshop.status_closed') }}
                            </button>
                        @else
                            <a href="{{ route('workshop.show', $featuredWorkshop->slug) }}#workshop-booking"
                               class="bg-white text-green-600 hover:bg-green-50 font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl w-full sm:w-auto">
                                <i class="fas fa-calendar-check mr-2 rtl:ml-2 text-lg sm:text-xl booking-button-icon"></i>
                                <span class="booking-button-label">{{ __('home.featured_workshop.primary_cta') }}</span>
                            </a>
                        @endif
                        <a href="{{ route('workshop.show', $featuredWorkshop->slug) }}" 
                           class="border-2 border-white text-white hover:bg-white hover:text-amber-600 font-bold text-sm sm:text-base py-2.5 px-4 sm:py-3 sm:px-6 rounded-lg sm:rounded-xl transition-all duration-300 flex items-center justify-center w-full sm:w-auto">
                            <i class="fas fa-info-circle mr-2 rtl:ml-2"></i>
                            {{ __('home.featured_workshop.secondary_cta') }}
                        </a>
                    </div>
                </div>
                
                <!-- الصورة -->
                <div class="relative h-48 sm:h-56 lg:h-auto overflow-hidden featured-workshop-media">
                    <div class="featured-media-backdrop" aria-hidden="true"></div>
                    <img src="{{ $featuredWorkshop->image ? asset('storage/' . $featuredWorkshop->image) : 'https://placehold.co/800x600/f87171/FFFFFF?text=Premium+Workshop' }}"
                         alt="{{ $featuredWorkshop->title }}"
                         class="w-full h-full object-cover featured-workshop-image"
                         width="1200"
                         height="900"
                         loading="lazy"
                         decoding="async">
                    <div class="featured-media-overlay" aria-hidden="true"></div>
                    <div class="featured-media-badge">
                        <i class="fas fa-star"></i>
                        <span>{{ __('home.featured_workshop.title') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($showToolsForAdmin)
    <!-- قسم أدوات الشيف -->
    <section class="container mx-auto px-4 py-10 lg:py-14">
        <div class="home-tools-section p-6 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8 lg:mb-10">
                <div class="text-left rtl:text-right max-w-2xl mx-auto lg:mx-0">
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-amber-600 bg-amber-100/70 px-4 py-2 rounded-full mb-3">
                        <i class="fas fa-toolbox text-sm"></i>
                        {{ __('home.tools.eyebrow') }}
                    </span>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('home.tools.title') }}</h2>
                    <p class="text-gray-600 text-base lg:text-lg">
                        {{ __('home.tools.subtitle') }}
                    </p>
                </div>
                @if($homeTools->count() > 0)
                    <div class="home-tools-nav hidden lg:flex">
                        <button type="button" class="home-tools-prev" aria-label="{{ __('home.tools.prev') }}">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="button" class="home-tools-next" aria-label="{{ __('home.tools.next') }}">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                @endif
            </div>

            @if($homeTools->count() > 0)
                <div class="swiper home-tools-swiper">
                    <div class="swiper-wrapper">
                        @foreach($homeTools as $tool)
                            <div class="swiper-slide">
                                <article class="home-tool-card">
                                    <div class="home-tool-card__image">
                                        <img src="{{ $tool->image_url }}" alt="{{ $tool->name }}" loading="lazy" decoding="async" width="320" height="240" onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'">
                                        @if(!empty($tool->category))
                                            <span class="home-tool-card__category">{{ $tool->category }}</span>
                                        @endif
                                    </div>
                                    <div class="home-tool-card__body">
                                        <h3 class="home-tool-card__title line-clamp-2">{{ $tool->name }}</h3>
                                        <div class="home-tool-card__rating">
                                            <i class="fas fa-star"></i>
                                            <span>{{ number_format($tool->rating ?? 0, 1) }}</span>
                                            <span class="text-gray-400 text-sm">/ 5</span>
                                        </div>
                                        @if(!is_null($tool->price))
                                            <div class="home-tool-card__price">{{ number_format($tool->price, 2) }} {{ __('home.labels.currency_suffix') }}</div>
                                        @endif
                                        <div class="home-tool-card__actions">
                                            <a href="{{ route('tools.show', $tool->id) }}" class="hero-action secondary-action text-center text-sm">
                                                <span>{{ __('home.tools.details') }}</span>
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                            @if($tool->amazon_url)
                                                <a href="{{ $tool->amazon_url }}" target="_blank" rel="nofollow noopener" class="hero-action primary-action text-center text-sm">
                                                    <span>{{ __('home.tools.buy_now') }}</span>
                                                    <i class="fas fa-shopping-cart"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('tools') }}" class="hero-action primary-action text-center text-sm">
                                                    <span>{{ __('home.tools.see_more') }}</span>
                                                    <i class="fas fa-arrow-left"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-amber-100 flex items-center justify-center text-amber-500">
                        <i class="fas fa-toolbox text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">{{ __('home.tools.empty_title') }}</h3>
                    <p class="text-gray-600 max-w-lg mx-auto mb-6">
                        {{ __('home.tools.empty_description') }}
                    </p>
                    <a href="{{ route('tools') }}" class="hero-action primary-action">
                        <span>{{ __('home.tools.empty_cta') }}</span>
                        <i class="fas fa-toolbox"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif

    <!-- قسم فيديوهات الخلاصة -->
    <section class="container mx-auto px-4 py-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-2 sm:px-4 lg:px-6 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('home.featured_recipes.title') ?? 'الفيديوهات' }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('home.featured_recipes.subtitle') }}</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-3xl bg-transparent text-gray-900 feed-videos-shell">
            @if($feedVideos->count() > 0)
                <div class="feed-videos-meta">
                    <div class="feed-videos-chip">
                        <i class="fas fa-clapperboard text-xs"></i>
                        <span>{{ app()->getLocale() === 'ar' ? 'ريلز' : 'Reels' }}</span>
                    </div>
                </div>
                <div class="feed-videos-nav" hidden>
                    <button type="button" class="feed-videos-prev" aria-label="{{ __('pagination.previous') }}" hidden>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="feed-videos-next" aria-label="{{ __('pagination.next') }}" hidden>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="feed-videos-fade feed-videos-fade-left" aria-hidden="true"></div>
                <div class="feed-videos-fade feed-videos-fade-right" aria-hidden="true"></div>
                <div class="swiper feed-videos-swiper px-2 sm:px-4 lg:px-6 py-8">
                    <div class="swiper-wrapper">
                        @foreach($feedVideos as $video)
                            <div class="swiper-slide">
                                <x-feed-video-card :video="$video" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="feed-videos-pagination pb-4 px-2 sm:px-4 lg:px-6" aria-hidden="true" hidden></div>
            @else
                <div class="px-6 lg:px-10 py-12 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-white/10 border border-white/10 flex items-center justify-center shadow-lg">
                        <i class="fas fa-play text-xl text-amber-300"></i>
                    </div>
                    <h3 class="text-2xl font-bold">{{ __('home.featured_recipes.empty_title') }}</h3>
                    <p class="text-white/70 max-w-2xl mx-auto">{{ __('home.featured_recipes.empty_description') }}</p>
                    <a href="{{ route('recipes') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white text-slate-900 font-semibold hover:shadow-lg transition-shadow">
                        <span>{{ __('home.featured_recipes.empty_cta') }}</span>
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- قسم الورشات المحسن -->
    <section class="py-12 bg-gradient-to-br from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">{{ __('home.premium_workshops.title') }}</h2>
                <p class="text-gray-600 text-lg max-w-3xl mx-auto mb-6">{{ __('home.premium_workshops.subtitle') }}</p>
                <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 font-semibold text-lg transition-colors">
                    {{ __('home.premium_workshops.cta') }}
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
            </div>
            
            @if($workshops->count() > 0)
                <div class="featured-workshops-grid">
                    @foreach($workshops as $workshop)
                        @php
                            $isFull = $workshop->bookings_count >= $workshop->max_participants;
                            $isRegistrationClosed = !$workshop->is_registration_open;
                            $isCompleted = $workshop->is_completed;
                            $isBooked = !empty($bookedWorkshopIds) && in_array($workshop->id, $bookedWorkshopIds, true);
                            $bookingLocationLabel = $workshop->is_online
                                ? __('home.labels.online_workshop')
                                : ($workshop->location ?? __('home.labels.offline_workshop'));
                            $bookingDeadlineLabel = $workshop->registration_deadline ? $workshop->registration_deadline->format('d/m/Y') : __('home.labels.unspecified');
                            $bookingInstructor = $workshop->instructor ?? __('home.labels.unspecified');
                            $bookingButtonStateClasses = $isBooked
                                ? 'bg-green-500 text-white cursor-not-allowed'
                                : 'bg-green-500 hover:bg-green-600 text-white';

                            // Debug information (remove in production)
                            // Uncomment the line below to debug registration status
                            // dd([
                            //     'workshop_id' => $workshop->id,
                            //     'title' => $workshop->title,
                            //     'registration_deadline' => $workshop->registration_deadline,
                            //     'is_upcoming' => $workshop->is_upcoming,
                            //     'is_registration_open' => $workshop->is_registration_open,
                            //     'isRegistrationClosed' => $isRegistrationClosed,
                            //     'now' => now(),
                            // ]);

                            // Temporary debug display (remove in production)
                            // Uncomment the lines below to see debug information
                            // @if($isRegistrationClosed)
                            //     <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-2">
                            //         DEBUG: Registration is closed for {{ $workshop->title }}
                            //     </div>
                            // @endif
                            $workshopSchedule = $formatWorkshopSchedule($workshop);
                        @endphp
                        <div class="flex">
                            <div class="workshop-card bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full w-full">
                                        <div class="relative premium-workshop-media">
                                            <img src="{{ $workshop->image ? asset('storage/' . $workshop->image) : 'https://placehold.co/600x400/f87171/FFFFFF?text=Workshop' }}" 
                                                 alt="{{ $workshop->title }}"
                                                 width="600"
                                                 height="400"
                                                 decoding="async"
                                                 onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ __('home.premium_workshops.placeholder_image_alt') }}';" loading="lazy">
                                            @if($isFull)
                                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                                <span class="text-white font-bold text-lg bg-red-500 px-4 py-2 rounded-full">{{ __('home.premium_workshops.status_full') }}</span>
                                            </div>
                                            @elseif($isRegistrationClosed)
                                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                                <span class="text-white font-bold text-lg bg-yellow-500 px-4 py-2 rounded-full">
                                                    <i class="fas fa-clock mr-2 rtl:ml-2"></i>
                                                    {{ __('home.premium_workshops.status_closed') }}
                                                </span>
                                            </div>
                                            @endif
                                            <div class="absolute top-4 right-4 bg-orange-500 text-white text-sm font-semibold px-3 py-1 rounded-full">{{ $workshop->price }} {{ $workshop->currency }}</div>
                                        </div>
                                        
                                        <div class="p-6 flex flex-col flex-grow">
                                            <div class="mb-2">
                                                <span class="text-sm font-semibold {{ $workshop->is_online ? 'text-blue-600' : 'text-green-600' }}">
                                                    {{ $workshop->is_online ? __('home.labels.online_short') : __('home.labels.onsite_short') }}
                                                </span>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $workshop->title }}</h3>
                                            <p class="text-gray-600 mb-4">{{ __('home.labels.with') }} {{ $bookingInstructor }}</p>
                                            <div class="flex items-start text-gray-700 text-sm mb-4 gap-3">
                                                <i class="fas fa-calendar-alt text-gray-500 mt-0.5 mr-2 rtl:ml-2"></i>
                                                <div class="space-y-1">
                                                    <div class="font-semibold text-gray-800 text-sm sm:text-base">
                                                        وقت الحدث ({{ $workshopSchedule['event_timezone'] }}):
                                                    </div>
                                                    <div class="text-gray-700">
                                                        {{ $workshopSchedule['event_start'] ?? __('home.labels.unspecified') }}
                                                    </div>
                                                    @if($workshopSchedule['has_viewer_diff'])
                                                        <div class="text-gray-500 text-xs sm:text-sm">
                                                            وقتك المحلي ({{ $workshopSchedule['viewer_timezone'] }}): {{ $workshopSchedule['viewer_start'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center text-gray-500 text-sm mb-4">
                                                <i class="fas fa-map-marker-alt mr-2 rtl:ml-2"></i> {{ $workshop->is_online ? __('home.labels.live_online') : ($workshop->location ?? __('home.labels.unspecified')) }}
                                            </div>
                                            @if($showAdminMetrics)
                                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                                    <i class="fas fa-users mr-2 rtl:ml-2"></i> {{ $workshop->bookings_count }}/{{ $workshop->max_participants }} {{ __('home.premium_workshops.participants_label') }}
                                                </div>
                                            @endif
                                            
                                            <!-- الأزرار المثبتة في الأسفل -->
                                            <div class="mt-auto pt-4 border-t border-gray-100">
                                                <div class="flex gap-3 items-center">
                                                    @if($isCompleted)
                                                    <button type="button" class="flex-1 bg-gray-400 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                                        <i class="fas fa-check-circle mr-2 rtl:ml-2"></i>
                                                        {{ __('home.featured_workshop.status_full') }}
                                                    </button>
                                                    @elseif($isBooked)
                                                    <button type="button" class="flex-1 bg-green-500 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm" disabled>
                                                        <i class="fas fa-check mr-2 rtl:ml-2 booking-button-icon"></i>
                                                        <span class="booking-button-label">{{ __('home.featured_workshop.status_booked') }}</span>
                                                    </button>
                                                    @elseif($isFull)
                                                    <button type="button" class="flex-1 bg-gray-400 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                                        <i class="fas fa-lock mr-2 rtl:ml-2"></i>
                                                        {{ __('home.featured_workshop.status_full') }}
                                                    </button>
                                                    @elseif($isRegistrationClosed)
                                                        <button type="button" class="flex-1 bg-yellow-400 text-yellow-800 font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                                            <i class="fas fa-clock mr-2 rtl:ml-2"></i>
                                                            {{ __('home.featured_workshop.status_closed') }}
                                                        </button>
                                                    @else
                                                        <a href="{{ route('workshop.show', $workshop->slug) }}#workshop-booking"
                                                           class="flex-1 text-center font-bold py-3 px-4 rounded-full transition-colors flex items-center justify-center text-sm {{ $bookingButtonStateClasses }}">
                                                            <i class="fas fa-calendar-check mr-2 rtl:ml-2 booking-button-icon"></i>
                                                            <span class="booking-button-label">{{ __('home.premium_workshops.card_primary_cta') }}</span>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('workshop.show', $workshop->slug) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-full transition-colors flex items-center justify-center group">
                                                        <i class="fas fa-info-circle text-sm mr-2 rtl:ml-2 group-hover:text-orange-500 transition-colors"></i>
                                                        <span class="text-sm">{{ __('home.buttons.view_details') }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-20 bg-white rounded-2xl shadow-lg">
                    <div class="w-24 h-24 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-coffee text-4xl text-amber-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 mb-3">{{ __('home.premium_workshops.empty_title') }}</h3>
                    <p class="text-gray-500 text-lg max-w-md mx-auto mb-6">{{ __('home.premium_workshops.empty_description') }}</p>
                </div>
            @endif
        </div>
    </section>


@endsection

@push('scripts')
<script>
(() => {
const homeTranslations = {
    save: @json(__('home.saved.save')),
    saved: @json(__('home.saved.saved')),
    alerts: {
        removed: @json(__('home.alerts.save_removed')),
        success: @json(__('home.alerts.save_success')),
        error: @json(__('home.alerts.save_error')),
    },
};

const initHomePageInteractions = () => {
    const isRtl = document.documentElement.dir === 'rtl';

    // Initialize hero swiper
    const heroSliderEl = document.querySelector('.hero-slider');
    if (heroSliderEl) {
        const heroInitialSlide = window.innerWidth <= 640 ? 1 : 0;
        new Swiper(heroSliderEl, {
            loop: true,
            speed: 700,
            initialSlide: heroInitialSlide,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            grabCursor: true,
        });
    }

    // Toggle featured description expand/collapse
    const featuredDesc = document.getElementById('featured-desc');
    const featuredToggle = document.querySelector('[data-toggle="featured-desc"]');
    if (featuredDesc && featuredToggle) {
        const previewText = featuredDesc.dataset.previewText || '';
        const fullText = featuredDesc.dataset.fullText || '';
        let expanded = false;

        featuredToggle.addEventListener('click', () => {
            expanded = !expanded;
            featuredDesc.textContent = expanded ? fullText : previewText;
            featuredDesc.classList.toggle('line-clamp-3', !expanded);
            const label = featuredToggle.querySelector('.toggle-label');
            const icon = featuredToggle.querySelector('i');
            if (label) {
                label.textContent = expanded ? 'إظهار أقل' : 'عرض المزيد';
            }
            if (icon) {
                icon.classList.toggle('fa-chevron-down', !expanded);
                icon.classList.toggle('fa-chevron-up', expanded);
            }
        });
    }

    // Initialize Swiper for feed videos
    const feedVideosSwiperEl = document.querySelector('.feed-videos-swiper');
    if (feedVideosSwiperEl) {
        const feedSlides = feedVideosSwiperEl.querySelectorAll('.swiper-slide').length;
        const feedNav = document.querySelector('.feed-videos-nav');
        const feedPagination = document.querySelector('.feed-videos-pagination');
        const showControls = feedSlides > 1;

        if (feedNav) {
            feedNav.hidden = !showControls;
            feedNav.setAttribute('aria-hidden', showControls ? 'false' : 'true');
            feedNav.querySelectorAll('button').forEach((button) => {
                button.hidden = !showControls;
                button.disabled = !showControls;
                button.tabIndex = showControls ? 0 : -1;
                button.setAttribute('aria-hidden', showControls ? 'false' : 'true');
            });
        }

        if (feedPagination) {
            feedPagination.hidden = !showControls;
            feedPagination.setAttribute('aria-hidden', showControls ? 'false' : 'true');
        }

        const feedSwiper = new Swiper(feedVideosSwiperEl, {
            effect: 'slide',
            slidesPerView: 'auto',
            spaceBetween: 20,
            grabCursor: true,
            centeredSlides: false,
            watchOverflow: true,
            allowTouchMove: true,
            simulateTouch: true,
            touchEventsTarget: 'container',
            touchRatio: 1.1,
            touchStartPreventDefault: true,
            passiveListeners: false,
            preventClicks: true,
            preventClicksPropagation: true,
            resistanceRatio: 0.65,
            threshold: 3,
            loop: false,
            freeMode: {
                enabled: true,
                momentum: true,
                momentumVelocityRatio: 0.65,
                momentumBounce: false,
            },
            autoplay: false,
            speed: 750,
            navigation: {
                nextEl: '.feed-videos-next',
                prevEl: '.feed-videos-prev',
            },
            pagination: feedPagination ? {
                el: feedPagination,
                clickable: true,
                bulletClass: 'feed-videos-bullet',
                bulletActiveClass: 'feed-videos-bullet-active',
            } : undefined,
            on: {
                touchMove() {
                    this.allowClick = false;
                },
                touchEnd() {
                    this.allowClick = true;
                },
            },
        });
    }

    // Initialize Swiper for tools slider
    const toolsSwiperEl = document.querySelector('.home-tools-swiper');
    if (toolsSwiperEl) {
        new Swiper(toolsSwiperEl, {
            slidesPerView: 1.1,
            spaceBetween: 16,
            grabCursor: true,
            watchOverflow: true,
            navigation: {
                nextEl: '.home-tools-next',
                prevEl: '.home-tools-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 1.5,
                    spaceBetween: 18,
                },
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
                1280: {
                    slidesPerView: 3.5,
                    spaceBetween: 28,
                },
                1536: {
                    slidesPerView: 4,
                    spaceBetween: 32,
                },
            },
        });
    }

    // Enable flip interaction on recipe cards
    const recipeCards = document.querySelectorAll('.card-container');
    if (recipeCards.length) {
        recipeCards.forEach((card) => {
            card.addEventListener('click', (event) => {
                // Avoid flipping when clicking actionable elements
                if (event.target.closest('.save-recipe-btn') || event.target.closest('a')) {
                    return;
                }

                recipeCards.forEach((otherCard) => {
                    if (otherCard !== card) {
                        otherCard.classList.remove('is-flipped');
                    }
                });

                card.classList.toggle('is-flipped');
            });
        });

        // Close card when clicking outside
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.card-container')) {
                recipeCards.forEach((card) => card.classList.remove('is-flipped'));
            }
        });
    }

    // Initialize save buttons using the global function from save-recipe.js
    if (typeof window.SaveRecipe !== 'undefined' && window.SaveRecipe.initializeSaveButtons) {
        window.SaveRecipe.initializeSaveButtons();
    } else {
        // Fallback if save-recipe.js is not loaded
        console.warn('save-recipe.js not loaded, using fallback initialization');
        initializeSaveButtonsFallback();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomePageInteractions, { once: true });
} else {
    initHomePageInteractions();
}

function initializeSaveButtonsFallback() {
    const saveButtons = document.querySelectorAll('.save-recipe-btn');
    
    saveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent card flip
            e.stopImmediatePropagation(); // Prevent other event handlers
            
            const recipeId = this.dataset.recipeId;
            const recipeName = this.dataset.recipeName;
            const isCurrentlySaved = this.dataset.saved === 'true';
            
            // Toggle visual state immediately
            if (isCurrentlySaved) {
                // Change from saved (green) to not saved (orange)
                this.classList.remove('bg-green-500', 'hover:bg-green-600');
                this.classList.add('bg-orange-500', 'hover:bg-orange-600');
                this.querySelector('span').textContent = homeTranslations.save;
                this.dataset.saved = 'false';
            } else {
                // Change from not saved (orange) to saved (green)
                this.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                this.classList.add('bg-green-500', 'hover:bg-green-600');
                this.querySelector('span').textContent = homeTranslations.saved;
                this.dataset.saved = 'true';
            }
            
            // Make API call
            fetch('/api/interactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'include',
                body: JSON.stringify({
                    recipe_id: recipeId,
                    is_saved: !isCurrentlySaved
                })
            })
            .then(response => {
                if (response.status === 401) {
                    // User not logged in, redirect to login
                    window.location.href = '/login';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show notification
                    showNotification(
                        isCurrentlySaved ? homeTranslations.alerts.removed : homeTranslations.alerts.success,
                        'success'
                    );
                    
                    // تحديث عداد الحفظ في صفحة الوصفة فورياً (فقط للمستخدمين المسجلين)
                    if (typeof window.SaveRecipe !== 'undefined' && window.SaveRecipe.updateRecipePageSaveCount) {
                        window.SaveRecipe.updateRecipePageSaveCount(!isCurrentlySaved);
                    }
                } else {
                    // Revert visual state on error
                    if (isCurrentlySaved) {
                        // Revert back to saved (green)
                        this.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                        this.classList.add('bg-green-500', 'hover:bg-green-600');
                        this.querySelector('span').textContent = homeTranslations.saved;
                        this.dataset.saved = 'true';
                    } else {
                        // Revert back to not saved (orange)
                        this.classList.remove('bg-green-500', 'hover:bg-green-600');
                        this.classList.add('bg-orange-500', 'hover:bg-orange-600');
                        this.querySelector('span').textContent = homeTranslations.save;
                        this.dataset.saved = 'false';
                    }
                    
                    showNotification(homeTranslations.alerts.error, 'error');
                }
            })
            .catch(error => {
                // Revert visual state on error
                if (isCurrentlySaved) {
                    // Revert back to saved (green)
                    this.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                    this.classList.add('bg-green-500', 'hover:bg-green-600');
                    this.querySelector('span').textContent = homeTranslations.saved;
                    this.dataset.saved = 'true';
                } else {
                    // Revert back to not saved (orange)
                    this.classList.remove('bg-green-500', 'hover:bg-green-600');
                    this.classList.add('bg-orange-500', 'hover:bg-orange-600');
                    this.querySelector('span').textContent = homeTranslations.save;
                    this.dataset.saved = 'false';
                }
                
                showNotification(homeTranslations.alerts.error, 'error');
            });
        });
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// دالة عرض التنبيه المخصص
function showCustomAlert(message, type = 'info') {
    // إزالة أي تنبيهات سابقة
    const existingAlert = document.getElementById('custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    // تحديد الألوان والأيقونات حسب النوع
    let bgColor, textColor, icon, borderColor;
    switch(type) {
        case 'success':
            bgColor = 'bg-green-50';
            textColor = 'text-green-800';
            icon = 'fas fa-check-circle';
            borderColor = 'border-green-200';
            break;
        case 'error':
            bgColor = 'bg-red-50';
            textColor = 'text-red-800';
            icon = 'fas fa-exclamation-circle';
            borderColor = 'border-red-200';
            break;
        case 'warning':
            bgColor = 'bg-yellow-50';
            textColor = 'text-yellow-800';
            icon = 'fas fa-exclamation-triangle';
            borderColor = 'border-yellow-200';
            break;
        default:
            bgColor = 'bg-blue-50';
            textColor = 'text-blue-800';
            icon = 'fas fa-info-circle';
            borderColor = 'border-blue-200';
    }

    // إنشاء التنبيه
    const alertHTML = `
        <div id="custom-alert" class="fixed top-4 right-4 z-50 max-w-sm w-full mx-4 transform transition-all duration-300 ease-in-out">
            <div class="${bgColor} ${borderColor} border-l-4 rounded-lg shadow-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${icon} ${textColor} text-xl"></i>
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="${textColor} text-sm font-medium leading-5">
                            ${message}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <button onclick="closeCustomAlert()" class="${textColor} hover:opacity-75 focus:outline-none">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // إضافة التنبيه للصفحة
    document.body.insertAdjacentHTML('beforeend', alertHTML);

    // إظهار التنبيه مع تأثير
    setTimeout(() => {
        const alert = document.getElementById('custom-alert');
        if (alert) {
            alert.style.transform = 'translateX(0)';
            alert.style.opacity = '1';
        }
    }, 100);

    // إزالة التنبيه تلقائياً بعد 5 ثوان
    setTimeout(() => {
        closeCustomAlert();
    }, 5000);
}

// دالة إغلاق التنبيه
function closeCustomAlert() {
    const alert = document.getElementById('custom-alert');
    if (alert) {
        alert.style.transform = 'translateX(100%)';
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

window.showCustomAlert = showCustomAlert;
window.closeCustomAlert = closeCustomAlert;
})();
</script>
@endpush



