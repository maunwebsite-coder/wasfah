@extends('layouts.app')

@section('title', __('links.meta.title'))

@push('preloads')
    <meta name="description" content="{{ __('links.meta.description') }}">
    <meta property="og:title" content="{{ __('links.meta.og_title') }}">
    <meta property="og:description" content="{{ __('links.meta.og_description') }}">
    <meta property="og:image" content="{{ \App\Support\BrandAssets::logoAsset('webp') }}">
@endpush

@php
    $hero = __('links.hero');
    $heroStats = $hero['stats'] ?? [];
    $monthlyCopy = __('links.monthly');
    $coreCards = __('links.core.cards');
    $showAdminTools = auth()->user()?->isAdmin() ?? false;
    $coreCardOrder = ['workshops', 'recipes'];
    $coreCardRoutes = [
        'workshops' => route('workshops'),
        'recipes' => route('recipes'),
    ];
    if ($showAdminTools) {
        $coreCardOrder[] = 'tools';
        $coreCardRoutes['tools'] = route('tools');
    }
    $socialCopy = __('links.social');
    $modeIcon = 'fa-globe';

    if (! empty($upcomingWorkshop['mode'] ?? null) && ! preg_match('/online|أونلاين|اونلاين|عن بعد/i', $upcomingWorkshop['mode'])) {
        $modeIcon = 'fa-location-dot';
    }
@endphp

@section('content')
<div class="bg-gradient-to-b from-orange-50/60 via-white to-white">
    <div class="container mx-auto px-4 py-10 space-y-10 lg:py-14">
        <section class="rounded-3xl bg-white border border-orange-100 shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 text-white px-6 py-10 sm:px-10">
                <p class="text-xs uppercase tracking-[0.35em] text-white/80 mb-3">{{ $hero['eyebrow'] }}</p>
                <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $hero['title'] }}</h1>
                <p class="text-white/90 max-w-3xl leading-relaxed">{{ $hero['description'] }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('workshops') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold shadow hover:bg-white/20">
                        <i class="fas fa-play-circle"></i>
                        {{ $hero['primary_cta'] }}
                    </a>
                    <a href="{{ route('recipes') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-white text-orange-700 px-5 py-3 text-sm font-semibold shadow hover:bg-orange-50">
                        <i class="fas fa-book-open"></i>
                        {{ $hero['secondary_cta'] }}
                    </a>
                </div>
            </div>
            @if (! empty($heroStats))
                <div class="grid gap-4 px-6 py-6 sm:grid-cols-2 md:grid-cols-3 bg-white">
                    @foreach ($heroStats as $stat)
                        <div class="rounded-2xl border border-orange-100 bg-orange-50/50 p-4 text-center shadow-inner">
                            <p class="text-lg font-semibold text-slate-900">{{ $stat['label'] }}</p>
                            <p class="text-sm text-slate-600">{{ $stat['sub'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if ($upcomingWorkshop)
            <section class="rounded-3xl bg-white border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center">
                    <div class="flex-1 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ __('links.upcoming.tag') }}</p>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $upcomingWorkshop['title'] }}</h2>
                        <p class="text-gray-600">
                            {{ __('links.upcoming.body', ['instructor' => $upcomingWorkshop['instructor'] ?? __('links.upcoming.default_instructor')]) }}
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2 text-sm text-gray-700">
                            @if (! empty($upcomingWorkshop['start_date']))
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-orange-500"></i>
                                    <span>{{ $upcomingWorkshop['start_date'] }}</span>
                                </div>
                            @endif
                            @if (! empty($upcomingWorkshop['mode']))
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $modeIcon }} text-emerald-500"></i>
                                    <span>{{ $upcomingWorkshop['mode'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('workshop.show', ['workshop' => $upcomingWorkshop['slug']]) }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-orange-600">
                                <i class="fas fa-arrow-up-right-from-square"></i>
                                {{ __('links.upcoming.cta') }}
                            </a>
                            <a href="{{ route('workshops') }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 hover:border-orange-200 hover:text-orange-700">
                                <i class="fas fa-list"></i>
                                {{ $hero['primary_cta'] }}
                            </a>
                        </div>
                    </div>
                    <div class="w-full max-w-sm mx-auto lg:mx-0">
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 shadow-inner">
                            <img src="{{ $upcomingWorkshop['image'] }}"
                                 alt="{{ __('links.upcoming.image_alt') }}"
                                 class="w-full h-full object-cover"
                                 loading="lazy">
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="rounded-3xl bg-white border border-gray-100 shadow-sm p-6 lg:p-8 space-y-6">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ $monthlyCopy['kicker'] }}</p>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $monthlyCopy['title'] }}</h2>
                    <p class="text-sm text-gray-500">{{ $monthlyCopy['hint'] }}</p>
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($monthlySelections as $selection)
                    <article class="h-full rounded-2xl border border-gray-100 bg-orange-50/40 shadow-sm overflow-hidden flex flex-col">
                        <div class="h-40 bg-white">
                            <img src="{{ $selection['image'] }}"
                                 alt="{{ $selection['alt'] }}"
                                 class="h-full w-full object-cover"
                                 loading="lazy">
                        </div>
                        <div class="flex flex-col gap-3 p-5 flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $selection['title'] }}</h3>
                            <a href="{{ $selection['url'] }}"
                               class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700">
                                {{ $monthlyCopy['cta'] }}
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl bg-white border border-gray-100 shadow-sm p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ __('links.core.aria_label') }}</p>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $hero['eyebrow'] }}</h2>
                    <p class="text-sm text-gray-500">{{ $hero['description'] }}</p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($coreCardOrder as $cardKey)
                    @php $card = $coreCards[$cardKey] ?? null; @endphp
                    @if ($card)
                        <article class="rounded-2xl border border-gray-100 bg-gray-50 p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $card['title'] }}</h3>
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-inner text-orange-500">
                                    @if ($cardKey === 'workshops')
                                        <i class="fas fa-video"></i>
                                    @elseif ($cardKey === 'recipes')
                                        <i class="fas fa-book-open"></i>
                                    @else
                                        <i class="fas fa-toolbox"></i>
                                    @endif
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 flex-1">{{ $card['body'] }}</p>
                            <a href="{{ $coreCardRoutes[$cardKey] }}"
                               class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700">
                                {{ $card['cta'] }}
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl bg-white border border-gray-100 shadow-sm p-6 lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ $socialCopy['kicker'] }}</p>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $socialCopy['title'] }}</h2>
                    <p class="text-sm text-gray-500 mt-2">{{ $socialCopy['body'] }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 text-pink-600 hover:border-orange-200 hover:text-orange-600"
                       href="https://www.instagram.com/thedolci.jo/"
                       target="_blank"
                       rel="noopener">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 text-red-600 hover:border-orange-200 hover:text-orange-600"
                       href="https://www.youtube.com/@wasfah.jordan"
                       target="_blank"
                       rel="noopener">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
