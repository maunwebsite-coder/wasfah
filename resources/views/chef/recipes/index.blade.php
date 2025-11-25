@extends('layouts.app')

@section('title', __('chef.title'))

@section('content')
@php
    use App\Models\Recipe;
    use Illuminate\Support\Facades\Storage;

    $locale = app()->getLocale();
    $dateTimeFormat = __('chef.formats.date_time');
    $tableDateTimeFormat = __('chef.formats.table_date_time');
    $dateFormat = __('chef.formats.date');

    $statusMeta = [
        Recipe::STATUS_DRAFT => [
            'label' => __('chef.status.labels.draft'),
            'description' => __('chef.status.descriptions.draft'),
            'bg' => 'bg-gray-100',
            'text' => 'text-gray-700',
        ],
        Recipe::STATUS_PENDING => [
            'label' => __('chef.status.labels.pending'),
            'description' => __('chef.status.descriptions.pending'),
            'bg' => 'bg-orange-100',
            'text' => 'text-orange-700',
        ],
        Recipe::STATUS_APPROVED => [
            'label' => __('chef.status.labels.approved'),
            'description' => __('chef.status.descriptions.approved'),
            'bg' => 'bg-emerald-100',
            'text' => 'text-emerald-700',
        ],
        Recipe::STATUS_REJECTED => [
            'label' => __('chef.status.labels.rejected'),
            'description' => __('chef.status.descriptions.rejected'),
            'bg' => 'bg-red-100',
            'text' => 'text-red-700',
        ],
    ];

    $visibilityMeta = [
        Recipe::VISIBILITY_PUBLIC => [
            'label' => __('chef.visibility.public.label'),
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'icon' => 'fa-earth-americas',
            'hint' => __('chef.visibility.public.hint'),
        ],
        Recipe::VISIBILITY_PRIVATE => [
            'label' => __('chef.visibility.private.label'),
            'bg' => 'bg-slate-100',
            'text' => 'text-slate-700',
            'icon' => 'fa-lock',
            'hint' => __('chef.visibility.private.hint'),
        ],
    ];

    $publicProfileUrl = auth()->check()
        ? route('chefs.show', ['chef' => auth()->id()])
        : null;

    $currentUser = auth()->user();
    $chefName = $currentUser?->name ?? __('navbar.mobile_banner.guest');
    $canViewRawMeetingLink = $currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin();
    $canManageRecipes = $currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin();
@endphp

