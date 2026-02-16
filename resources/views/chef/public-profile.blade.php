@extends('layouts.app')

@section('title', __('chef.meta.title', ['name' => $chef->name ?? '']))

@php
    $locale = app()->getLocale();
    $carbonLocale = $locale === 'ar' ? 'ar' : 'en';

    $totalFollowers = max(0, (int) ($chef->instagram_followers ?? 0)) + max(0, (int) ($chef->youtube_followers ?? 0));
    $bio = $chef->chef_specialty_description ?: __('chef.defaults.bio');
    $specialty = $chef->chef_specialty_area
        ? __('chef.defaults.specialty_with_area', ['area' => $chef->chef_specialty_area])
        : __('chef.defaults.specialty_generic');

    $ratedWorkshopsCount = max(0, (int) data_get($stats, 'rated_workshops_count', 0));

    $statsAverage = $ratedWorkshopsCount > 0 && $stats['average_rating']
        ? number_format((float) $stats['average_rating'], 1)
        : 'â€”';

    $wasfahFollowers = max(
        0,
        (int) data_get(
            $chef,
            'followers_count',
            data_get(
                $chef,
                'wasfah_followers',
                data_get($chef, 'subscribers_count', data_get($chef, 'wasfah_subscribers', 0))
            )
        )
    );

    if ($wasfahFollowers === 0) {
        $maybeFollowersRelation = data_get($chef, 'followers');

        if (is_countable($maybeFollowersRelation)) {
            $wasfahFollowers = count($maybeFollowersRelation);
        }
    }

    $platformFollowers = $totalFollowers;
    $workshopPlaceholderText = rawurlencode(__('chef.workshops.placeholder_text'));
    $workshopDateTimeFormat = __('chef.workshops.datetime_format');
    $workshopDateFormat = __('chef.workshops.date_format');
    $isRtl = $locale === 'ar';
    $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
    $arrowLongIcon = $isRtl ? 'fa-arrow-left-long' : 'fa-arrow-right-long';
    $publicWorkshopsIdentifier = $chef->username
        ?? $chef->slug
        ?? $chef->handle
        ?? $chef->referral_code
        ?? $chef->id;
    $publicWorkshopsUrl = route('chef.public.workshops', ['username' => $publicWorkshopsIdentifier]);
    $hasRecordedWorkshops = $recordedWorkshops instanceof \Illuminate\Support\Collection
        ? $recordedWorkshops->isNotEmpty()
        : false;
    $smartDatePlaceholder = 'mm/dd/yyyy --:-- --';
    $registrationAutoNote = $locale === 'ar'
        ? 'ÙŠØºÙ„Ù‚ Ø§Ù„ØªØ³Ø¬ÙŠÙ„ ØªÙ„Ù‚Ø§Ø¦ÙŠØ§Ù‹ Ù‚Ø¨Ù„ Ø¨Ø¯Ø¡ Ø§Ù„ÙˆØ±Ø´Ø© Ø¨Ø¯Ù‚ÙŠÙ‚ØªÙŠÙ† ÙˆÙÙ‚ ØªÙˆÙ‚ÙŠØª Ø§Ù„Ù…Ø¶ÙŠÙ.'
        : 'Registration closes automatically 2 minutes before the workshop starts, based on the host timezone.';
    $smartSuggestionCta = $locale === 'ar' ? 'Ø§Ù‚ØªØ±Ø§Ø­ ÙˆÙ‚Øª Ø°ÙƒÙŠ' : 'Smart time suggestion';
    $smartSuggestionInfo = $locale === 'ar'
        ? 'Ø³Ù†Ù‚ØªØ±Ø­ ÙˆÙ‚ØªØ§Ù‹ Ù…Ù†Ø§Ø³Ø¨Ø§Ù‹ Ø¨Ø§Ù„Ø§Ø¹ØªÙ…Ø§Ø¯ Ø¹Ù„Ù‰ ØªÙˆÙ‚ÙŠØªÙƒ Ø§Ù„Ø­Ø§Ù„ÙŠ ÙˆØªÙˆÙ‚ÙŠØª Ø§Ù„Ù…Ø¶ÙŠÙ.'
        : 'We will suggest a suitable slot based on your timezone and the host timezone.';
    $startLabelText = $locale === 'ar' ? 'ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¨Ø¯Ø§ÙŠØ© *' : 'Start date *';
    $endLabelText = $locale === 'ar' ? 'ØªØ§Ø±ÙŠØ® Ø§Ù„Ù†Ù‡Ø§ÙŠØ© *' : 'End date *';
    $deadlineLabelText = $locale === 'ar' ? 'Ù…ÙˆØ¹Ø¯ Ø¥ØºÙ„Ø§Ù‚ Ø§Ù„ØªØ³Ø¬ÙŠÙ„' : 'Registration deadline';
    $locationLabelText = $locale === 'ar' ? 'Ø§Ù„Ù…ÙˆÙ‚Ø¹' : 'Location';
