@extends('layouts.app')

@section('title', __('bookings.meta_title'))

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    use Illuminate\Support\Collection;

    $currentLocale = app()->getLocale();
    $showEmptyState = $showEmptyState ?? false;
    $viewer = auth()->user();
    $entries = collect($recordingEntries ?? []);
    $driveEntries = $entries->where('type', 'drive');
    $ownedEntries = $entries->where('is_owner', true);
    $bookingEntries = $entries->where('type', 'booking');
    $hiddenEntries = $entries->filter(fn ($entry) => !empty($entry['hidden']) || !empty($entry['hidden_global']));
    $visibleCount = max($entries->count() - $hiddenEntries->count(), 0);
    $driveConnected = $viewer?->hasGoogleDriveCredentials() ?? false;
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            <div class="rounded-3xl border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-white p-6 md:p-8 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-orange-200/25 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-48 h-48 bg-orange-100/30 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">{{ __('chef.recordings.eyebrow') }}</p>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">{{ __('bookings.recordings.hub_title') }}</h1>
                        <p class="text-sm md:text-base text-gray-600 leading-relaxed">
                            {{ __('bookings.recordings.hub_subtitle') }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-xl {{ $driveConnected ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }} px-4 py-2 text-xs font-semibold">
                                <i class="fa-solid {{ $driveConnected ? 'fa-check-circle' : 'fa-link-slash' }}"></i>
                                {{ $driveConnected ? __('bookings.recordings.drive_status_connected') : __('bookings.recordings.drive_status_disconnected') }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-layer-group text-orange-500"></i>
                                {{ __('bookings.stats.total') }}: {{ $entries->count() }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-eye text-emerald-500"></i>
                                {{ __('bookings.recordings.visible_count', ['count' => $visibleCount]) }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold text-slate-700 border border-slate-200">
                                <i class="fa-solid fa-eye-slash text-rose-500"></i>
                                {{ __('bookings.recordings.hidden_count', ['count' => $hiddenEntries->count()]) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow hover:border-slate-300">
                                <i class="fa-solid fa-calendar-check text-xs"></i>
                                {{ __('navbar.account_menu.links.bookings') }}
                            </a>
                            @if ($viewer?->isChef())
                                <a href="{{ route('chef.workshops.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-600 shadow hover:border-orange-300 hover:bg-orange-50">
                                    <i class="fas fa-screwdriver-wrench text-xs"></i>
                                    {{ __('bookings.recordings.manage_workshops') }}
                                </a>
                            @endif
                            <a href="{{ route('google.drive.redirect', ['redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 rounded-xl {{ $driveConnected ? 'bg-emerald-500 hover:bg-emerald-600 text-white border border-emerald-400' : 'bg-orange-500 hover:bg-orange-600 text-white border border-orange-400' }} px-4 py-2 text-sm font-semibold shadow">
                                <i class="fab fa-google-drive text-xs"></i>
                                {{ $driveConnected ? __('bookings.recordings.refresh_drive') : __('bookings.recordings.connect_drive') }}
                            </a>
                            <a href="https://drive.google.com/drive/u/0/my-drive" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow hover:border-slate-300">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                {{ __('bookings.recordings.open_drive') }}
                            </a>
                        </div>
                    </div>

                    <div class="grid w-full max-w-xl grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="rounded-2xl border border-white/60 bg-white/70 p-4 shadow-sm backdrop-blur">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">{{ __('bookings.recordings.hub_stats.drive') }}</span>
                                <i class="fab fa-google-drive text-lg text-orange-500"></i>
                            </div>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $driveEntries->count() }}</p>
                            <p class="text-sm text-slate-500">{{ __('bookings.recordings.drive_library_description') }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/60 bg-white/70 p-4 shadow-sm backdrop-blur">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">{{ __('bookings.recordings.hub_stats.owned') }}</span>
                                <i class="fa-solid fa-chalkboard-user text-lg text-emerald-500"></i>
                            </div>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $ownedEntries->count() }}</p>
                            <p class="text-sm text-slate-500">{{ __('bookings.recordings.manage_note') }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/60 bg-white/70 p-4 shadow-sm backdrop-blur md:col-span-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">{{ __('bookings.recordings.hub_stats.visibility') }}</span>
                                <i class="fa-solid fa-toggle-on text-lg text-orange-500"></i>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-700">
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
                                    <p class="text-xs font-semibold text-emerald-600">{{ __('bookings.recordings.visible_count', ['count' => $visibleCount]) }}</p>
                                    <p class="text-[11px] text-emerald-700">{{ __('bookings.recordings.visibility_note') }}</p>
                                </div>
                                <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2">
                                    <p class="text-xs font-semibold text-rose-600">{{ __('bookings.recordings.hidden_count', ['count' => $hiddenEntries->count()]) }}</p>
                                    <p class="text-[11px] text-rose-700">{{ __('bookings.recordings.hidden_note') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($showEmptyState)
                <div class="mt-8 rounded-3xl border border-dashed border-orange-200 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                        <i class="fa-solid fa-record-vinyl text-xl"></i>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-slate-900">{{ __('bookings.history.empty.message') }}</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('bookings.hero.description') }}
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                            <i class="fas fa-search text-xs"></i>
                            {{ __('bookings.history.empty.cta') }}
                        </a>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-600 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700">
                            <i class="fa-solid fa-house"></i>
                            {{ __('bookings.hero.profile') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($driveConnected)
                <section class="mt-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">{{ __('bookings.recordings.drive_library_title') }}</p>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('bookings.recordings.drive_focus_title') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('bookings.recordings.drive_focus_subtitle') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('google.drive.redirect', ['redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300">
                                <i class="fa-solid fa-rotate"></i>
                                {{ __('bookings.recordings.refresh_drive') }}
                            </a>
                            <a href="https://drive.google.com/drive/u/0/my-drive" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                {{ __('bookings.recordings.open_drive') }}
                            </a>
                        </div>
                    </div>

                    @if ($driveEntries->isNotEmpty())
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            @foreach ($driveEntries->take(3) as $entry)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 shadow-sm">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 font-semibold text-slate-700">
                                            <i class="fab fa-google-drive text-orange-500"></i>
                                            {{ __('bookings.recordings.hub_stats.drive') }}
                                        </span>
                                        <span class="font-semibold text-slate-600">{{ $entry['date_label'] ?? '' }}</span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $entry['title'] }}</h3>
                                    @if (!empty($entry['excerpt']))
                                        <p class="mt-1 text-sm text-slate-600">{{ $entry['excerpt'] }}</p>
                                    @endif
                                    <div class="mt-3 overflow-hidden rounded-xl border border-slate-100 bg-black/80">
                                        @if (!empty($entry['preview_url']))
                                            <iframe
                                                src="{{ $entry['preview_url'] }}"
                                                class="aspect-video w-full"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen
                                                loading="lazy"
                                            ></iframe>
                                        @elseif (!empty($entry['is_direct_video']) && !empty($entry['watch_url']))
                                            <video controls preload="metadata" playsinline class="aspect-video w-full object-cover bg-black">
                                                <source src="{{ $entry['watch_url'] }}">
                                            </video>
                                        @else
                                            <div class="flex aspect-video w-full items-center justify-center gap-2 text-xs font-semibold text-slate-200">
                                                <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                                                <span>{{ __('chef.recordings.fallback_drive') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if (!empty($entry['watch_url']))
                                            <a
                                                href="{{ $entry['watch_url'] }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-orange-600"
                                            >
                                                <i class="fa-solid fa-play"></i>
                                                {{ __('chef.recordings.cta.watch') }}
                                            </a>
                                        @endif
                                        @if (!empty($entry['details_url']))
                                            <a
                                                href="{{ $entry['details_url'] }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-gray-300"
                                            >
                                                {{ __('bookings.history.actions.details') }}
                                                <i class="fa-solid fa-arrow-right-long"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-5 text-sm text-emerald-700">
                            {{ __('bookings.recordings.drive_empty') }}
                        </div>
                    @endif
                </section>
            @endif

            @if (isset($recordingEntries) && $recordingEntries->isNotEmpty())
                @php
                    $isRtl = app()->getLocale() === 'ar';
                    $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
                @endphp
                <section class="mt-8 rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">{{ __('chef.recordings.eyebrow') }}</p>
                            <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.recordings.title') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('chef.recordings.description', ['name' => auth()->user()->name]) }}</p>
                            <p class="mt-2 text-xs font-semibold text-slate-500">{{ __('bookings.recordings.manage_note') }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-1 text-sm text-slate-500 md:items-end">
                            <span class="flex items-center gap-2 font-semibold text-slate-700">
                                <i class="fa-solid fa-layer-group text-orange-500"></i>
                                {{ __('bookings.stats.total') }}: {{ $recordingEntries->count() }}
                            </span>
                            <span class="flex items-center gap-2 text-emerald-600">
                                <i class="fa-solid fa-eye"></i>
                                {{ __('bookings.recordings.visible_count', ['count' => $visibleCount]) }}
                            </span>
                            <span class="flex items-center gap-2 text-rose-600">
                                <i class="fa-solid fa-eye-slash"></i>
                                {{ __('bookings.recordings.hidden_count', ['count' => $hiddenEntries->count()]) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('bookings.recordings.filters.title') }}">
                            <button data-filter-btn data-filter="all" type="button" class="filter-pill inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-orange-500">
                                <i class="fa-solid fa-circle-dot text-[11px]"></i>
                                {{ __('bookings.recordings.filters.all') }}
                            </button>
                            <button data-filter-btn data-filter="bookings" type="button" class="filter-pill inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300">
                                <i class="fa-solid fa-calendar-check text-[11px] text-orange-500"></i>
                                {{ __('bookings.recordings.filters.bookings') }}
                            </button>
                            <button data-filter-btn data-filter="owned" type="button" class="filter-pill inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300">
                                <i class="fa-solid fa-chalkboard-user text-[11px] text-emerald-500"></i>
                                {{ __('bookings.recordings.filters.owned') }}
                            </button>
                            <button data-filter-btn data-filter="drive" type="button" class="filter-pill inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300">
                                <i class="fab fa-google-drive text-[11px] text-orange-500"></i>
                                {{ __('bookings.recordings.filters.drive') }}
                            </button>
                            <button data-filter-btn data-filter="hidden" type="button" class="filter-pill inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300">
                                <i class="fa-solid fa-eye-slash text-[11px] text-rose-500"></i>
                                {{ __('bookings.recordings.filters.hidden') }}
                            </button>
                        </div>
                        <label class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 shadow-sm focus-within:border-orange-300 focus-within:ring-2 focus-within:ring-orange-100 md:w-96">
                            <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            <input
                                type="search"
                                class="w-full bg-transparent outline-none placeholder:text-slate-400"
                                placeholder="{{ __('bookings.recordings.filters.search_placeholder') }}"
                                data-filter-search
                            >
                        </label>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 text-[11px] text-slate-600">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ __('bookings.recordings.legend.public') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 font-semibold text-rose-700">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            {{ __('bookings.recordings.legend.hidden') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 font-semibold text-red-700">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            {{ __('bookings.recordings.legend.platform') }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3" id="recording-card-grid">
                        @foreach ($recordingEntries as $entry)
                            @php
                                $type = $entry['type'] ?? 'booking';
                                $typeIcon = match ($type) {
                                    'drive' => 'fab fa-google-drive',
                                    'workshop' => 'fa-solid fa-chalkboard-user',
                                    default => 'fa-solid fa-circle-play',
                                };
                                $typeLabel = match ($type) {
                                    'drive' => __('bookings.recordings.card_badges.drive'),
                                    'workshop' => __('bookings.recordings.card_badges.owner'),
                                    default => __('bookings.recordings.card_badges.booking'),
                                };
                                $isHidden = (!empty($entry['hidden']) && $entry['hidden']) || (!empty($entry['hidden_global']) && $entry['hidden_global']);
                                $searchable = Str::lower(trim(($entry['title'] ?? '') . ' ' . ($entry['excerpt'] ?? '')));
                            @endphp
                            <article
                                data-recording-card
                                data-recording-id="{{ $entry['id'] }}"
                                data-filter-type="{{ $type }}"
                                data-filter-owned="{{ !empty($entry['is_owner']) ? '1' : '0' }}"
                                data-filter-hidden="{{ $isHidden ? '1' : '0' }}"
                                data-filter-title="{{ e($searchable) }}"
                                data-sort="{{ $entry['sort_timestamp'] ?? 0 }}"
                                class="group relative flex flex-col overflow-hidden rounded-2xl border {{ $isHidden ? 'border-rose-100 bg-rose-50/60' : 'border-slate-100 bg-white' }} shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div class="relative grid gap-4 p-4 md:grid-cols-[1.05fr_1.35fr]">
                                    <div class="relative overflow-hidden rounded-xl border border-slate-100 bg-slate-900/80">
                                        <div class="absolute left-3 top-3 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold text-slate-800 shadow-sm">
                                            <i class="{{ $typeIcon }} text-orange-500"></i>
                                            <span>{{ $typeLabel }}</span>
                                        </div>
                                        @if ($isHidden)
                                            <div class="absolute right-3 top-3 inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-[11px] font-semibold text-rose-700 shadow-sm">
                                                <i class="fa-solid fa-eye-slash"></i>
                                                {{ __('bookings.recordings.hidden_badge') }}
                                            </div>
                                        @endif
                                        @if (!empty($entry['hidden_global']))
                                            <div class="absolute right-3 bottom-3 inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-red-700 shadow-sm">
                                                <i class="fa-solid fa-ban"></i>
                                                {{ __('bookings.recordings.hide_platform') }}
                                            </div>
                                        @endif
                                        @if (!empty($entry['poster']))
                                            <div class="absolute inset-0 opacity-60">
                                                <img src="{{ $entry['poster'] }}" alt="" class="h-full w-full object-cover">
                                            </div>
                                        @endif
                                        <div class="relative">
                                            @if (!empty($entry['preview_url']))
                                                <iframe
                                                    src="{{ $entry['preview_url'] }}"
                                                    class="aspect-video w-full"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen
                                                    loading="lazy"
                                                ></iframe>
                                            @elseif (!empty($entry['is_direct_video']) && !empty($entry['watch_url']))
                                                <video controls preload="metadata" playsinline class="aspect-video w-full object-cover">
                                                    <source src="{{ $entry['watch_url'] }}">
                                                </video>
                                            @else
                                                <div class="flex aspect-video w-full flex-col items-center justify-center gap-2 bg-slate-900/80 text-xs font-semibold text-slate-200">
                                                    <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                                                    <span>{{ __('chef.recordings.fallback_drive') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-1">
                                                <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">{{ $entry['badge'] ?? __('chef.recordings.badges.available') }}</p>
                                                <h3 class="text-lg font-bold text-slate-900">{{ $entry['title'] }}</h3>
                                                @if (!empty($entry['excerpt']))
                                                    <p class="text-sm text-slate-600">{{ $entry['excerpt'] }}</p>
                                                @endif
                                            </div>
                                            @if (!empty($entry['viewer_count']))
                                                <div class="rounded-xl bg-slate-50 px-3 py-2 text-right text-[11px] font-semibold text-slate-700">
                                                    <div class="flex items-center gap-1 text-orange-600">
                                                        <i class="fa-solid fa-users"></i>
                                                        <span>{{ $entry['viewer_count'] }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500">{{ __('bookings.recordings.viewers', ['count' => $entry['viewer_count'] ?? 0]) }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-700">
                                            @if (!empty($entry['date_label']))
                                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1">
                                                    <i class="fa-solid fa-calendar-day text-amber-500"></i>
                                                    {{ $entry['date_label'] }}
                                                </span>
                                            @endif
                                            @if (!empty($entry['location_label']))
                                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1">
                                                    <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                                    {{ $entry['location_label'] }}
                                                </span>
                                            @endif
                                            @if (!empty($entry['access']))
                                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                                    <i class="fa-solid fa-lock text-slate-500"></i>
                                                    {{ $entry['access'] }}
                                                </span>
                                            @endif
                                        </div>

                                        @php
                                            $viewerNames = $entry['viewer_names'] ?? [];
                                            $visibleViewers = array_slice($viewerNames, 0, 5);
                                            $extraViewers = max(count($viewerNames) - count($visibleViewers), 0);
                                        @endphp

                                        @if (!empty($viewerNames))
                                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-3">
                                                <div class="flex items-center justify-between text-[11px] font-semibold text-emerald-800">
                                                    <span class="inline-flex items-center gap-2">
                                                        <i class="fa-solid fa-shield-halved"></i>
                                                        {{ __('bookings.recordings.access_label') }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 text-emerald-700">
                                                        <i class="fa-solid fa-user-check"></i>
                                                        {{ __('bookings.recordings.viewer_list') }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach ($visibleViewers as $viewerName)
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-slate-700 shadow-sm">
                                                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold uppercase">
                                                                {{ Str::substr(trim($viewerName), 0, 1) ?: '•' }}
                                                            </span>
                                                            <span class="whitespace-nowrap">{{ $viewerName }}</span>
                                                        </span>
                                                    @endforeach
                                                    @if ($extraViewers > 0)
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-900/90 px-3 py-1 text-[11px] font-semibold text-white shadow-sm">
                                                            +{{ $extraViewers }}
                                                            <span class="text-[10px]">{{ __('bookings.recordings.viewers', ['count' => $extraViewers]) }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex flex-wrap gap-2">
                                                @if (!empty($entry['watch_url']))
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300"
                                                        data-copy-link="{{ $entry['watch_url'] }}"
                                                    >
                                                        <i class="fa-solid fa-link"></i>
                                                        <span data-copy-label>{{ __('bookings.recordings.copy_link') }}</span>
                                                    </button>
                                                    <a
                                                        href="{{ $entry['watch_url'] }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-orange-600 to-amber-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:from-orange-500 hover:to-amber-400"
                                                    >
                                                        <i class="fa-solid fa-play"></i>
                                                        {{ __('chef.recordings.cta.watch') }}
                                                    </a>
                                                @endif
                                                @if (!empty($entry['details_url']))
                                                    <a
                                                        href="{{ $entry['details_url'] }}"
                                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-gray-300"
                                                    >
                                                        {{ $entry['type'] === 'booking' ? __('bookings.history.actions.details') : __('chef.workshops.view_details') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            @if (!empty($viewerNames))
                                                <div class="inline-flex items-center gap-2 rounded-full border border-slate-100 bg-slate-50 px-3 py-2 text-[11px] font-semibold text-slate-700 shadow-sm">
                                                    <i class="fa-solid fa-user-lock text-orange-500"></i>
                                                    <span>{{ __('bookings.recordings.viewer_list') }}</span>
                                                    <div class="flex -space-x-2 rtl:space-x-reverse">
                                                        @foreach ($visibleViewers as $viewerName)
                                                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-emerald-100 text-[11px] font-bold uppercase text-emerald-700 shadow-sm">
                                                                {{ Str::substr(trim($viewerName), 0, 1) ?: '•' }}
                                                            </span>
                                                        @endforeach
                                                        @if ($extraViewers > 0)
                                                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-800 text-[10px] font-semibold text-white shadow-sm">
                                                                +{{ $extraViewers }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        @if (!empty($entry['is_owner']) && $entry['is_owner'] === true && !empty($entry['workshop_id']))
                                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs text-slate-700">
                                                <p class="flex items-center gap-2 font-semibold text-slate-800">
                                                    <i class="fa-solid fa-toggle-on text-orange-500"></i>
                                                    {{ __('bookings.recordings.access_label') }}
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <form action="{{ route('bookings.recordings.visibility', $entry['workshop_id']) }}" method="POST" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="hidden" value="{{ $entry['hidden'] ? 0 : 1 }}">
                                                        <input type="hidden" name="scope" value="public">
                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border {{ $entry['hidden'] ? 'border-emerald-200 text-emerald-700' : 'border-slate-200 text-slate-700' }} bg-white px-3 py-2 font-semibold transition hover:border-slate-300">
                                                            <i class="fa-solid {{ $entry['hidden'] ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                            {{ $entry['hidden'] ? __('bookings.recordings.show_global') : __('bookings.recordings.hide_global') }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('bookings.recordings.visibility', $entry['workshop_id']) }}" method="POST" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="hidden" value="{{ $entry['hidden_global'] ? 0 : 1 }}">
                                                        <input type="hidden" name="scope" value="platform">
                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border {{ $entry['hidden_global'] ? 'border-emerald-200 text-emerald-700' : 'border-red-200 text-red-600' }} bg-white px-3 py-2 font-semibold transition hover:border-slate-300">
                                                            <i class="fa-solid {{ $entry['hidden_global'] ? 'fa-eye' : 'fa-ban' }}"></i>
                                                            {{ $entry['hidden_global'] ? __('bookings.recordings.show_platform') : __('bookings.recordings.hide_platform') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-4 hidden rounded-2xl border border-dashed border-orange-200 bg-orange-50 px-4 py-5 text-sm font-semibold text-orange-800" data-recording-empty>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-compass"></i>
                            <span>{{ __('bookings.recordings.empty_filtered') }}</span>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-copy-link]').forEach((btn) => {
                const label = btn.querySelector('[data-copy-label]');
                const originalLabel = label ? label.textContent : btn.textContent;

                btn.addEventListener('click', async () => {
                    const url = btn.getAttribute('data-copy-link');
                    if (!url) return;
                    try {
                        await navigator.clipboard.writeText(url);
                        if (label) {
                            label.textContent = '{{ __('bookings.recordings.copied') }}';
                        } else {
                            btn.textContent = '{{ __('bookings.recordings.copied') }}';
                        }

                        setTimeout(() => {
                            if (label) {
                                label.textContent = originalLabel;
                            } else {
                                btn.textContent = originalLabel;
                            }
                        }, 1400);
                    } catch (error) {
                        alert(url);
                    }
                });
            });

            const cards = Array.from(document.querySelectorAll('[data-recording-card]'));
            const filterButtons = document.querySelectorAll('[data-filter-btn]');
            const searchInput = document.querySelector('[data-filter-search]');
            const emptyState = document.querySelector('[data-recording-empty]');
            let activeFilter = 'all';

            const applyFilters = () => {
                const term = (searchInput?.value || '').toLowerCase().trim();
                let visibleCount = 0;

                cards.forEach((card) => {
                    const matchesFilter =
                        activeFilter === 'all' ||
                        (activeFilter === 'bookings' && card.dataset.filterType === 'booking') ||
                        (activeFilter === 'owned' && card.dataset.filterOwned === '1') ||
                        (activeFilter === 'drive' && card.dataset.filterType === 'drive') ||
                        (activeFilter === 'hidden' && card.dataset.filterHidden === '1');

                    const matchesSearch = !term || (card.dataset.filterTitle || '').includes(term);
                    const shouldShow = matchesFilter && matchesSearch;

                    card.classList.toggle('hidden', !shouldShow);
                    if (shouldShow) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', visibleCount !== 0);
                }
            };

            filterButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    activeFilter = btn.dataset.filter || 'all';

                    filterButtons.forEach((button) => {
                        button.classList.remove('bg-orange-600', 'text-white', 'border-orange-200', 'shadow');
                        button.classList.add('bg-white', 'border-slate-200', 'text-slate-700');
                    });

                    btn.classList.remove('bg-white', 'border-slate-200', 'text-slate-700');
                    btn.classList.add('bg-orange-600', 'text-white', 'border-orange-200', 'shadow');

                    applyFilters();
                });
            });

            searchInput?.addEventListener('input', applyFilters);
            applyFilters();
        })();
    </script>
@endpush