<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wider text-orange-500 font-semibold mb-2">{{ __('chef.hero.badge') }}</p>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('chef.hero.heading', ['name' => $chefName]) }}</h1>
                <p class="text-gray-600 mt-1">{{ __('chef.hero.description') }}</p>
            </div>
            <div class="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto sm:flex-row sm:items-stretch sm:justify-end">
                @if ($canManageRecipes)
                    <a href="{{ route('chef.recipes.create') }}" class="order-1 sm:order-5 col-span-2 sm:col-span-1 group flex w-full sm:w-auto items-center gap-3 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3 text-white font-semibold shadow-[0_12px_30px_rgba(249,115,22,0.35)] transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-[0_18px_45px_rgba(234,88,12,0.4)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white/70">
                        <span class="flex h-11 w-11 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-lg text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                        </span>
                        <span class="flex flex-col text-left leading-tight">
                            <span>{{ __('chef.hero.actions.new_recipe') }}</span>
                        </span>
                    </a>
                @endif
                <a href="{{ route('chef.workshops.create') }}" class="order-2 sm:order-4 col-span-2 sm:col-span-1 group flex w-full sm:w-auto items-center gap-3 rounded-full bg-gradient-to-r from-blue-700 via-blue-600 to-blue-500 px-6 py-3 text-white font-semibold shadow-[0_12px_30px_rgba(37,99,235,0.32)] transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-[0_18px_42px_rgba(37,99,235,0.45)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white/70">
                    <span class="flex h-11 w-11 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-lg text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    </span>
                    <span class="flex flex-col text-left leading-tight">
                        <span>{{ __('chef.hero.actions.new_workshop') }}</span>
                    </span>
                </a>
                <a href="{{ route('chef.workshops.index') }}" class="order-3 sm:order-3 inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-5 py-3 text-indigo-600 font-semibold shadow-sm hover:border-indigo-300 hover:bg-indigo-50 transition">
                    <i class="fas fa-video"></i>
                    {{ __('chef.hero.actions.workshops') }}
                </a>
                <a href="{{ route('chef.workshops.earnings') }}" class="order-4 sm:order-2 inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-5 py-3 text-emerald-600 font-semibold shadow-sm hover:border-emerald-300 hover:bg-emerald-50 transition">
                    <i class="fas fa-wallet"></i>
                    {{ __('chef.hero.actions.earnings') }}
                </a>
                @if ($publicProfileUrl)
                    <a href="{{ $publicProfileUrl }}" target="_blank" rel="noopener" class="order-5 sm:order-1 inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-orange-600 font-semibold shadow-sm hover:bg-orange-50 hover:border-orange-300 transition">
                        <i class="fas fa-eye"></i>
                        {{ __('chef.hero.actions.public_profile') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="-mx-4 mb-8 overflow-hidden sm:mx-0">
            <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-4">
                @foreach ($statusMeta as $status => $meta)
                    <div class="min-w-[180px] flex-shrink-0 snap-center rounded-xl border border-gray-100 bg-white/95 p-4 shadow-sm backdrop-blur-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500">{{ $meta['label'] }}</p>
                            <span class="{{ $meta['bg'] }} {{ $meta['text'] }} inline-flex h-8 w-8 items-center justify-center rounded-xl text-base font-bold">
                                {{ $statusCounts[$status] ?? 0 }}
                            </span>
                        </div>
                        <p class="mt-2 text-[13px] leading-relaxed text-gray-500">{{ $meta['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <section class="mb-10">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-500 to-orange-500 text-white shadow-lg">
                <div class="absolute -top-24 -left-16 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -right-20 h-56 w-56 rounded-full bg-white/20 blur-3xl opacity-60"></div>
                <div class="relative flex flex-col gap-8 p-6 sm:p-8 lg:grid lg:grid-cols-[1.1fr,0.9fr]">
                    <div class="space-y-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">{{ __('chef.workshops.eyebrow') }}</p>
                            <h2 class="mt-2 text-2xl font-bold leading-snug md:text-3xl">{{ __('chef.workshops.title') }}</h2>
                            <p class="mt-3 text-sm text-white/80">
                                {{ __('chef.workshops.description', ['name' => $chefName]) }}
                                @if ($latestWorkshop)
                                    <span class="mt-2 block text-xs text-white/70">
                                        {{ __('chef.workshops.latest_workshop', ['title' => \Illuminate\Support\Str::limit($latestWorkshop->title, 40)]) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-4 sm:grid sm:gap-3 sm:grid-cols-2 sm:overflow-visible sm:pb-0 sm:snap-none xl:grid-cols-4">
                            <div class="min-w-[220px] flex-shrink-0 snap-center rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm sm:min-w-0">
                                <p class="text-xs font-semibold text-white/70">{{ __('chef.workshops.stats.total.title') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ $workshopStats['total'] ?? 0 }}</p>
                                <p class="text-xs text-white/70">{{ __('chef.workshops.stats.total.hint') }}</p>
                            </div>
                            <div class="min-w-[220px] flex-shrink-0 snap-center rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm sm:min-w-0">
                                <p class="text-xs font-semibold text-white/70">{{ __('chef.workshops.stats.active.title') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ $workshopStats['active'] ?? 0 }}</p>
                                <p class="text-xs text-white/70">{{ __('chef.workshops.stats.active.hint') }}</p>
                            </div>
                            <div class="min-w-[220px] flex-shrink-0 snap-center rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm sm:min-w-0">
                                <p class="text-xs font-semibold text-white/70">{{ __('chef.workshops.stats.online.title') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ $workshopStats['online'] ?? 0 }}</p>
                                <p class="text-xs text-white/70">{{ __('chef.workshops.stats.online.hint') }}</p>
                            </div>
                            <div class="min-w-[220px] flex-shrink-0 snap-center rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm sm:min-w-0">
                                <p class="text-xs font-semibold text-white/70">{{ __('chef.workshops.stats.upcoming.title') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ $workshopStats['upcoming'] ?? 0 }}</p>
                                <p class="text-xs text-white/70">{{ __('chef.workshops.stats.upcoming.hint') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('chef.workshops.create') }}"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-indigo-600 shadow hover:bg-indigo-50 sm:w-auto sm:justify-start">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                {{ __('chef.workshops.buttons.create') }}
                            </a>
                            <a href="{{ route('chef.workshops.index') }}"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/30 px-5 py-3 text-sm font-semibold text-white hover:border-white/60 sm:w-auto sm:justify-start">
                                <i class="fas fa-chalkboard-teacher"></i>
                                {{ __('chef.workshops.buttons.manage') }}
                            </a>
                        </div>
                    </div>
                    <div class="space-y-4 rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur sm:p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2 text-sm font-semibold text-white">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10">
                                    <i class="fas fa-calendar-star"></i>
                                </span>
                                {{ __('chef.workshops.next.heading') }}
                            </div>
                            <span class="text-xs text-white/70 sm:text-right">{{ __('chef.workshops.next.limit', ['count' => 3]) }}</span>
                        </div>
                        @php
                            $nextHostWorkshop = $upcomingWorkshops->first();
                        @endphp
                        @if ($nextHostWorkshop)
                            @php
                                $nextStart = optional($nextHostWorkshop->start_date)?->locale($locale)->translatedFormat($dateTimeFormat);
                            @endphp
                            <div class="rounded-2xl border border-white/25 bg-white/95 p-4 text-slate-900 shadow-sm ring-1 ring-white/20">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500">{{ __('chef.workshops.next.quick_join') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ \Illuminate\Support\Str::limit($nextHostWorkshop->title, 50) }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            <i class="fas fa-clock ml-1"></i>
                                            {{ $nextStart ?? __('chef.workshops.next.fallback_time') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('chef.workshops.join', $nextHostWorkshop) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow hover:from-indigo-600 hover:to-indigo-700">
                                        <i class="fas fa-video"></i>
                                        {{ __('chef.workshops.next.open_room') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        @forelse ($upcomingWorkshops as $workshop)
                            @php
                                $confirmed = (int) ($workshop->confirmed_bookings ?? 0);
                                $capacity = (int) ($workshop->max_participants ?? 0);
                                $fillPercent = $capacity > 0 ? (int) min(100, round(($confirmed / $capacity) * 100)) : 0;
                                $isLive = !is_null($workshop->meeting_started_at);
                                $hostStatusLabel = $isLive
                                    ? __('chef.workshops.host_status.live')
                                    : ($workshop->is_online
                                        ? __('chef.workshops.host_status.online_upcoming')
                                        : __('chef.workshops.host_status.onsite'));
                                $participantStatusLabel = $workshop->is_online
                                    ? ($isLive
                                        ? __('chef.workshops.participant_status.online_live')
                                        : __('chef.workshops.participant_status.online_waiting'))
                                    : __('chef.workshops.participant_status.onsite');
                                $participantHint = $workshop->is_online
                                    ? ($isLive
                                        ? __('chef.workshops.participant_hints.online_live')
                                        : __('chef.workshops.participant_hints.online_waiting'))
                                    : __('chef.workshops.participant_hints.onsite');
                                $startDisplay = optional($workshop->start_date)?->locale($locale)->translatedFormat($dateTimeFormat);
                                $participantsLabel = trans_choice('chef.workshops.items.participants_count', $confirmed, ['count' => $confirmed]);
                                $capacityLabel = $capacity > 0
                                    ? trans_choice('chef.workshops.items.capacity_count', $capacity, ['count' => $capacity])
                                    : __('chef.workshops.items.capacity_unlimited');
                            @endphp
                            <div class="rounded-2xl bg-white/90 p-4 text-slate-800 shadow-sm transition hover:shadow-md">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold text-slate-500">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 {{ $workshop->is_online ? 'bg-indigo-100 text-indigo-600' : 'bg-orange-100 text-orange-600' }}">
                                        <i class="fas {{ $workshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }}"></i>
                                        {{ $workshop->is_online ? __('chef.workshops.labels.online') : __('chef.workshops.labels.onsite') }}
                                    </span>
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <i class="fas fa-clock"></i>
                                        {{ $startDisplay ?? __('chef.workshops.next.fallback_time') }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-col gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">{{ $workshop->title }}</h3>
                                        <p class="mt-2 text-sm text-slate-500 line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit($workshop->description, 110) }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <div class="rounded-2xl border border-indigo-100 bg-white px-4 py-3 shadow-sm">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-500">{{ __('chef.workshops.sections.host') }}</p>
                                                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $hostStatusLabel }}</p>
                                                    @if ($isLive && $workshop->meeting_started_at)
                                                        <p class="text-xs text-indigo-500">
                                                            {{ __('chef.workshops.host_status.live_since', ['time' => $workshop->meeting_started_at?->locale($locale)->diffForHumans(null, true)]) }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <a href="{{ route('chef.workshops.join', $workshop) }}"
                                                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow hover:from-indigo-600 hover:to-indigo-700">
                                                    <i class="fas fa-door-open"></i>
                                                    {{ __('chef.workshops.next.open_room') }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">{{ __('chef.workshops.sections.participants') }}</p>
                                                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $participantStatusLabel }}</p>
                                                    <p class="text-xs text-slate-500">{{ $participantHint }}</p>
                                                </div>
                                                @if ($workshop->is_online && $workshop->meeting_link && $canViewRawMeetingLink)
                                                    <a href="{{ $workshop->meeting_link }}"
                                                       target="_blank"
                                                       rel="noopener"
                                                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-slate-700">
                                                        <i class="fas fa-link"></i>
                                                        {{ __('chef.workshops.items.participant_link') }}
                                                    </a>
                                                @elseif ($workshop->is_online)
                                                    <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 px-4 py-2 text-xs font-semibold text-slate-500">
                                                        <i class="fas fa-lock"></i>
                                                        {{ __('chef.workshops.items.participant_link_restricted') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($workshop->is_online && $workshop->meeting_link && $canViewRawMeetingLink)
                                                <p class="mt-2 break-all text-xs text-slate-400 ltr:text-left rtl:text-right">
                                                    {{ $workshop->meeting_link }}
                                                </p>
                                            @endif
                                            @unless ($workshop->is_online)
                                                <p class="mt-2 text-xs text-slate-500">
                                                    {{ $workshop->location ? __('chef.workshops.items.location_value', ['location' => $workshop->location]) : __('chef.workshops.items.location_missing') }}
                                                </p>
                                            @endunless
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                                            <span><i class="fas fa-users text-slate-400"></i> {{ $participantsLabel }}</span>
                                            <span>{{ $capacityLabel }}</span>
                                        </div>
                                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-orange-600 transition-all duration-300" style="width: {{ $fillPercent }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('chef.workshops.edit', $workshop) }}"
                                       class="inline-flex w-full items-center justify-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 sm:w-auto sm:justify-start">
                                        <i class="fas fa-pen"></i>
                                        {{ __('chef.workshops.items.edit_details') }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-white/40 px-6 py-10 text-center text-white/80">
                                <i class="fas fa-sparkles text-3xl"></i>
                                <p class="text-sm">{{ __('chef.workshops.items.no_upcoming') }}</p>
                                <a href="{{ route('chef.workshops.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/90 px-4 py-2 text-sm font-semibold text-indigo-600 shadow">
                                    <i class="fas fa-plus"></i>
                                    {{ __('chef.workshops.items.plan_next') }}
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        @php
            $linkPage = auth()->user()?->ensureLinkPage();
            $publicLinkUrl = $linkPage ? route('links.chef', $linkPage) : route('links');
        @endphp

        <div class="mb-10">
            <div class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden flex flex-col md:flex-row">
                <div class="flex-1 px-6 py-6 md:px-8 md:py-7">
                    <div class="flex items-center gap-3 mb-4 text-orange-500">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-100">
                            <i class="fas fa-link text-lg"></i>
                        </span>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('chef.link_page.title') }}</h2>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">
                        {{ __('chef.link_page.description') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-orange-600 font-medium">
                            <i class="fas fa-palette"></i>
                            {{ __('chef.link_page.features.customize') }}
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-orange-600 font-medium">
                            <i class="fas fa-bolt"></i>
                            {{ __('chef.link_page.features.instant_updates') }}
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-orange-600 font-medium">
                            <i class="fas fa-share-alt"></i>
                            {{ __('chef.link_page.features.shareable') }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-3 bg-gradient-to-bl from-orange-50 to-orange-100 px-6 py-6 md:px-8 md:py-7">
                    <a href="{{ route('chef.links.edit') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition">
                        <i class="fas fa-pen"></i>
                        {{ __('chef.link_page.actions.manage') }}
                    </a>
                    <a href="{{ $publicLinkUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-orange-600 font-semibold hover:bg-orange-50 transition">
                        <i class="fas fa-external-link-alt"></i>
                        {{ __('chef.link_page.actions.view') }}
                    </a>
                    <div class="text-xs text-gray-500 break-all max-w-sm ltr:text-left rtl:text-right">
                        {{ $publicLinkUrl }}
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            @if ($recipes->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <div class="mb-4 flex justify-center">
                        <div class="h-20 w-20 rounded-full bg-orange-50 flex items-center justify-center text-orange-400">
                            <i class="fas fa-utensils text-2xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('chef.empty_state.title') }}</h2>
                    <p class="text-sm text-gray-500 mb-6">{{ __('chef.empty_state.description') }}</p>
                    @if ($canManageRecipes)
                        <a href="{{ route('chef.recipes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white font-semibold hover:from-orange-600 hover:to-orange-700 transition">
                            <i class="fas fa-plus"></i>
                            {{ __('chef.empty_state.cta') }}
                        </a>
                    @else
                        <p class="text-sm font-semibold text-gray-600">إضافة الوصفات متاحة للإدمن فقط.</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('chef.table.headers.recipe') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('chef.table.headers.status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('chef.table.headers.visibility') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('chef.table.headers.updated_at') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('chef.table.headers.category') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 w-56">{{ __('chef.table.headers.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($recipes as $recipe)
                                <tr class="hover:bg-orange-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-14 w-14 overflow-hidden rounded-xl border border-gray-100 bg-gray-100">
                                                @if ($recipe->image)
                                                    <img src="{{ Storage::disk('public')->url($recipe->image) }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover" loading="lazy">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                                                        <i class="fas fa-utensils"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $recipe->title }}</p>
                                                <p class="text-sm text-gray-500 line-clamp-2">{{ \Illuminate\Support\Str::limit($recipe->description, 80) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $meta = $statusMeta[$recipe->status] ?? $statusMeta[Recipe::STATUS_DRAFT];
                                        @endphp
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium {{ $meta['bg'] }} {{ $meta['text'] }}">
                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                        @if ($recipe->status === Recipe::STATUS_APPROVED && $recipe->approved_at)
                                            <p class="mt-1 text-xs text-emerald-600">
                                                {{ __('chef.table.approved_on', ['date' => $recipe->approved_at->locale($locale)->translatedFormat($dateFormat)]) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $visibilityInfo = $visibilityMeta[$recipe->visibility] ?? $visibilityMeta[Recipe::VISIBILITY_PUBLIC];
                                        @endphp
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium {{ $visibilityInfo['bg'] }} {{ $visibilityInfo['text'] }}">
                                            <i class="fas {{ $visibilityInfo['icon'] }}"></i>
                                            {{ $visibilityInfo['label'] }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">{{ $visibilityInfo['hint'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $recipe->updated_at?->locale($locale)->translatedFormat($tableDateTimeFormat) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $recipe->category->name ?? __('chef.table.no_category') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('chef.recipes.edit', $recipe) }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">
                                                <i class="fas fa-pen"></i>
                                                {{ __('chef.table.actions.edit') }}
                                            </a>

                                            @if (in_array($recipe->status, [Recipe::STATUS_DRAFT, Recipe::STATUS_REJECTED], true))
                                                <form method="POST" action="{{ route('chef.recipes.submit', $recipe) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-orange-200 px-3 py-1.5 text-sm text-orange-600 hover:bg-orange-50">
                                                        <i class="fas fa-paper-plane"></i>
                                                        {{ __('chef.table.actions.submit_for_review') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if (in_array($recipe->status, [Recipe::STATUS_DRAFT, Recipe::STATUS_REJECTED], true))
                                                <form method="POST" action="{{ route('chef.recipes.destroy', $recipe) }}" onsubmit="return confirm('{{ __('chef.table.actions.delete_confirm') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-red-200 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                                                        <i class="fas fa-trash"></i>
                                                        {{ __('chef.table.actions.delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                    {{ $recipes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


