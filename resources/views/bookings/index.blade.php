@extends('layouts.app')

@section('title', __('bookings.history.title'))

@php
    use Carbon\Carbon;

    $statusLabels = [
        'pending' => __('bookings.status.pending'),
        'confirmed' => __('bookings.status.confirmed'),
        'cancelled' => __('bookings.status.cancelled'),
    ];

    $statusStyles = [
        'pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
        'confirmed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        'cancelled' => 'bg-rose-50 text-rose-700 border border-rose-100',
    ];

    $statCards = [
        [
            'label' => __('bookings.stats.total'),
            'value' => number_format($bookings->total()),
            'container' => 'border border-slate-100 bg-slate-50',
            'labelClass' => 'text-slate-500',
            'valueClass' => 'text-slate-900',
        ],
        [
            'label' => __('bookings.status.confirmed'),
            'value' => number_format($statusCounts['confirmed'] ?? 0),
            'container' => 'border border-emerald-100 bg-emerald-50',
            'labelClass' => 'text-emerald-600',
            'valueClass' => 'text-emerald-800',
        ],
        [
            'label' => __('bookings.status.pending'),
            'value' => number_format($statusCounts['pending'] ?? 0),
            'container' => 'border border-amber-100 bg-amber-50',
            'labelClass' => 'text-amber-600',
            'valueClass' => 'text-amber-800',
        ],
    ];
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            <div class="rounded-3xl border border-orange-100 bg-white/80 p-6 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">{{ __('bookings.history.title') }}</p>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">{{ __('navbar.account_menu.links.bookings') ?? __('bookings.history.title') }}</h1>
                        <p class="mt-2 text-sm text-gray-600">{{ __('bookings.history.description') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if (!empty($canManageRecordings))
                            <a href="{{ route('bookings.recordings') }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 hover:border-indigo-300 hover:text-indigo-700">
                                <i class="fa-solid fa-video"></i>
                                {{ __('bookings.recordings.manage_title') }}
                            </a>
                        @endif
                        <a href="{{ route('workshops') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                            <i class="fas fa-search text-xs"></i>
                            {{ __('bookings.hero.explore') }}
                        </a>
                    </div>
                </div>

                <div class="mt-6 -mx-4 sm:hidden">
                    <div class="flex gap-3 overflow-x-auto px-4 pb-2 snap-x snap-mandatory">
                        @foreach ($statCards as $card)
                            <div class="w-56 flex-shrink-0 snap-center">
                                <div class="h-full rounded-2xl px-4 py-3 shadow-inner {{ $card['container'] }}">
                                    <p class="text-xs font-semibold uppercase tracking-wide {{ $card['labelClass'] }}">{{ $card['label'] }}</p>
                                    <p class="mt-1 text-2xl font-bold {{ $card['valueClass'] }}">{{ $card['value'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 hidden gap-3 sm:grid sm:grid-cols-3">
                    @foreach ($statCards as $card)
                        <div class="rounded-2xl px-4 py-3 shadow-inner {{ $card['container'] }}">
                            <p class="text-xs font-semibold uppercase tracking-wide {{ $card['labelClass'] }}">{{ $card['label'] }}</p>
                            <p class="mt-1 text-2xl font-bold {{ $card['valueClass'] }}">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($showEmptyState)
                <div class="mt-8 rounded-3xl border border-dashed border-orange-200 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-slate-900">{{ __('bookings.history.empty.message') }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ __('bookings.history.description') }}</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('workshops') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                            <i class="fas fa-search text-xs"></i>
                            {{ __('bookings.history.empty.cta') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="mt-8 space-y-4">
                    @foreach ($bookings as $booking)
                        @php
                            $workshop = $booking->workshop;
                            $start = optional($workshop?->start_date);
                            $status = $booking->status;
                            $statusClass = $statusStyles[$status] ?? 'bg-slate-100 text-slate-700 border border-slate-200';
                            $formatLabel = $workshop?->is_online ? __('bookings.history.labels.online') : __('bookings.history.labels.in_person');
                            $title = $workshop?->title ?: __('bookings.history.labels.untitled');
                            $recordingUrl = $workshop?->recording_resolved_url ?? $workshop?->recording_url;
                            $host = $workshop?->chef;
                            $hostName = $host?->name;
                            $hostProfileUrl = $host ? route('chefs.show', ['chef' => $host->id]) : null;
                            $canOpenRecording = $status === 'confirmed' && !empty($recordingUrl);
                        @endphp
                        <article class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold {{ $statusClass }}">
                                            <i class="fa-solid fa-circle-info text-[10px]"></i>
                                            {{ $statusLabels[$status] ?? $status }}
                                        </span>
                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700">
                                            <i class="fa-solid fa-location-dot text-amber-500"></i>
                                            {{ $formatLabel }}
                                        </span>
                                        @if ($hostName && $hostProfileUrl)
                                            <a href="{{ $hostProfileUrl }}"
                                               class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[11px] font-semibold text-indigo-700 hover:border-indigo-200 hover:bg-indigo-100">
                                                <i class="fa-solid fa-user"></i>
                                                {{ __('bookings.history.labels.hosted_by', ['name' => $hostName]) }}
                                            </a>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900">{{ $title }}</h3>
                                    @if ($start)
                                        <p class="text-sm text-slate-600">
                                            <i class="fa-regular fa-clock ml-2 text-orange-500"></i>
                                            {{ $start->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') }}
                                        </p>
                                    @endif
                                    @if (!empty($workshop?->location) && ! $workshop->is_online)
                                        <p class="text-sm text-slate-500">
                                            <i class="fa-solid fa-map-pin ml-2 text-orange-500"></i>
                                            {{ $workshop->location }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-2 md:items-end">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">
                                        <i class="fa-solid fa-arrow-left"></i>
                                        {{ __('bookings.history.actions.details') }}
                                    </a>
                                    @if ($canOpenRecording)
                                        <a href="{{ $recordingUrl }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:from-indigo-600 hover:to-indigo-700">
                                            <i class="fa-solid fa-video"></i>
                                            {{ __('bookings.history.actions.enter_room') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
