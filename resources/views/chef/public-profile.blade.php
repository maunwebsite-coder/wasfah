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

    $statsAverage = $stats['average_rating']
        ? number_format($stats['average_rating'], 1)
        : '—';

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
    $recipesCount = max(0, (int) $stats['recipes_count']);
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
    $privateRecordingsUrl = route('chef.workshops.recordings');
    $canViewPrivateRecordings = $isOwner && $chef->isChef() && $hasRecordedWorkshops;
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
                                <p class="text-sm text-slate-500">{{ __('chef.hero.stats.recipes') }}: {{ number_format($recipesCount) }}</p>
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
                                @if ($canViewPrivateRecordings)
                                    <a
                                        href="{{ $privateRecordingsUrl }}"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-indigo-100 bg-white px-4 py-2 text-xs font-semibold text-indigo-700 transition hover:border-indigo-200 hover:bg-indigo-50 md:text-sm"
                                    >
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        <span>{{ __('chef.dashboard.workshops.ctas.recordings') }}</span>
                                    </a>
                                @endif
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

                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
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
                        <p class="text-xs text-slate-500">{{ __('chef.hero.stats.recipes') }}</p>
                        <p class="text-xl font-bold text-slate-900">{{ number_format($recipesCount) }}</p>
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
                    <span class="chef-chip">
                        <i class="fa-solid fa-utensils text-emerald-500"></i>
                        {{ __('chef.recipes.likes', ['count' => number_format((int) $stats['total_made'])]) }}
                    </span>
                    <span class="chef-chip">
                        <i class="fa-solid fa-star text-amber-400"></i>
                        {{ __('chef.recipes.likes', ['count' => number_format((int) $stats['rating_count'])]) }}
                    </span>
                </div>
            </section>

            @if ($recordingEntries->isNotEmpty())
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
                        @foreach ($recordingEntries as $entry)
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


            @if ($popularRecipes->isNotEmpty())
                <section class="chef-card rounded-3xl bg-white p-6 md:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.popular.title') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('chef.popular.subtitle') }}</p>
                        </div>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($popularRecipes as $recipe)
                            <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="block h-48 w-full overflow-hidden bg-gray-100">
                                    <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover transition duration-200 hover:scale-[1.02]" loading="lazy">
                                </a>
                                <div class="flex flex-1 flex-col gap-3 p-4">
                                    <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                        <span class="chef-chip">
                                            <i class="fa-solid fa-star text-amber-400"></i>
                                            {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : '—' }}
                                        </span>
                                        <span class="chef-chip">
                                            <i class="fa-solid fa-bookmark"></i>
                                            {{ __('chef.recipes.saves', ['count' => number_format($recipe->saved_count ?? 0)]) }}
                                        </span>
                                        <span class="chef-chip">
                                            <i class="fa-solid fa-heart"></i>
                                            {{ __('chef.recipes.likes', ['count' => number_format($recipe->rating_count ?? 0)]) }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ $recipe->title }}</h3>
                                    <div class="mt-auto pt-2">
                                        <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                            {{ __('chef.popular.view_details') }}
                                            <i class="fa-solid {{ $arrowIcon }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
            <section class="chef-card rounded-3xl bg-white p-6 md:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.hero.stats.recipes') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('chef.recipes.tabs.public') }} @if ($canViewExclusive) • {{ __('chef.recipes.tabs.exclusive') }} @endif</p>
                    </div>
                    <div class="chef-tab-nav" role="tablist">
                        <button class="chef-tab-btn is-active" data-tab="public" type="button" role="tab" aria-selected="true">
                            {{ __('chef.recipes.tabs.public') }}
                        </button>
                        @if ($canViewExclusive)
                            <button class="chef-tab-btn" data-tab="exclusive" type="button" role="tab" aria-selected="false">
                                {{ __('chef.recipes.tabs.exclusive') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <div class="chef-tab-panel is-active" data-panel="public" role="tabpanel">
                        @if ($publicRecipes->isEmpty())
                            <div class="chef-empty-state">
                                {{ __('chef.recipes.public_empty') }}
                            </div>
                        @else
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($publicRecipes as $recipe)
                                    <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                        <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="block h-48 w-full overflow-hidden bg-gray-100">
                                            <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover transition duration-200 hover:scale-[1.02]" loading="lazy">
                                        </a>
                                        <div class="flex flex-1 flex-col gap-3 p-4">
                                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                                @if ($recipe->category)
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-tag"></i>
                                                        {{ $recipe->category->category_name ?? __('chef.recipes.category_fallback') }}
                                                    </span>
                                                @endif
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-star text-amber-400"></i>
                                                    {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : __('chef.recipes.no_rating') }}
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-900">{{ $recipe->title }}</h3>
                                            <div class="flex flex-wrap gap-2 text-sm font-semibold text-slate-700">
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-bookmark"></i>
                                                    {{ __('chef.recipes.saves', ['count' => number_format($recipe->saved_count ?? 0)]) }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-heart"></i>
                                                    {{ __('chef.recipes.likes', ['count' => number_format($recipe->rating_count ?? 0)]) }}
                                                </span>
                                            </div>
                                            <div class="mt-auto pt-2">
                                                <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                                    {{ __('chef.recipes.view_recipe') }}
                                                    <i class="fa-solid {{ $arrowIcon }}"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($canViewExclusive)
                        <div class="chef-tab-panel" data-panel="exclusive" role="tabpanel">
                            @if ($exclusiveRecipes->isEmpty())
                                <div class="chef-empty-state">
                                    {{ __('chef.recipes.exclusive_empty') }}
                                </div>
                            @else
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($exclusiveRecipes as $recipe)
                                        <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="block h-48 w-full overflow-hidden bg-gray-100">
                                                <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover transition duration-200 hover:scale-[1.02]" loading="lazy">
                                            </a>
                                            <div class="flex flex-1 flex-col gap-3 p-4">
                                                <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-lock"></i>
                                                        {{ __('chef.recipes.private_tag') }}
                                                    </span>
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-star text-amber-400"></i>
                                                        {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : __('chef.recipes.no_rating') }}
                                                    </span>
                                                </div>
                                                <h3 class="text-lg font-bold text-slate-900">{{ $recipe->title }}</h3>
                                                <div class="flex flex-wrap gap-2 text-sm font-semibold text-slate-700">
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-book-open"></i>
                                                        {{ __('chef.recipes.private_details') }}
                                                    </span>
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-shield-halved"></i>
                                                        {{ __('chef.recipes.private_access') }}
                                                    </span>
                                                </div>
                                                <div class="mt-auto pt-2">
                                                    <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                                        {{ __('chef.recipes.view_recipe') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

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
                                            : __('chef.workshops.tbd_time');
                                        $locationLabel = $workshop->is_online
                                            ? __('chef.workshops.online_live')
                                            : ($workshop->location ?: __('chef.workshops.location_tbd'));
                                        $priceLabel = $workshop->formatted_price
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
                                        $currentBookings = number_format((int) ($workshop->bookings_count ?? 0));
                                        $maxParticipants = $workshop->max_participants ? number_format((int) $workshop->max_participants) : null;
                                        $capacityLabel = $workshop->max_participants
                                            ? __('chef.workshops.capacity_with_limit', ['current' => $currentBookings, 'max' => $maxParticipants])
                                            : __('chef.workshops.capacity_open', ['count' => $currentBookings]);
                                        $deadlineLabel = $workshop->registration_deadline
                                            ? $workshop->registration_deadline->copy()->locale($carbonLocale)->translatedFormat($workshopDateFormat)
                                            : null;
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
                                            <ul class="space-y-1 text-sm text-slate-600">
                                                <li class="flex items-center gap-2">
                                                    <i class="fa-solid fa-calendar-day text-amber-500"></i>
                                                    {{ $startDateLabel }}
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                                    {{ $locationLabel }}
                                                </li>
                                                @if ($deadlineLabel)
                                                    <li class="flex items-center gap-2">
                                                        <i class="fa-solid fa-hourglass-half text-slate-500"></i>
                                                        {{ __('chef.workshops.register_until', ['date' => $deadlineLabel]) }}
                                                    </li>
                                                @endif
                                            </ul>
                                            <div class="mt-auto pt-2">
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
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
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
        });
    </script>
@endpush
