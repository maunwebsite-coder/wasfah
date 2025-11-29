@extends('layouts.app')

@section('title', __('chef.dashboard.workshops.host_room.meta_title', ['title' => $workshop->title]))

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f4f6ff 0%, #e3ecff 35%, #f7f4ea 65%, #f1f5f9 100%);
        min-height: 100vh;
    }

    footer {
        display: none !important;
    }

    .host-shell {
        max-width: 1100px;
        margin: 0 auto;
        padding: 3.5rem 1.5rem 4rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .host-card {
        border-radius: 1.75rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
        box-shadow: 0 30px 80px -60px rgba(15, 23, 42, 0.55);
        color: #0f172a;
        padding: 2.5rem;
    }

    .participants-card {
        border-radius: 1.5rem;
        border: 1px solid rgba(8, 25, 255, 0.25);
        background: linear-gradient(120deg, rgba(8, 25, 255, 0.08), rgba(26, 184, 255, 0.18), #f4f6ff);
        box-shadow: 0 35px 80px -65px rgba(8, 25, 255, 0.55);
        padding: 2rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.85rem 1.8rem;
        border-radius: 999px;
        font-weight: 600;
        transition: transform 150ms ease, box-shadow 150ms ease;
    }

    .action-btn.primary {
        background: linear-gradient(120deg, #0819ff, #16c3ff);
        color: #fff;
        box-shadow: 0 18px 35px rgba(8, 25, 255, 0.35);
    }

    .action-btn.secondary {
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: #fff;
        color: #0f172a;
        box-shadow: 0 20px 45px -30px rgba(15, 23, 42, 0.4);
    }

    .action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .action-btn:not(:disabled):hover {
        transform: translateY(-2px);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 1.1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .status-badge.ready {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        border-color: rgba(34, 197, 94, 0.35);
    }

    .status-badge.pending {
        background: rgba(251, 191, 36, 0.18);
        color: #92400e;
        border-color: rgba(251, 191, 36, 0.35);
    }

    .status-badge.locked {
        background: rgba(239, 68, 68, 0.15);
        color: #b91c1c;
        border-color: rgba(239, 68, 68, 0.35);
    }
</style>
@endpush

@php
    $hostTimezoneName = $workshop->host_timezone
        ?? optional($workshop->start_date)?->getTimezone()?->getName()
        ?? config('app.timezone', 'UTC');
    $hostTimezonePretty = $hostTimezoneName ? str_replace('_', ' ', $hostTimezoneName) : null;
    $hostOffset = $workshop->start_date
        ? sprintf('GMT%s', $workshop->start_date->format('P'))
        : 'GMT+00:00';
    $hostScheduleDisplay = $workshop->start_date
        ? __('workshops.details.timezones.host_timezone_template', [
            'label' => __('workshops.details.timezones.host_label'),
            'date' => optional($workshop->start_date)->format('m/d/Y g:i A'),
            'offset' => $hostOffset,
            'timezone' => $hostTimezonePretty ?? 'UTC',
        ])
        : null;
@endphp

@section('content')
@php
    $locale = app()->getLocale();
    $dateFormat = __('chef.formats.date_time');
    $startDateFormatted = $workshop->start_date
        ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateFormat)
        : null;
    $startDateIso = optional($workshop->start_date)->toIso8601String();
    $confirmedCount = $workshop->confirmed_bookings_count ?? $workshop->bookings_count ?? 0;
    $recentParticipantsCount = is_countable($recentParticipants) ? count($recentParticipants) : 0;
@endphp
<div class="host-shell">
    <div class="host-card space-y-6">
        <div class="space-y-3">
            <p class="uppercase tracking-[0.45em] text-xs text-orange-500/80">{{ __('chef.dashboard.workshops.host_room.eyebrow') }}</p>
            <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">{{ $workshop->title }}</h1>
            <p class="text-slate-600 text-sm leading-relaxed max-w-3xl">
                {{ __('chef.dashboard.workshops.host_room.description') }}
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-orange-100 bg-white/90 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-slate-500">{{ __('chef.dashboard.workshops.host_room.stats.schedule') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">
                    {{ $startDateFormatted ?? __('chef.dashboard.workshops.host_room.no_schedule') }}
                    @if($hostScheduleDisplay)
                        <span class="block text-sm font-normal text-slate-600 mt-1">{{ $hostScheduleDisplay }}</span>
                    @endif
                    @if($startDateIso)
                        <span
                            class="block text-sm font-normal text-slate-500 mt-1"
                            data-local-time
                            data-source-time="{{ $startDateIso }}"
                            data-label="{{ __('workshops.details.timezones.viewer_label') }}"
                            data-template="{{ __('workshops.details.timezones.viewer_timezone_template') }}"
                            data-fallback-timezone="{{ __('workshops.details.timezones.viewer_timezone_fallback') }}"
                            data-placeholder="{{ __('workshops.details.timezones.viewer_placeholder') }}"
                            data-locale="{{ app()->getLocale() }}"
                            data-format="datetime-full"
                        >{{ __('workshops.details.timezones.viewer_placeholder') }}</span>
                    @endif
                </p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/90 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-slate-500">{{ __('chef.dashboard.workshops.host_room.stats.attendance') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">
                    {{ trans_choice('chef.dashboard.workshops.host_room.attendance_value', $confirmedCount, ['count' => number_format($confirmedCount)]) }}
                </p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/90 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-slate-500">{{ __('chef.dashboard.workshops.host_room.stats.status') }}</p>
                <p class="mt-2">
                    <span id="hostMeetingStatus" class="status-badge {{ $workshop->meeting_locked_at ? 'locked' : ($workshop->meeting_started_at ? 'ready' : 'pending') }}">
                        @if ($workshop->meeting_locked_at)
                            <i class="fas fa-lock"></i> {{ __('chef.dashboard.workshops.host_room.status_badges.locked') }}
                        @elseif ($workshop->meeting_started_at)
                            <i class="fas fa-circle-check"></i> {{ __('chef.dashboard.workshops.host_room.status_badges.live') }}
                        @else
                            <i class="fas fa-clock"></i> {{ __('chef.dashboard.workshops.host_room.status_badges.pending') }}
                        @endif
                    </span>
                </p>
            </div>
        </div>

        <div>
            <p class="text-xs uppercase tracking-widest text-slate-500">{{ __('chef.dashboard.workshops.host_room.meeting.secure_label') }}</p>
            @if($hostRedirectUrl)
                <div class="mt-2 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm text-emerald-900 shadow-sm">
                    <p>{{ __('chef.dashboard.workshops.host_room.meeting.secure_help') }}</p>
                </div>
                <div class="mt-2 rounded-2xl border border-slate-100 bg-white/90 p-4 text-xs text-slate-900 break-all shadow-sm">
                    {{ $hostRedirectUrl }}
                </div>
            @else
                <div class="mt-2 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                    {{ __('chef.dashboard.workshops.host_room.meeting.secure_missing') }}
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ $hostRedirectUrl ?? '#' }}"
               target="_blank"
               rel="noopener noreferrer"
               class="action-btn primary {{ $hostRedirectUrl ? '' : 'opacity-60 cursor-not-allowed' }}"
               id="openMeetAsHost"
               @unless($hostRedirectUrl) aria-disabled="true" @endunless>
                <i class="fab fa-google"></i>
                {{ __('chef.dashboard.workshops.host_room.meeting.open') }}
            </a>
            <button type="button" class="action-btn secondary" id="refreshHostStatus">
                <i class="fas fa-rotate-right"></i>
                {{ __('chef.dashboard.workshops.host_room.meeting.refresh') }}
            </button>
        </div>

    </div>

    <div class="participants-card space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-widest text-orange-500/80">{{ __('chef.dashboard.workshops.host_room.participants.eyebrow') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-1">{{ __('chef.dashboard.workshops.host_room.participants.title') }}</h2>
            </div>
            <span class="rounded-full border border-orange-100 bg-white px-4 py-1.5 text-sm font-semibold text-slate-900 shadow-sm">
                {{ __('chef.dashboard.workshops.host_room.participants.summary', [
                    'current' => number_format($recentParticipantsCount),
                    'total' => number_format($confirmedCount),
                ]) }}
            </span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($recentParticipants as $participant)
                <div class="rounded-2xl border border-orange-50 bg-white/90 p-4 shadow-sm">
                    <p class="text-base font-semibold text-slate-900">{{ $participant->user?->name ?? __('chef.dashboard.workshops.host_room.participants.fallback_name') }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $participant->user?->email ?? __('chef.dashboard.workshops.host_room.participants.fallback_email') }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">
                        @php
                            $lastUpdate = optional($participant->updated_at, fn ($value) => $value->copy()->locale($locale)->diffForHumans());
                        @endphp
                        {{ __('chef.dashboard.workshops.host_room.participants.last_update', ['time' => $lastUpdate ?? '--']) }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-600">{{ __('chef.dashboard.workshops.host_room.participants.empty') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const refreshButton = document.getElementById('refreshHostStatus');
        const statusBadge = document.getElementById('hostMeetingStatus');
        const startUrl = @json(route('chef.workshops.start', $workshop));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const markMeetingStarted = () => {
            if (!startUrl || !csrfToken) {
                return;
            }

            const payload = new URLSearchParams();
            payload.append('confirm_host', '1');

            fetch(startUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload.toString(),
                credentials: 'same-origin',
            }).catch(() => {
                // Non-blocking
            });

            if (window.Livewire?.dispatch) {
                window.Livewire.dispatch('chef-start-meeting', { confirmHost: true });
            }
        };

        const updateStatusBadge = (state, label) => {
            if (!statusBadge) {
                return;
            }
            statusBadge.classList.remove('ready', 'pending', 'locked');
            statusBadge.classList.add(state);
            statusBadge.innerHTML = label;
        };

        refreshButton?.addEventListener('click', () => {
            window.location.reload();
        });

        markMeetingStarted();
    });
</script>
@endpush







