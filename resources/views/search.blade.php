@extends('layouts.app')

@section('title', __('search.meta_title'))

@php
    $isRtl = app()->getLocale() === 'ar';

    $typeOptions = [
        'all' => [
            'label' => __('search.types.all'),
            'icon' => 'fa-border-all',
            'count' => $recipes->count() + $workshops->count(),
        ],
        'recipes' => [
            'label' => __('search.types.recipes'),
            'icon' => 'fa-bowl-food',
            'count' => $recipes->count(),
        ],
        'workshops' => [
            'label' => __('search.types.workshops'),
            'icon' => 'fa-chalkboard-teacher',
            'count' => $workshops->count(),
        ],
    ];

    $hasQuery = filled($query);
    $totalResults = $typeOptions['all']['count'];

    $highlight = function (?string $text) use ($hasQuery, $query) {
        if (! $text) {
            return '';
        }

        $clean = e(strip_tags($text));

        if (! $hasQuery || trim($query) === '') {
            return $clean;
        }

        $pattern = '~(' . preg_quote($query, '~') . ')~iu';
        $result = preg_replace($pattern, '<mark class="SearchHighlight">$1</mark>', $clean);

        return $result ?? $clean;
    };

    $suggestedChips = \Illuminate\Support\Arr::wrap(trans('search.inactive.chips'));
    $inputDirection = $isRtl ? 'rtl' : 'ltr';
@endphp

@push('styles')
<style>
    .SearchHighlight {
        background-color: rgba(251, 146, 60, 0.18);
        color: #4d1f22;
        border-radius: 0.45rem;
        padding: 0 0.35rem;
    }

    .SearchCard {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .SearchCard:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 45px rgba(248, 113, 113, 0.16);
    }
</style>
@endpush