@endphp

@push('styles')
    <style>
        .chef-page {
            background: #f8fafc;
        }

        .chef-card {
            border: 1px solid #e5e7eb;
            box-shadow: 0 6px 20px -12px rgba(15, 23, 42, 0.25);
        }

        .chef-follow-btn {
            background: #f97316;
            color: #fff;
            border: 1px solid #f97316;
            padding: 0.75rem 1.5rem;
            border-radius: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .chef-follow-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -15px rgba(249, 115, 22, 0.8);
        }

        .chef-follow-btn.is-following {
            background: #fff;
            color: #f97316;
            border-color: #f97316;
        }

        .chef-tab-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .chef-tab-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .chef-tab-btn.is-active {
            background: #f97316;
            border-color: #f97316;
            color: #fff;
            box-shadow: 0 8px 18px -12px rgba(249, 115, 22, 0.9);
        }

        .chef-tab-panel {
            display: none;
        }

        .chef-tab-panel.is-active {
            display: block;
        }

        .chef-empty-state {
            border: 1px dashed #e2e8f0;
            border-radius: 16px;
            background: #f8fafc;
            color: #475569;
            padding: 1.5rem;
            text-align: center;
        }

        .chef-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
            padding: 0.35rem 0.7rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .chef-media-frame {
            aspect-ratio: 16 / 9;
            background: #0f172a;
            border-radius: 16px;
            overflow: hidden;
        }

        .chef-media-frame iframe,
        .chef-media-frame video {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="chef-page py-10">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            <section class="chef-card rounded-3xl bg-white p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center">
                    <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border border-orange-100 bg-orange-50 shadow-sm md:h-32 md:w-32">
                        <img src="{{ $avatarUrl }}" alt="{{ __('chef.hero.avatar_alt', ['name' => $chef->name]) }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <div class="flex-1 space-y-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-orange-600">{{ $specialty }}</p>
                                <h1 class="text-3xl font-bold text-slate-900">{{ __('chef.hero.heading', ['name' => $chef->name]) }}</h1>
                            </div>
                            <div class="flex flex-col items-start gap-2 md:items-end">
                                @if (! $isOwner)
                                    @auth
                                        <button
                                            type="button"
                                            class="chef-follow-btn{{ $isFollowing ? ' is-following' : '' }}"
                                            data-follow-button
                                            data-follow-url="{{ $followRoutes['follow'] }}"
                                            data-unfollow-url="{{ $followRoutes['unfollow'] }}"
                                        >
                                            <i class="fa-solid {{ $isFollowing ? 'fa-check' : 'fa-plus' }}" data-follow-icon></i>
                                            <span data-follow-label>
                                                {{ $isFollowing ? __('chef.hero.buttons.following') : __('chef.hero.buttons.follow') }}
                                            </span>
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="chef-follow-btn">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>{{ __('chef.hero.buttons.follow') }}</span>
                                    </a>
                                @endauth
                                @endif
                                @auth
                                    @if(auth()->user()?->canAccessRecordManagement())
                                        <a
                                            href="{{ route('bookings.index') }}"
                                            class="inline-flex items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-100"
                                        >
                                            <i class="fa-solid fa-calendar-check"></i>
                                            <span>{{ __('navbar.account_menu.links.bookings') }}</span>
                                        </a>
                                    @endif
                                @endauth
                                <a href="{{ $publicWorkshopsUrl }}" class="inline-flex items-center gap-2 rounded-2xl border border-orange-100 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-200 hover:bg-orange-100">
                                    <i class="fa-solid fa-circle-play"></i>
                                    <span>{{ __('chef.recordings.button') }}</span>
                                </a>
                            </div>
                        </div>
                        <p class="leading-relaxed text-slate-600">{{ $bio }}</p>
                        @if ($socialLinks->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($socialLinks as $link)
                                    <a
                                        href="{{ $link['url'] }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-orange-100 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-200 hover:bg-orange-100"
                                    >
                                        <i class="{{ $link['icon'] }}"></i>
                                        <span>{{ $link['label'] }}</span>
                                        @if (! empty($link['followers']))
                                            <span class="text-xs text-orange-500">({{ number_format($link['followers']) }})</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                        <p class="text-xs text-slate-500">{{ __('chef.hero.stats.wasfah_followers') }}</p>
                        <p
                            class="text-xl font-bold text-slate-900"
                            data-followers-count
                            data-followers-value="{{ $wasfahFollowers }}"
                        >{{ number_format($wasfahFollowers) }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                        <p class="text-xs text-slate-500">{{ __('chef.hero.stats.other_platform_followers') }}</p>
                        <p class="text-xl font-bold text-slate-900">{{ number_format($platformFollowers) }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                        <p class="text-xs text-slate-500">{{ __('chef.hero.stats.average_rating') }}</p>
                        <p class="text-xl font-bold text-slate-900">{{ $statsAverage }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2 text-sm font-semibold text-slate-600">
                    <span class="chef-chip">
                        <i class="fa-solid fa-bookmark text-orange-500"></i>
                        {{ __('chef.recipes.saves', ['count' => number_format((int) $stats['total_saves'])]) }}
                    </span>
                    @if ($ratedWorkshopsCount > 0 && $statsAverage !== 'â€”')
                        <span class="chef-chip">
                            <i class="fa-solid fa-star text-amber-400"></i>
                            {{ __('chef.recipes.rated_workshops_average', [
                                'value' => $statsAverage,
                                'count' => number_format($ratedWorkshopsCount),
                            ]) }}
                        </span>
                    @endif
                </div>
            </section>

            @php
                $driveBadgeLabel = __('chef.recordings.badges.drive');
                $visibleRecordingEntries = ($recordingEntries instanceof \Illuminate\Support\Collection ? $recordingEntries : collect($recordingEntries ?? []))
                    ->filter(function ($entry) use ($driveBadgeLabel) {
                        $hasRecording = ! empty($entry['preview_url']) || ! empty($entry['watch_url']) || ! empty($entry['is_direct_video']);
                        $badgeLabel = is_string($entry['badge'] ?? null) ? trim($entry['badge']) : '';
                        $isExternalDrive = $badgeLabel === $driveBadgeLabel;

                        return $hasRecording && ! $isExternalDrive;
                    });
            @endphp

            @if ($visibleRecordingEntries->isNotEmpty())
                <section class="chef-card rounded-3xl bg-white p-6 md:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">
                                {{ __('chef.recordings.eyebrow') }}
                            </p>
                            <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.recordings.title') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('chef.recordings.description', ['name' => $chef->name]) }}</p>
                        </div>
                        <a href="{{ $publicWorkshopsUrl }}" class="inline-flex items-center gap-2 rounded-2xl border border-orange-100 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-200 hover:bg-orange-100">
                            {{ __('chef.recordings.view_all') }}
                            <i class="fa-solid {{ $arrowLongIcon }}"></i>
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($visibleRecordingEntries as $entry)
                            @php
                                $previewUrl = $entry['preview_url'] ?? null;
                                $watchUrl = $entry['watch_url'] ?? null;
                                $isDirectVideo = $entry['is_direct_video'] ?? false;
                                $badgeLabel = $entry['badge'] ?? __('chef.recordings.badges.available');
                                $poster = $entry['poster'] ?? \App\Support\BrandAssets::logoAsset('webp');
                            @endphp
                            <article class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="chef-media-frame">
                                    @if ($previewUrl)
                                        <iframe
                                            src="{{ $previewUrl }}"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            title="{{ $entry['title'] }}"
                                        ></iframe>
                                    @elseif ($isDirectVideo)
                                        <video controls preload="metadata" playsinline poster="{{ $poster }}">
                                            <source src="{{ $watchUrl }}">
                                        </video>
                                    @else
                                        <div class="flex h-full w-full flex-col items-center justify-center gap-2 bg-slate-900 text-slate-200">
                                            <i class="fa-solid fa-cloud-arrow-down text-xl"></i>
                                            <span>{{ __('chef.recordings.fallback_drive') }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
                                    <span class="chef-chip">
                                        <i class="fa-solid fa-circle-play text-orange-500"></i>
                                        {{ $badgeLabel }}
                                    </span>
                                    <span class="chef-chip">
                                        <i class="fa-solid fa-calendar-day text-amber-500"></i>
                                        {{ $entry['date_label'] ?? '' }}
                                    </span>
                                    <span class="chef-chip">
                                        <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                        {{ $entry['location_label'] ?? '' }}
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $entry['title'] }}</h3>
                                    @if (! empty($entry['excerpt']))
                                        <p class="text-sm leading-relaxed text-slate-600">{{ $entry['excerpt'] }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @if ($watchUrl)
                                        <a
                                            href="{{ $watchUrl }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600"
                                        >
                                            <i class="fa-solid fa-play"></i>
                                            {{ __('chef.recordings.cta.watch') }}
                                        </a>
                                    @endif
                                    @if (! empty($entry['details_url']))
                                        <a
                                            href="{{ $entry['details_url'] }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-gray-300"
                                        >
                                            {{ __('chef.workshops.view_details') }}
                                            <i class="fa-solid {{ $arrowIcon }}"></i>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif


            @if ($upcomingWorkshops->isNotEmpty() || $pastWorkshops->isNotEmpty())
                <section class="chef-card rounded-3xl bg-white p-6 md:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.workshops.title') }}</h2>
                            <p class="text-sm text-slate-500">
                                {{ __('chef.workshops.description', ['name' => $chef->name]) }}
                            </p>
                        </div>
                        <a href="{{ $publicWorkshopsUrl }}" class="inline-flex items-center gap-2 rounded-2xl border border-orange-100 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-200 hover:bg-orange-100">
                            {{ __('chef.workshops.view_all') }}
                            <i class="fa-solid {{ $arrowLongIcon }}"></i>
                        </a>
                    </div>

                    @if ($upcomingWorkshops->isNotEmpty())
                        <div class="mt-6 space-y-3">
                            <h3 class="text-lg font-semibold text-slate-800">
                                <i class="fa-solid fa-calendar-check text-emerald-500"></i>
                                {{ __('chef.workshops.upcoming') }}
                            </h3>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($upcomingWorkshops as $workshop)
                                    @php
                                        $coverImage = $workshop->image
                                            ? asset('storage/' . ltrim($workshop->image, '/'))
                                            : "https://placehold.co/600x400/0f4c73/FFFFFF?text={$workshopPlaceholderText}";
                                        $startDateLabel = $workshop->start_date
                                            ? $workshop->start_date->copy()->locale($carbonLocale)->translatedFormat($workshopDateTimeFormat)
                                            : $smartDatePlaceholder;
                                        $endDateLabel = $workshop->end_date
                                            ? $workshop->end_date->copy()->locale($carbonLocale)->translatedFormat($workshopDateTimeFormat)
                                            : $smartDatePlaceholder;
                                        $locationLabel = $workshop->is_online
                                            ? __('chef.workshops.online_live')
                                            : ($workshop->location ?: __('chef.workshops.location_tbd'));
                                        $priceLabel = $workshop->formatted_price
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? config('finance.default_currency', 'JOD')));
                                        $currentBookings = number_format((int) ($workshop->bookings_count ?? 0));
                                        $maxParticipants = $workshop->max_participants ? number_format((int) $workshop->max_participants) : null;
                                        $capacityLabel = $workshop->max_participants
                                            ? __('chef.workshops.capacity_with_limit', ['current' => $currentBookings, 'max' => $maxParticipants])
                                            : __('chef.workshops.capacity_open', ['count' => $currentBookings]);
                                        $deadlineLabel = $workshop->registration_deadline
                                            ? $workshop->registration_deadline->copy()->locale($carbonLocale)->translatedFormat($workshopDateFormat)
                                            : null;
                                        $hostTimezoneLabel = $workshop->host_timezone ?: config('app.timezone', 'UTC');
                                        $isRegistrationOpen = (bool) $workshop->is_registration_open;
                                    @endphp
                                    <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                        <div class="h-44 w-full overflow-hidden bg-gray-100">
                                            <img src="{{ $coverImage }}" alt="{{ __('chef.workshops.image_alt', ['title' => $workshop->title]) }}" class="h-full w-full object-cover" loading="lazy">
                                        </div>
                                        <div class="flex flex-1 flex-col gap-3 p-4">
                                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                                <span class="chef-chip">
                                                    <i class="fa-solid {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                    {{ $workshop->is_online ? __('chef.workshops.delivery.online_short') : __('chef.workshops.delivery.in_person_short') }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-users"></i>
                                                    {{ $capacityLabel }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-money-bill-wave text-orange-500"></i>
                                                    {{ $priceLabel }}
                                                </span>
                                            </div>
                                            <div class="space-y-1">
                                                <h3 class="text-lg font-bold text-slate-900">{{ $workshop->title }}</h3>
                                                @if (! empty($workshop->instructor))
                                                    <p class="text-sm text-slate-500">{{ __('chef.workshops.with_instructor', ['name' => $workshop->instructor]) }}</p>
                                                @endif
                                            </div>
                                            <ul class="space-y-2 text-sm text-slate-600">
                                                <li class="flex items-start gap-3">
                                                    <span class="mt-0.5 rounded-full bg-amber-50 p-2 text-amber-600">
                                                        <i class="fa-solid fa-calendar-day"></i>
                                                    </span>
                                                    <div>
                                                        <p class="text-xs font-semibold text-slate-500">{{ $startLabelText }}</p>
                                                        <p class="font-semibold text-slate-900">{{ $startDateLabel }}</p>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="mt-0.5 rounded-full bg-indigo-50 p-2 text-indigo-600">
                                                        <i class="fa-solid fa-flag-checkered"></i>
                                                    </span>
                                                    <div>
                                                        <p class="text-xs font-semibold text-slate-500">{{ $endLabelText }}</p>
                                                        <p class="font-semibold text-slate-900">{{ $endDateLabel }}</p>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="mt-0.5 rounded-full bg-emerald-50 p-2 text-emerald-600">
                                                        <i class="fa-solid fa-location-dot"></i>
                                                    </span>
                                                    <div>
                                                        <p class="text-xs font-semibold text-slate-500">{{ $locationLabelText }}</p>
                                                        <p>{{ $locationLabel }}</p>
                                                    </div>
                                                </li>
                                                <li class="flex items-start gap-3">
                                                    <span class="mt-0.5 rounded-full bg-slate-100 p-2 text-slate-600">
                                                        <i class="fa-solid fa-hourglass-half"></i>
                                                    </span>
                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-slate-500">{{ $deadlineLabelText }}</p>
                                                        <p class="font-semibold text-slate-900">
                                                            {{ $deadlineLabel ? __('chef.workshops.register_until', ['date' => $deadlineLabel]) : ($locale === 'ar' ? 'ÙŠØºÙ„Ù‚ Ù‚Ø¨Ù„ Ø¨Ø¯Ø¡ Ø§Ù„ÙˆØ±Ø´Ø©' : 'Closes before the workshop starts') }}
                                                        </p>
                                                        <p class="text-xs leading-relaxed text-slate-500">
                                                            {{ $registrationAutoNote }} ({{ $hostTimezoneLabel }})
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="mt-auto space-y-2 pt-2">
                                                <div class="space-y-1">
                                                    <button
                                                        type="button"
                                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-300 hover:bg-orange-50"
                                                        data-smart-suggest
                                                        data-smart-start="{{ optional($workshop->start_date)->toIso8601String() }}"
                                                        data-smart-end="{{ optional($workshop->end_date)->toIso8601String() }}"
                                                        data-smart-timezone="{{ $hostTimezoneLabel }}"
                                                        data-smart-note="{{ $registrationAutoNote }}"
                                                    >
                                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                        <span>{{ $smartSuggestionCta }}</span>
                                                    </button>
                                                    <p class="text-xs text-slate-500">{{ $smartSuggestionInfo }}</p>
                                                </div>
                                                @if ($isRegistrationOpen)
                                                    <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                                        {{ __('chef.workshops.book_now') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                @else
                                                    <span class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm font-semibold text-slate-500">
                                                        {{ __('chef.workshops.registration_closed') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($pastWorkshops->isNotEmpty())
                        <div class="mt-8 space-y-3">
                            <h3 class="text-lg font-semibold text-slate-800">
                                <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                                {{ __('chef.workshops.past') }}
                            </h3>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($pastWorkshops as $workshop)
                                    @php
                                        $coverImage = $workshop->image
                                            ? asset('storage/' . ltrim($workshop->image, '/'))
                                            : "https://placehold.co/600x400/0f4c73/FFFFFF?text={$workshopPlaceholderText}";
                                        $startDateLabel = $workshop->start_date
                                            ? $workshop->start_date->copy()->locale($carbonLocale)->translatedFormat($workshopDateTimeFormat)
                                            : __('chef.workshops.unscheduled_time');
                                        $locationLabel = $workshop->is_online
                                            ? __('chef.workshops.online_live')
                                            : ($workshop->location ?: __('chef.workshops.location_tbd'));
                                        $priceLabel = $workshop->formatted_price
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? config('finance.default_currency', 'JOD')));
                                    @endphp
                                    <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 shadow-sm">
                                        <div class="h-40 w-full overflow-hidden bg-gray-200">
                                            <img src="{{ $coverImage }}" alt="{{ __('chef.workshops.image_alt', ['title' => $workshop->title]) }}" class="h-full w-full object-cover" loading="lazy">
                                        </div>
                                        <div class="flex flex-1 flex-col gap-3 p-4">
                                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                                <span class="chef-chip">
                                                    <i class="fa-solid {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                    {{ $workshop->is_online ? __('chef.workshops.delivery.online_short') : __('chef.workshops.delivery.in_person_short') }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-money-bill-wave text-slate-500"></i>
                                                    {{ $priceLabel }}
                                                </span>
                                            </div>
                                            <div class="space-y-1">
                                                <h3 class="text-lg font-bold text-slate-900">{{ $workshop->title }}</h3>
                                                @if (! empty($workshop->instructor))
                                                    <p class="text-sm text-slate-500">{{ __('chef.workshops.delivered_by', ['name' => $workshop->instructor]) }}</p>
                                                @endif
                                            </div>
                                            <ul class="space-y-1 text-sm text-slate-600">
                                                <li class="flex items-center gap-2">
                                                    <i class="fa-solid fa-calendar-day text-slate-500"></i>
                                                    {{ $startDateLabel }}
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <i class="fa-solid fa-location-dot text-slate-500"></i>
                                                    {{ $locationLabel }}
                                                </li>
                                            </ul>
                                            <div class="mt-auto pt-2">
                                                <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-gray-300">
                                                    {{ __('chef.workshops.view_details') }}
                                                    <i class="fa-solid {{ $arrowIcon }}"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $chefTranslations = [
            'buttons' => [
                'follow' => __('chef.hero.buttons.follow'),
                'following' => __('chef.hero.buttons.following'),
            ],
            'follow' => [
                'error' => __('chef.follow.errors.generic'),
            ],
        ];
    @endphp
    <script>
        const chefTranslations = @json($chefTranslations);

        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.chef-tab-btn');
            const tabPanels = document.querySelectorAll('.chef-tab-panel');

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-tab');

                    tabButtons.forEach((btn) => {
                        const isActive = btn === button;
                        btn.classList.toggle('is-active', isActive);
                        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    tabPanels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
                    });
                });
            });

            const followButton = document.querySelector('[data-follow-button]');
            if (followButton) {
                const followLabel = followButton.querySelector('[data-follow-label]');
                const followIcon = followButton.querySelector('[data-follow-icon]');
                const followersCounter = document.querySelector('[data-followers-count]');
                const numberFormatter = new Intl.NumberFormat(document.documentElement.lang || 'ar');
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
                const states = {
                    follow: {
                        icon: 'fa-plus',
                        label: chefTranslations.buttons.follow,
                    },
                    following: {
                        icon: 'fa-check',
                        label: chefTranslations.buttons.following,
                    },
                };

                const updateFollowState = (isFollowing) => {
                    followButton.classList.toggle('is-following', isFollowing);
                    const state = isFollowing ? states.following : states.follow;

                    if (followLabel) {
                        followLabel.textContent = state.label;
                    }

                    if (followIcon) {
                        followIcon.classList.remove('fa-plus', 'fa-check');
                        followIcon.classList.add(state.icon);
                    }
                };

                const updateFollowersCount = (value) => {
                    if (!followersCounter) {
                        return;
                    }

                    followersCounter.dataset.followersValue = value;
                    followersCounter.textContent = numberFormatter.format(value);
                };

                const initialFollowersValue = followersCounter
                    ? Number(followersCounter.dataset.followersValue || followersCounter.textContent.replace(/[^0-9.]/g, ''))
                    : NaN;

                if (!Number.isNaN(initialFollowersValue)) {
                    updateFollowersCount(initialFollowersValue);
                }

                followButton.addEventListener('click', async () => {
                    const followUrl = followButton.dataset.followUrl;
                    const unfollowUrl = followButton.dataset.unfollowUrl;

                    if (!followUrl || !unfollowUrl) {
                        return;
                    }

                    if (followButton.dataset.loading === 'true') {
                        return;
                    }

                    const isFollowing = followButton.classList.contains('is-following');
                    const targetUrl = isFollowing ? unfollowUrl : followUrl;
                    const method = isFollowing ? 'DELETE' : 'POST';

                    followButton.dataset.loading = 'true';
                    followButton.disabled = true;

                    try {
                        const response = await fetch(targetUrl, {
                            method,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                ...(method === 'POST' ? { 'Content-Type': 'application/json' } : {}),
                            },
                            body: method === 'POST' ? '{}' : undefined,
                        });

                        let payload = {};

                        try {
                            payload = await response.json();
                        } catch (error) {
                            payload = {};
                        }

                        if (!response.ok) {
                            throw new Error(payload.message || chefTranslations.follow.error);
                        }

                        updateFollowState(Boolean(payload.is_following));

                        if (typeof payload.followers_count === 'number') {
                            updateFollowersCount(payload.followers_count);
                        }
                    } catch (error) {
                        console.error('Follow toggle failed:', error);
                        alert(error.message || chefTranslations.follow.error);
                    } finally {
                        followButton.dataset.loading = 'false';
                        followButton.disabled = false;
                    }
                });

                updateFollowState(followButton.classList.contains('is-following'));
            }

            const smartSuggestionButtons = document.querySelectorAll('[data-smart-suggest]');
            smartSuggestionButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const startIso = button.dataset.smartStart;
                    const endIso = button.dataset.smartEnd;
                    const timezone = button.dataset.smartTimezone || 'UTC';
                    const note = button.dataset.smartNote || '';
                    const locale = document.documentElement.lang || 'en';

                    const fallbackDate = new Date();
                    fallbackDate.setDate(fallbackDate.getDate() + 3);
                    fallbackDate.setHours(19, 0, 0, 0);

                    const baseDate = startIso ? new Date(startIso) : fallbackDate;
                    const displayDate = Number.isNaN(baseDate.getTime()) ? fallbackDate : baseDate;

                    const formatter = new Intl.DateTimeFormat(locale, {
                        dateStyle: 'full',
                        timeStyle: 'short',
                        timeZone: timezone,
                    });

                    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || timezone;
                    const userFormatter = new Intl.DateTimeFormat(locale, {
                        dateStyle: 'full',
                        timeStyle: 'short',
                        timeZone: userTimezone,
                    });

                    const parts = [
                        locale.startsWith('ar') ? 'Ø§Ù‚ØªØ±Ø§Ø­ ÙˆÙ‚Øª Ù„Ù„ÙˆØ±Ø´Ø©' : 'Suggested workshop time',
                        `${formatter.format(displayDate)} (${timezone})`,
                        `${locale.startsWith('ar') ? 'Ø¹Ù†Ø¯Ùƒ' : 'Your time'}: ${userFormatter.format(displayDate)} (${userTimezone})`,
                    ];

                    if (endIso) {
                        const endDate = new Date(endIso);
                        if (!Number.isNaN(endDate.getTime())) {
                            parts.push(
                                locale.startsWith('ar')
                                    ? `ÙŠÙ†ØªÙ‡ÙŠ ØªÙ‚Ø±ÙŠØ¨Ø§Ù‹ ÙÙŠ ${formatter.format(endDate)}`
                                    : `Ends around ${formatter.format(endDate)}`
                            );
                        }
                    }

                    if (note) {
                        parts.push(note);
                    }

                    alert(parts.join('\n'));
                });
            });
        });
    </script>
@endpush

