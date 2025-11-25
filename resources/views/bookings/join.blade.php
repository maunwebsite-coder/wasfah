@extends('layouts.app')

@section('title', __('bookings.join.meta_title', ['workshop' => $workshop->title]))

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #eaf3f6 0%, #c8ede3 33%, #f1f5f9 100%);
        min-height: 100vh;
    }

    footer {
        display: none !important;
    }

    .booking-shell {
        max-width: 960px;
        margin: 0 auto;
        padding: 3rem 1.25rem 4rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .booking-card, .tips-card {
        border-radius: 1.5rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
        box-shadow: 0 30px 80px -60px rgba(15, 23, 42, 0.4);
        padding: 2.25rem;
    }

    .tips-card {
        background: linear-gradient(120deg, rgba(252, 211, 77, 0.18), rgba(248, 250, 252, 0.85));
        border-color: rgba(251, 146, 60, 0.25);
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.85rem 1.8rem;
        border-radius: 999px;
        font-weight: 600;
        transition: transform 150ms ease, box-shadow 150ms ease;
    }

    .action-btn.primary {
        background: linear-gradient(120deg, #0f4c73, #f43f5e);
        color: #fff;
        box-shadow: 0 18px 40px rgba(15, 76, 115, 0.35);
    }

    .action-btn.secondary {
        border: 1px solid rgba(15, 23, 42, 0.15);
        color: #0f172a;
    }

    .action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.ready {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }

    .status-badge.pending {
        background: rgba(251, 191, 36, 0.15);
        color: #92400e;
    }

    .status-badge.locked {
        background: rgba(239, 68, 68, 0.15);
        color: #b91c1c;
    }
</style>
@endpush

@section('content')
<div class="booking-shell">
    <div class="booking-card space-y-6">
        <div class="space-y-3">
            <p class="uppercase tracking-[0.4em] text-xs text-orange-500/70">{{ __('bookings.join.header.label') }}</p>
            <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">{{ $workshop->title }}</h1>
            <p class="text-slate-600 text-sm leading-relaxed max-w-3xl">
                {{ __('bookings.join.header.description', ['workshop' => $workshop->title]) }}
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.details.date_time') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">
                    {{ optional($workshop->start_date)->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') ?? __('bookings.join.details.soon') }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.details.host_name') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">
                    {{ $hostName ?? $workshop->instructor ?? __('bookings.join.details.default_host') }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.details.meeting_status') }}</p>
                <p class="mt-2">
                    <span id="participantStatusBadge" class="status-badge {{ $meetingLocked ? 'locked' : ($meetingStarted ? 'ready' : 'pending') }}">
                        @if ($meetingLocked)
                            <i class="fas fa-lock"></i> {{ __('bookings.join.status.badges.locked') }}
                        @elseif ($meetingStarted)
                            <i class="fas fa-circle-check"></i> {{ __('bookings.join.status.badges.ready') }}
                        @else
                            <i class="fas fa-clock"></i> {{ __('bookings.join.status.badges.pending') }}
                        @endif
                    </span>
                </p>
            </div>
        </div>

        <div class="space-y-2">
            <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.secure.label') }}</p>
            <div class="rounded-2xl bg-slate-900/5 border border-slate-100 p-4 text-sm text-slate-700">
                <span class="font-semibold text-slate-900 block">{{ __('bookings.join.secure.title') }}</span>
                <span>{{ __('bookings.join.secure.description') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" class="action-btn primary" id="joinMeetButton" data-launch="{{ $secureLaunchUrl }}" @if(!$meetingStarted || $meetingLocked) disabled @endif>
                <i class="fab fa-google"></i>
                {{ __('bookings.join.actions.join') }}
            </button>
            <button type="button" class="action-btn secondary" id="refreshMeetingStatus">
                <i class="fas fa-rotate-right"></i>
                {{ __('bookings.join.actions.refresh') }}
            </button>
        </div>

        <div id="statusMessage" class="rounded-2xl border p-4 text-sm {{ $meetingLocked ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
            @if ($meetingLocked)
                {{ __('bookings.join.status.messages.locked') }}
            @elseif (!$meetingStarted)
                {{ __('bookings.join.status.messages.pending') }}
            @else
                {{ __('bookings.join.status.messages.ready') }}
            @endif
        </div>
    </div>

    <div class="tips-card space-y-4">
        <h2 class="text-2xl font-semibold text-slate-900">{{ __('bookings.join.tips.title') }}</h2>
        <ul class="space-y-2 text-sm leading-relaxed text-slate-700">
            <li class="flex gap-3"><i class="fas fa-circle text-xs text-orange-500 mt-1.5"></i> {{ __('bookings.join.tips.items.signin') }}</li>
            <li class="flex gap-3"><i class="fas fa-circle text-xs text-orange-500 mt-1.5"></i> {{ __('bookings.join.tips.items.gear') }}</li>
            <li class="flex gap-3"><i class="fas fa-circle text-xs text-orange-500 mt-1.5"></i> {{ __('bookings.join.tips.items.focus') }}</li>
            <li class="flex gap-3"><i class="fas fa-circle text-xs text-orange-500 mt-1.5"></i> {{ __('bookings.join.tips.items.support') }}</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const joinButton = document.getElementById('joinMeetButton');
        const refreshButton = document.getElementById('refreshMeetingStatus');
        const statusBadge = document.getElementById('participantStatusBadge');
        const statusMessage = document.getElementById('statusMessage');
        const launchUrl = joinButton?.dataset.launch;
        const statusUrl = @json($secureStatusUrl);
        const translations = {
            badges: {
                locked: @json(__('bookings.join.status.badges.locked')),
                ready: @json(__('bookings.join.status.badges.ready')),
                pending: @json(__('bookings.join.status.badges.pending')),
            },
            messages: {
                locked: @json(__('bookings.join.status.messages.locked')),
                ready: @json(__('bookings.join.status.messages.ready')),
                pending: @json(__('bookings.join.status.messages.pending')),
            },
        };

        let meetingStarted = @json($meetingStarted);
        let meetingLocked = @json($meetingLocked);

        const updateUI = () => {
            if (!statusBadge || !statusMessage) {
                return;
            }

            statusBadge.classList.remove('ready', 'pending', 'locked');

            if (meetingLocked) {
                statusBadge.classList.add('locked');
                statusBadge.innerHTML = '<i class="fas fa-lock"></i> ' + translations.badges.locked;
                statusMessage.className = 'rounded-2xl border border-rose-200 bg-rose-50 text-sm text-rose-700 p-4';
                statusMessage.textContent = translations.messages.locked;
                joinButton.disabled = true;
                return;
            }

            if (meetingStarted) {
                statusBadge.classList.add('ready');
                statusBadge.innerHTML = '<i class="fas fa-circle-check"></i> ' + translations.badges.ready;
                statusMessage.className = 'rounded-2xl border border-emerald-200 bg-emerald-50 text-sm text-emerald-700 p-4';
                statusMessage.textContent = translations.messages.ready;
                joinButton.disabled = false;
            } else {
                statusBadge.classList.add('pending');
                statusBadge.innerHTML = '<i class="fas fa-clock"></i> ' + translations.badges.pending;
                statusMessage.className = 'rounded-2xl border border-amber-200 bg-amber-50 text-sm text-amber-700 p-4';
                statusMessage.textContent = translations.messages.pending;
                joinButton.disabled = true;
            }
        };

        const pollStatus = async () => {
            if (!statusUrl || meetingLocked) {
                return;
            }

            try {
                const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                meetingStarted = Boolean(data.meeting_started);
                meetingLocked = Boolean(data.meeting_locked);
                updateUI();
            } catch (error) {
                console.warn('Failed to poll meeting status', error);
            }
        };

        joinButton?.addEventListener('click', () => {
            if (!launchUrl || joinButton.disabled) {
                return;
            }
            window.open(launchUrl, '_blank', 'noopener,noreferrer');
        });

        refreshButton?.addEventListener('click', () => {
            pollStatus();
        });

        updateUI();

        if (statusUrl && !meetingStarted && !meetingLocked) {
            setInterval(pollStatus, 7000);
        }
    });
</script>
@endpush