@section('content')
<div class="bg-gradient-to-b from-orange-50/60 via-white to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        <div class="rounded-3xl border border-orange-100 bg-white shadow-sm">
            <div class="px-6 py-8 sm:px-10 sm:py-10 space-y-6">
                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-widest text-orange-500">{{ __('search.badge') }}</p>
                    @if($hasQuery)
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
                            {{ __('search.heading.results_prefix') }}
                            <span class="text-orange-500">"{{ $query }}"</span>
                        </h1>
                        <p class="text-slate-500 text-sm sm:text-base">
                            {{ __('search.heading.results_description') }}
                        </p>
                    @else
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">{{ __('search.heading.empty_title') }}</h1>
                        <p class="text-slate-500 text-sm sm:text-base">
                            {{ __('search.heading.empty_description') }}
                        </p>
                    @endif
                </div>

                <form method="GET" action="{{ route('search') }}" class="relative">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="relative">
                        <input name="q"
                               dir="{{ $inputDirection }}"
                               type="text"
                               value="{{ $query }}"
                               placeholder="{{ __('search.form.placeholder') }}"
                               class="w-full rounded-2xl border border-orange-100 bg-orange-50/70 {{ $isRtl ? 'pr-14 pl-5' : 'pr-5 pl-14' }} py-4 text-base text-slate-700 placeholder:text-slate-400 focus:border-orange-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-200">
                        <button type="submit" class="absolute {{ $isRtl ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-orange-500 text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-200">
                            <i class="fas fa-search text-sm"></i>
                        </button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-2">
                    @foreach ($typeOptions as $optionType => $option)
                        @php
                            $isActive = $type === $optionType;
                            $queryParams = array_filter([
                                'q' => $query,
                                'type' => $optionType,
                            ], fn ($value) => filled($value));
                        @endphp
                        <a href="{{ route('search', $queryParams) }}"
                           class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $isActive ? 'border-orange-400 bg-orange-500 text-white shadow-sm' : 'border-orange-100 bg-white text-slate-600 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600' }}">
                            <i class="fas {{ $option['icon'] }} text-xs"></i>
                            <span>{{ $option['label'] }}</span>
                            <span class="text-xs font-semibold opacity-80">({{ $option['count'] }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-orange-100 bg-white p-5 text-center shadow-sm">
                <span class="text-sm font-semibold text-orange-500">{{ __('search.stats.total.label') }}</span>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalResults }}</p>
                <p class="text-sm text-slate-500">{{ __('search.stats.total.subtitle') }}</p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5 text-center shadow-sm">
                <span class="text-sm font-semibold text-orange-500">{{ __('search.stats.recipes.label') }}</span>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $typeOptions['recipes']['count'] }}</p>
                <p class="text-sm text-slate-500">{{ __('search.stats.recipes.subtitle') }}</p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5 text-center shadow-sm">
                <span class="text-sm font-semibold text-orange-500">{{ __('search.stats.workshops.label') }}</span>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $typeOptions['workshops']['count'] }}</p>
                <p class="text-sm text-slate-500">{{ __('search.stats.workshops.subtitle') }}</p>
            </div>
        </div>

        @if($hasQuery)
            <div class="space-y-16">
                @if($type === 'all' || $type === 'recipes')
                    <section>
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-900">{{ __('search.recipes.title') }}</h2>
                                <p class="text-sm text-slate-500">
                                    {{ $recipes->count()
                                        ? __('search.recipes.summary.found', ['count' => $recipes->count()])
                                        : __('search.recipes.summary.empty') }}
                                </p>
                            </div>
                            @if($recipes->count())
                                <a href="{{ route('recipes') }}" class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-600 transition hover:border-orange-300 hover:bg-orange-50">
                                    {{ __('search.recipes.view_all') }}
                                    <i class="fas fa-arrow-left text-xs"></i>
                                </a>
                            @endif
                        </div>

                        @if($recipes->count())
                            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($recipes as $recipe)
                                    @php
                                        $image = $recipe->image_url ?: ($recipe->image ? (str_starts_with($recipe->image, 'http') ? $recipe->image : asset('storage/' . $recipe->image)) : \App\Support\BrandAssets::logoAsset('webp'));
                                        $prepTime = $recipe->prep_time
                                            ? __('search.recipes.prep_time', ['minutes' => $recipe->prep_time])
                                            : __('search.recipes.flex_time');
                                        $excerpt = \Illuminate\Support\Str::limit($recipe->description ?? '', 140);
                                    @endphp
                                    <article class="SearchCard group flex flex-col overflow-hidden rounded-3xl border border-orange-50 bg-white shadow-sm">
                                        <div class="relative h-56 overflow-hidden">
                                            <img src="{{ $image }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                                            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/70 via-black/30 to-transparent px-4 py-3 text-xs text-white">
                                                <span class="flex items-center gap-2 font-medium">
                                                    <i class="fas fa-tag text-[11px] opacity-80"></i>
                                                    {{ $recipe->category->name ?? __('search.recipes.category_fallback') }}
                                                </span>
                                                <span class="flex items-center gap-1 tracking-wide">
                                                    <i class="far fa-clock text-[11px]"></i>
                                                    {{ $prepTime }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex flex-1 flex-col space-y-4 p-6">
                                            <div class="space-y-2">
                                                <h3 class="text-xl font-semibold leading-tight text-slate-900">{!! $highlight($recipe->title) !!}</h3>
                                                <p class="text-sm leading-6 text-slate-500">{!! $highlight($excerpt) !!}</p>
                                            </div>
                                            <div class="mt-auto flex items-center justify-between text-sm text-slate-500">
                                                <span class="flex items-center gap-2">
                                                    <i class="fas fa-user text-orange-400"></i>
                                                    {{ $recipe->author ?? __('search.recipes.author_fallback') }}
                                                </span>
                                                <span class="flex items-center gap-2">
                                                    <i class="fas fa-bookmark text-orange-400"></i>
                                                    {{ __('search.recipes.saved', ['count' => number_format($recipe->saved_count ?? 0)]) }}
                                                </span>
                                            </div>
                                            <a href="{{ route('recipe.show', $recipe->slug) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-2 focus:ring-offset-white">
                                                <span>{{ __('search.recipes.view_recipe') }}</span>
                                                <i class="fas fa-arrow-left text-xs"></i>
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-orange-200 bg-orange-50/40 px-6 py-10 text-center text-slate-600">
                                <i class="fas fa-cookie-bite mb-4 text-4xl text-orange-400"></i>
                                <p class="text-base font-medium">{{ __('search.recipes.empty_state') }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                @if($type === 'all' || $type === 'workshops')
                    <section>
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-900">{{ __('search.workshops.title') }}</h2>
                                <p class="text-sm text-slate-500">
                                    {{ $workshops->count()
                                        ? __('search.workshops.summary.found', ['count' => $workshops->count()])
                                        : __('search.workshops.summary.empty') }}
                                </p>
                            </div>
                            @if($workshops->count())
                                <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-600 transition hover:border-orange-300 hover:bg-orange-50">
                                    {{ __('search.workshops.view_all') }}
                                    <i class="fas fa-arrow-left text-xs"></i>
                                </a>
                            @endif
                        </div>

                        @if($workshops->count())
                            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($workshops as $workshop)
                                    @php
                                            $workshopImage = $workshop->image
                                                ? (str_starts_with($workshop->image, 'http') ? $workshop->image : asset('storage/' . $workshop->image))
                                                : \App\Support\BrandAssets::logoAsset('webp');
                                        $workshopExcerpt = \Illuminate\Support\Str::limit($workshop->description ?? '', 140);
                                        $workshopDate = optional($workshop->start_date)->format('Y-m-d');
                                    @endphp
                                    <article class="SearchCard group flex flex-col overflow-hidden rounded-3xl border border-orange-50 bg-white shadow-sm">
                                        <div class="relative h-56 overflow-hidden">
                                            <img src="{{ $workshopImage }}" alt="{{ $workshop->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                                            <div class="absolute inset-x-0 top-4 flex items-start justify-between px-4">
                                                <span class="rounded-full bg-orange-500/90 px-3 py-1 text-xs font-semibold text-white shadow">
                                                    {{ $workshop->formatted_price ?? number_format($workshop->price ?? 0, 2) . ' ' . ($workshop->currency ?? config('finance.default_currency', 'JOD')) }}
                                                </span>
                                                @if($workshop->is_featured)
                                                    <span class="rounded-full bg-amber-400/90 px-3 py-1 text-xs font-semibold text-white shadow">
                                                        {{ __('search.workshops.featured') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-1 flex-col space-y-4 p-6">
                                            <div class="space-y-2">
                                                <h3 class="text-xl font-semibold leading-tight text-slate-900">{!! $highlight($workshop->title) !!}</h3>
                                                <p class="text-sm leading-6 text-slate-500">{!! $highlight($workshopExcerpt) !!}</p>
                                            </div>
                                            <dl class="grid grid-cols-2 gap-4 text-xs text-slate-500">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-user-circle text-orange-400"></i>
                                                    <span>{{ $workshop->instructor }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 justify-end">
                                                    <i class="fas fa-calendar text-orange-400"></i>
                                                    <span>{{ $workshopDate ?? __('search.workshops.date_flexible') }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-map-marker-alt text-orange-400"></i>
                                                    <span>{{ $workshop->is_online ? __('search.workshops.online') : ($workshop->location ?? __('search.workshops.location_pending')) }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 justify-end">
                                                    <i class="fas fa-star text-orange-400"></i>
                                                    <span>{{ number_format($workshop->rating ?? 0, 1) }} <small class="opacity-70">({{ $workshop->reviews_count ?? 0 }})</small></span>
                                                </div>
                                            </dl>
                                            <a href="{{ route('workshop.show', $workshop->slug) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-2 focus:ring-offset-white">
                                                <span>{{ __('search.workshops.view_workshop') }}</span>
                                                <i class="fas fa-arrow-left text-xs"></i>
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-orange-200 bg-orange-50/40 px-6 py-10 text-center text-slate-600">
                                <i class="fas fa-chalkboard-teacher mb-4 text-4xl text-orange-400"></i>
                                <p class="text-base font-medium">{{ __('search.workshops.empty_state') }}</p>
                            </div>
                        @endif
                    </section>
                @endif
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-orange-200 bg-white px-8 py-12 text-center shadow-sm">
                <i class="fas fa-search mb-4 text-4xl text-orange-400"></i>
                <h2 class="text-2xl font-semibold text-slate-900 mb-2">{{ __('search.inactive.title') }}</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">
                    {{ __('search.inactive.description') }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm text-orange-600">
                    @foreach($suggestedChips as $chip)
                        <span class="rounded-full bg-orange-50 px-4 py-2">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($hasQuery && $totalResults === 0)
            <div class="rounded-3xl border border-orange-100 bg-white px-8 py-12 shadow-sm">
                <div class="mx-auto max-w-2xl text-center space-y-4">
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('search.no_results.title') }}</h2>
                    <p class="text-slate-500">{{ __('search.no_results.description') }}</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">
                            {{ __('search.no_results.home') }}
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                        <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-full border border-orange-200 px-5 py-2.5 text-sm font-semibold text-orange-600 transition hover:border-orange-300 hover:bg-orange-50">
                            {{ __('search.no_results.workshops') }}
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection






