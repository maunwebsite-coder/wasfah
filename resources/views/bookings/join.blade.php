@extends('layouts.app')

@section('title', __('bookings.join.meta_title', ['workshop' => $workshop->title]))

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f4f6ff 0%, #e3ecff 33%, #f1f5f9 100%);
        min-height: 100vh;
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
        background: linear-gradient(120deg, #0819ff, #f43f5e);
        color: #fff;
        box-shadow: 0 18px 40px rgba(8, 25, 255, 0.35);
    }

    .action-btn.review {
        position: relative;
        background: linear-gradient(135deg, #f97316, #0ea5e9, #f43f5e, #f97316);
        background-size: 240% 240%;
        color: #fff;
        box-shadow: 0 20px 50px rgba(244, 63, 94, 0.32);
        overflow: hidden;
        animation: gradientShift 6s ease infinite, floatPulse 3s ease-in-out infinite;
    }

    .action-btn.review::after {
        content: '';
        position: absolute;
        inset: 4px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        opacity: 0.9;
        mix-blend-mode: screen;
        animation: shimmer 2.6s linear infinite;
        pointer-events: none;
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

    .status-badge.grace {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .status-badge.locked {
        background: rgba(239, 68, 68, 0.15);
        color: #b91c1c;
    }

    .status-badge.expired {
        background: rgba(148, 163, 184, 0.2);
        color: #0f172a;
    }

    .highlight-pulse {
        animation: highlightPulse 1.2s ease;
    }

    #scrollToReviewButton {
        white-space: nowrap;
        padding: 0.65rem 1.25rem;
        font-size: 0.95rem;
        gap: 0.4rem;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes floatPulse {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }

    @keyframes shimmer {
        0% { opacity: 0; transform: translateX(-20%); }
        50% { opacity: 0.8; transform: translateX(10%); }
        100% { opacity: 0; transform: translateX(30%); }
    }

    @keyframes highlightPulse {
        0% { box-shadow: 0 30px 80px -60px rgba(15, 23, 42, 0.4), 0 0 0 0 rgba(14, 165, 233, 0); }
        50% { box-shadow: 0 20px 60px -50px rgba(14, 165, 233, 0.28), 0 0 0 10px rgba(244, 158, 11, 0.22); }
        100% { box-shadow: 0 30px 80px -60px rgba(15, 23, 42, 0.4), 0 0 0 0 rgba(14, 165, 233, 0); }
    }

    @media (max-width: 640px) {
        #scrollToReviewButton {
            padding: 0.55rem 0.95rem;
            font-size: 0.85rem;
            gap: 0.35rem;
        }
    }
</style>
@endpush

@section('content')
<div class="booking-shell">
    <div class="booking-card space-y-6">
        <div class="space-y-3">
            <p class="uppercase tracking-[0.4em] text-xs text-orange-500/70">{{ __('bookings.join.header.label') }}</p>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">{{ $workshop->title }}</h1>
                <button type="button" class="action-btn review" id="scrollToReviewButton">
                    <i class="fas fa-star-half-alt"></i>
                    {{ __('bookings.join.actions.rate') }}
                </button>
            </div>
            <p class="text-slate-600 text-sm leading-relaxed max-w-3xl">
                {{ __('bookings.join.header.description', ['workshop' => $workshop->title]) }}
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.details.date_time') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">
                    {{ optional($workshop->start_date)->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') ?? __('bookings.join.details.soon') }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('bookings.join.details.end_time') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">
                    {{ optional($workshop->end_date)->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') ?? __('bookings.join.details.soon') }}
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
                    <span id="participantStatusBadge" class="status-badge {{ $meetingExpired ? 'expired' : ($meetingLocked ? 'locked' : ($meetingReady ? ($graceJoinAllowed ? 'grace' : 'ready') : 'pending')) }}">
                        @if ($meetingExpired)
                            <i class="fas fa-hourglass-end"></i> {{ __('bookings.join.status.badges.expired') }}
                        @elseif ($meetingLocked)
                            <i class="fas fa-lock"></i> {{ __('bookings.join.status.badges.locked') }}
                        @elseif ($meetingReady)
                            <i class="fas fa-circle-check"></i>
                            {{ $graceJoinAllowed ? __('bookings.join.status.badges.grace') : __('bookings.join.status.badges.ready') }}
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
            <button type="button" class="action-btn primary" id="joinMeetButton" data-launch="{{ $secureLaunchUrl }}" @if(!$meetingReady || $meetingLocked || $meetingExpired) disabled @endif>
                <i class="fab fa-google"></i>
                {{ __('bookings.join.actions.join') }}
            </button>
            <button type="button" class="action-btn secondary" id="refreshMeetingStatus">
                <i class="fas fa-rotate-right"></i>
                {{ __('bookings.join.actions.refresh') }}
            </button>
        </div>

        @php
            $statusClass = $meetingExpired
                ? 'border-slate-200 bg-slate-50 text-slate-700'
                : ($meetingLocked
                    ? 'border-rose-200 bg-rose-50 text-rose-700'
                    : ($meetingReady ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'));
            $statusText = $meetingExpired
                ? __('bookings.join.status.messages.expired')
                : ($meetingLocked
                    ? __('bookings.join.status.messages.locked')
                    : ($meetingReady
                        ? ($graceJoinAllowed
                            ? __('bookings.join.status.messages.grace', ['minutes' => $graceJoinMinutes])
                            : __('bookings.join.status.messages.ready'))
                        : __('bookings.join.status.messages.pending_grace', ['minutes' => $graceJoinMinutes])));
        @endphp
        <div id="statusMessage" class="rounded-2xl border p-4 text-sm {{ $statusClass }}">
            {{ $statusText }}
            @if (!$meetingLocked && !$meetingReady && !$meetingExpired && $graceJoinAtIso)
                <span id="graceCountdown" class="block text-xs mt-2 text-slate-600"></span>
            @endif
        </div>
    </div>

    <div id="reviewSection" class="booking-card space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="uppercase tracking-[0.4em] text-xs text-orange-500/70">{{ __('workshops.reviews.title') }}</p>
                <h2 class="text-xl font-semibold text-slate-900 mt-1">{{ __('workshops.reviews.rating_label') }}</h2>
                <p class="text-sm text-slate-600">{{ __('workshops.reviews.messages.wait_until_end') }}</p>
            </div>
            <i class="fas fa-star text-amber-400 text-2xl"></i>
        </div>

        @if (session('review_saved'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('review_saved') }}
            </div>
        @endif

        @php
            $selectedRating = (int) old('rating', optional($userReview)->rating ?? 5);
        @endphp
        <div id="reviewFormContainer" class="{{ $canReview ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('workshops.reviews.store', ['workshop' => $workshop->slug]) }}" class="space-y-3">
                @csrf
                <div>
                    <div class="flex flex-wrap gap-2">
                        @for($i = 5; $i >= 1; $i--)
                            @php $isChecked = $selectedRating === $i; @endphp
                            <label
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border {{ $isChecked ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50' }} px-2.5 py-1.5 text-sm font-semibold text-slate-800 hover:border-amber-300 hover:bg-amber-50 rating-option"
                                data-rating-option="{{ $i }}"
                            >
                                <input type="radio" name="rating" value="{{ $i }}" class="sr-only" @checked($isChecked)>
                                <span>{{ $i }}</span>
                                <i class="fas fa-star text-amber-400"></i>
                            </label>
                        @endfor
                    </div>
                    @error('rating')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="review-comment" class="block text-sm font-semibold text-slate-800">
                        {{ __('workshops.reviews.comment_label') }}
                        <span class="text-xs font-normal text-slate-500">({{ __('workshops.reviews.optional') }})</span>
                    </label>
                    <textarea
                        id="review-comment"
                        name="comment"
                        rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                        placeholder="{{ __('workshops.reviews.comment_placeholder') }}">{{ old('comment', optional($userReview)->comment) }}</textarea>
                    @error('comment')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="action-btn primary w-full justify-center">
                    <i class="fas fa-paper-plane"></i>
                    <span>{{ $userReview ? __('workshops.reviews.update') : __('workshops.reviews.submit') }}</span>
                </button>
            </form>
        </div>

        <div id="reviewLockedContainer" class="{{ $canReview ? 'hidden' : '' }}">
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-3 text-sm text-amber-800">
                <div class="flex items-center gap-2">
                    <i class="fas fa-hourglass-half"></i>
                    <span>{{ __('workshops.reviews.messages.wait_until_end') }}</span>
                </div>
                @if($reviewUnlockAtIso)
                    <p id="reviewCountdown" class="mt-2 text-xs text-slate-600"></p>
                @endif
            </div>
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
        const graceCountdown = document.getElementById('graceCountdown');
        const reviewCountdown = document.getElementById('reviewCountdown');
        const reviewFormContainer = document.getElementById('reviewFormContainer');
        const reviewLockedContainer = document.getElementById('reviewLockedContainer');
        const reviewSection = document.getElementById('reviewSection');
        const scrollToReviewButton = document.getElementById('scrollToReviewButton');
        const ratingOptions = Array.from(document.querySelectorAll('.rating-option'));
        const launchUrl = joinButton?.dataset.launch;
        const statusUrl = @json($secureStatusUrl);
        const translations = {
            badges: {
                expired: @json(__('bookings.join.status.badges.expired')),
                locked: @json(__('bookings.join.status.badges.locked')),
                ready: @json(__('bookings.join.status.badges.ready')),
                grace: @json(__('bookings.join.status.badges.grace')),
                pending: @json(__('bookings.join.status.badges.pending')),
            },
            messages: {
                expired: @json(__('bookings.join.status.messages.expired')),
                locked: @json(__('bookings.join.status.messages.locked')),
                ready: @json(__('bookings.join.status.messages.ready')),
                pending: @json(__('bookings.join.status.messages.pending_grace', ['minutes' => $graceJoinMinutes])),
                grace: @json(__('bookings.join.status.messages.grace', ['minutes' => $graceJoinMinutes])),
                grace_wait: @json(__('bookings.join.status.messages.grace_wait', ['minutes' => $graceJoinMinutes])),
            },
        };

        let meetingStarted = @json($meetingStarted);
        let meetingLocked = @json($meetingLocked);
        let graceJoinAllowed = @json($graceJoinAllowed);
        let joinUnlocked = @json($meetingReady);
        let graceJoinAt = @json($graceJoinAtIso);
        let meetingExpired = @json($meetingExpired);
        let meetingExpiresAt = @json($meetingExpiresAtIso);
        let reviewUnlockAt = @json($reviewUnlockAtIso);
        let expiryTimerId = null;
        const reviewWaitLabel = @json(__('workshops.reviews.messages.wait_until_end'));
        const reviewReadyLabel = @json(__('workshops.reviews.submit'));

        const scheduleExpiryEnforcement = () => {
            if (expiryTimerId) {
                clearTimeout(expiryTimerId);
                expiryTimerId = null;
            }

            if (!meetingExpiresAt || meetingExpired) {
                return;
            }

            const expiresAtMs = new Date(meetingExpiresAt).getTime();

            if (Number.isNaN(expiresAtMs)) {
                return;
            }

            const delay = expiresAtMs - Date.now();

            if (delay <= 0) {
                meetingExpired = true;
                updateUI();
                return;
            }

            expiryTimerId = setTimeout(() => {
                meetingExpired = true;
                updateUI();
            }, delay);
        };

        const formatCountdown = () => {
            if (!graceJoinAt) return '';
            const target = new Date(graceJoinAt).getTime();
            const diff = target - Date.now();
            if (diff <= 0) return '';
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        };

        const formatReviewCountdown = () => {
            if (!reviewUnlockAt) return '';
            const target = new Date(reviewUnlockAt).getTime();
            const diff = target - Date.now();
            if (diff <= 0) return '';
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        };

        const updateUI = () => {
            if (!statusBadge || !statusMessage) {
                return;
            }

            statusBadge.classList.remove('ready', 'pending', 'locked', 'grace', 'expired');

            if (refreshButton) {
                refreshButton.disabled = false;
            }

            if (meetingExpired) {
                statusBadge.classList.add('expired');
                statusBadge.innerHTML = '<i class="fas fa-hourglass-end"></i> ' + translations.badges.expired;
                statusMessage.className = 'rounded-2xl border border-slate-200 bg-slate-50 text-sm text-slate-700 p-4';
                statusMessage.textContent = translations.messages.expired;
                if (joinButton) {
                    joinButton.disabled = true;
                }
                if (graceCountdown) {
                    graceCountdown.textContent = '';
                }
                if (refreshButton) {
                    refreshButton.disabled = true;
                }
                return;
            }

            if (meetingLocked) {
                statusBadge.classList.add('locked');
                statusBadge.innerHTML = '<i class="fas fa-lock"></i> ' + translations.badges.locked;
                statusMessage.className = 'rounded-2xl border border-rose-200 bg-rose-50 text-sm text-rose-700 p-4';
                statusMessage.textContent = translations.messages.locked;
                if (joinButton) {
                    joinButton.disabled = true;
                }
                if (graceCountdown) {
                    graceCountdown.textContent = '';
                }
                return;
            }

            if (meetingStarted || graceJoinAllowed || joinUnlocked) {
                const isGrace = !meetingStarted && (graceJoinAllowed || joinUnlocked);
                statusBadge.classList.add(isGrace ? 'grace' : 'ready');
                statusBadge.innerHTML = '<i class="fas fa-circle-check"></i> ' + (isGrace ? translations.badges.grace : translations.badges.ready);
                statusMessage.className = 'rounded-2xl border border-emerald-200 bg-emerald-50 text-sm text-emerald-700 p-4';
                statusMessage.textContent = isGrace ? translations.messages.grace : translations.messages.ready;
                if (joinButton) {
                    joinButton.disabled = false;
                }
                if (graceCountdown) {
                    graceCountdown.textContent = '';
                }
            } else {
                statusBadge.classList.add('pending');
                statusBadge.innerHTML = '<i class="fas fa-clock"></i> ' + translations.badges.pending;
                statusMessage.className = 'rounded-2xl border border-amber-200 bg-amber-50 text-sm text-amber-700 p-4';
                statusMessage.textContent = translations.messages.pending;
                if (graceCountdown) {
                    const countdownText = formatCountdown();
                    graceCountdown.textContent = countdownText
                        ? translations.messages.grace_wait + ' • ' + countdownText
                        : translations.messages.grace_wait;
                }
                if (joinButton) {
                    joinButton.disabled = true;
                }
            }
        };

        const updateReviewCountdown = () => {
            if (!reviewCountdown || !reviewUnlockAt) {
                return;
            }
            const remaining = formatReviewCountdown();
            if (!remaining) {
                reviewCountdown.textContent = reviewReadyLabel;
                reviewCountdown.classList.add('text-emerald-700', 'font-semibold');
                reviewLockedContainer?.classList.add('hidden');
                reviewFormContainer?.classList.remove('hidden');
                return;
            }
            reviewCountdown.textContent = `${reviewWaitLabel} • ${remaining}`;
            reviewLockedContainer?.classList.remove('hidden');
            reviewFormContainer?.classList.add('hidden');
        };

        const updateRatingStyles = (selectedValue) => {
            if (!ratingOptions.length) {
                return;
            }

            ratingOptions.forEach((label) => {
                const value = label.dataset.ratingOption;
                const isSelected = value === String(selectedValue);

                label.classList.toggle('border-amber-300', isSelected);
                label.classList.toggle('bg-amber-50', isSelected);
                label.classList.toggle('border-slate-200', !isSelected);
                label.classList.toggle('bg-slate-50', !isSelected);
            });
        };

        const pollStatus = async () => {
            if (!statusUrl || meetingLocked || meetingExpired) {
                return;
            }

            try {
                const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                meetingStarted = Boolean(data.meeting_started);
                meetingLocked = Boolean(data.meeting_locked);
                graceJoinAllowed = Boolean(data.grace_join_allowed);
                joinUnlocked = Boolean(data.join_unlocked);
                graceJoinAt = data.grace_join_at || graceJoinAt;
                meetingExpired = Boolean(data.meeting_expired);
                meetingExpiresAt = data.expires_at || meetingExpiresAt;
                reviewUnlockAt = data.review_unlock_at || reviewUnlockAt;
                const reviewWindowOpen = Boolean(data.review_window_open);

                if (meetingExpired) {
                    graceJoinAllowed = false;
                    joinUnlocked = false;
                }

                if (reviewWindowOpen) {
                    reviewUnlockAt = null;
                    if (reviewCountdown) {
                        reviewCountdown.textContent = reviewReadyLabel;
                        reviewCountdown.classList.add('text-emerald-700', 'font-semibold');
                    }
                    reviewLockedContainer?.classList.add('hidden');
                    reviewFormContainer?.classList.remove('hidden');
                }

                scheduleExpiryEnforcement();
                updateUI();
                updateReviewCountdown();
            } catch (error) {
                console.warn('Failed to poll meeting status', error);
            }
        };

        joinButton?.addEventListener('click', () => {
            if (!launchUrl || joinButton.disabled || meetingExpired) {
                return;
            }
            window.open(launchUrl, '_blank', 'noopener,noreferrer');
        });

        refreshButton?.addEventListener('click', () => {
            pollStatus();
        });

        scrollToReviewButton?.addEventListener('click', () => {
            if (!reviewSection) {
                return;
            }

            reviewSection.classList.remove('highlight-pulse');
            reviewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

            requestAnimationFrame(() => {
                reviewSection.classList.add('highlight-pulse');
            });

            setTimeout(() => {
                reviewSection.classList.remove('highlight-pulse');
            }, 1200);
        });

        ratingOptions.forEach((label) => {
            const input = label.querySelector('input[type="radio"]');
            if (!input) return;

            label.addEventListener('click', () => {
                input.checked = true;
                updateRatingStyles(input.value);
            });

            input.addEventListener('change', () => {
                updateRatingStyles(input.value);
            });
        });

        const initialChecked = document.querySelector('input[name="rating"]:checked');
        if (initialChecked) {
            updateRatingStyles(initialChecked.value);
        }

        updateUI();
        scheduleExpiryEnforcement();
        updateReviewCountdown();

        if (statusUrl && !meetingLocked && !meetingExpired) {
            setInterval(pollStatus, 7000);
        }
    });
</script>
@endpush







