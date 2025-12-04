@extends('layouts.auth')

@section('title', __('auth.title'))

@php
    $hideLocaleSwitcher = true;
    $pendingWorkshopBooking = $pendingWorkshopBooking
        ?? $pendingWorkshop
        ?? request()->input('pending_workshop_booking');
@endphp

@push('styles')
<style>
:root {
    --auth-bg: #f4f6ff;
    --auth-card: #ffffff;
    --auth-primary: #0819ff;
    --auth-primary-dark: #050f9f;
    --auth-primary-soft: #e3ecff;
    --auth-accent: #1ab8ff;
    --auth-text: #1f2937;
    --auth-muted: #5c6fe0;
    --auth-border: rgba(8, 25, 255, 0.14);
}

.auth-shell {
    min-height: 100vh;
    background: radial-gradient(circle at top right, rgba(8, 25, 255, 0.16), transparent 55%),
        radial-gradient(circle at bottom left, rgba(26, 184, 255, 0.18), transparent 45%),
        var(--auth-bg);
    padding: clamp(1.5rem, 4vw, 4rem);
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-stack {
    width: min(100%, 1100px);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(1rem, 2vw, 1.75rem);
}

.auth-card {
    width: 100%;
    background: var(--auth-card);
    border-radius: 28px;
    box-shadow: 0 45px 120px rgba(8, 25, 255, 0.12);
    padding: clamp(1.75rem, 3vw, 3.5rem);
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    border: 1px solid rgba(8, 25, 255, 0.16);
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: clamp(1rem, 2vw, 2.25rem);
    padding-bottom: clamp(1.25rem, 2vw, 1.75rem);
    border-bottom: 1px solid var(--auth-border);
    justify-content: center;
}

.brand-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: clamp(180px, 22vw, 260px);
    padding: 0;
    background: transparent;
    border-radius: 0;
    box-shadow: none;
    flex-shrink: 0;
}

.brand-logo img {
    width: 100%;
    height: auto;
    object-fit: contain;
}

.auth-brand__copy {
    display: grid;
    gap: 0.35rem;
    text-align: start;
}

.auth-brand__eyebrow {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--auth-primary-dark);
    font-weight: 700;
}

.auth-brand__headline {
    margin: 0;
    font-size: clamp(1.35rem, 3vw, 1.75rem);
    color: var(--auth-text);
}

.auth-brand__subcopy {
    margin: 0;
    color: var(--auth-muted);
    line-height: 1.6;
}


.auth-flow {
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    display: grid;
    gap: 1.5rem;
    text-align: right;
}

.info-card {
    background: linear-gradient(135deg, #eef2ff, #e3ecff);
    border-radius: 22px;
    padding: 1.75rem;
    border: 1px solid rgba(8, 25, 255, 0.2);
    display: grid;
    gap: 1rem;
}

.info-card__title {
    font-size: 1.1rem;
    color: var(--auth-primary-dark);
    font-weight: 700;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 0.75rem;
}

.info-list li {
    display: flex;
    gap: 0.6rem;
    color: var(--auth-muted);
    line-height: 1.6;
}

.info-list i {
    color: var(--auth-primary);
    margin-top: 0.2rem;
}

.google-stack {
    display: grid;
    gap: 0.9rem;
}

.google-stack > p {
    text-align: left;
}

.google-btn {
    border-radius: 18px;
    border: 1px solid rgba(8, 25, 255, 0.18);
    padding: 0.95rem 1.2rem;
    font-weight: 700;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    cursor: pointer;
    background: linear-gradient(135deg, #ffffff, #eef2ff);
    color: var(--auth-text);
    box-shadow: 0 22px 45px rgba(8, 25, 255, 0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.google-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 30px 55px rgba(8, 25, 255, 0.22);
}

.google-btn img {
    width: 28px;
    height: 28px;
}

.micro-flags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.micro-flag {
    font-size: 0.8rem;
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    background: rgba(8, 25, 255, 0.12);
    color: var(--auth-primary-dark);
    font-weight: 600;
}


.divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--auth-muted);
    font-size: 0.9rem;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(8, 25, 255, 0.15);
}

.auth-form {
    display: grid;
    gap: 1rem;
}

.field-group {
    display: grid;
    gap: 0.35rem;
}

.field-group label {
    font-weight: 600;
    color: var(--auth-text);
}

.input-control {
    border-radius: 14px;
    border: 1px solid var(--auth-border);
    padding: 0.85rem 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
}

.input-control.has-error {
    border-color: #f87171;
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.15);
}

.input-control:focus {
    outline: none;
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 3px rgba(8, 25, 255, 0.18);
}

.field-hint {
    font-size: 0.85rem;
    color: var(--auth-muted);
}

.field-error {
    font-size: 0.85rem;
    color: #b42318;
}

.actions-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.remember {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    color: var(--auth-muted);
}

.ghost-link {
    color: var(--auth-primary);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.ghost-link:hover {
    color: var(--auth-primary-dark);
}

.primary-action {
    border: none;
    border-radius: 16px;
    padding: 0.95rem 1.6rem;
    background: linear-gradient(135deg, var(--auth-primary), var(--auth-primary-dark));
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}

.primary-action:hover {
    background: linear-gradient(135deg, var(--auth-primary-dark), var(--auth-primary));
    transform: translateY(-2px);
}

.intent-switch {
    display: flex;
    gap: 0.5rem;
}

.intent-pill {
    border: 1px solid rgba(8, 25, 255, 0.25);
    background: var(--auth-primary-soft);
    padding: 0.45rem 1.2rem;
    border-radius: 999px;
    font-weight: 600;
    color: var(--auth-muted);
    cursor: pointer;
    transition: all 0.2s ease;
}

.intent-pill.is-active {
    background: #fff;
    color: var(--auth-primary-dark);
    box-shadow: 0 12px 20px rgba(8, 25, 255, 0.18);
    border-color: rgba(8, 25, 255, 0.45);
}

.intent-pill[data-role="chef"].is-active {
    background: #e8f6ff;
    border-color: rgba(26, 184, 255, 0.35);
    color: #0f78ad;
}

.inline-alerts {
    display: grid;
    gap: 0.6rem;
}

.inline-alert {
    border-radius: 14px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    display: flex;
    gap: 0.6rem;
    align-items: flex-start;
    border: 1px solid transparent;
}

.inline-alert__icon {
    font-size: 1rem;
}

.inline-alert--success {
    background: #ecfdf3;
    border-color: #bbf7d0;
    color: #047857;
}

.inline-alert--error {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

.inline-alert--warning {
    background: #eef2ff;
    border-color: #c8d6ff;
    color: #050f9f;
}

.inline-alert--info {
    background: #f0f9ff;
    border-color: #bae6fd;
    color: #075985;
}

[data-intent-state="chef"] .google-btn {
    background: linear-gradient(135deg, #d9f2ff, #b6e5ff);
    color: #0f78ad;
}

[data-intent-state="chef"] .google-btn img {
    filter: drop-shadow(0 2px 4px rgba(15, 120, 173, 0.2));
}

[data-intent-state="chef"] .divider {
    color: #0f78ad;
}

[data-intent-state="chef"] .field-hint {
    color: #0f78ad;
}

[data-intent-state="chef"] .ghost-link {
    color: #0f78ad;
}

@media (max-width: 640px) {
    .auth-brand {
        flex-direction: column;
        text-align: center;
    }

    .auth-brand__copy {
        text-align: center;
    }

    .actions-row {
        flex-direction: column;
        align-items: stretch;
    }

    .primary-action {
        width: 100%;
        text-align: center;
    }
}
</style>
@endpush

@section('content')
<div class="auth-shell">
    <div class="auth-stack">
        <div class="auth-card">
            <div class="auth-flow" data-intent-state="customer">
                <div class="auth-brand">
                    <a href="{{ route('home') }}" class="brand-logo" aria-label="{{ __('auth.logo_alt') }}">
                        <x-optimized-picture
                            :base="\App\Support\BrandAssets::logoBase()"
                            :widths="[128, 256, 384, 512]"
                            alt="{{ __('auth.logo_alt') }}"
                            :lazy="false"
                            sizes="(max-width: 480px) 200px, (max-width: 768px) 240px, 260px"
                        />
                    </a>
                </div>

                <div class="inline-alerts" aria-live="polite" data-copy-switch>
                    @foreach (['success' => 'inline-alert--success', 'status' => 'inline-alert--info', 'info' => 'inline-alert--info', 'error' => 'inline-alert--error'] as $key => $class)
                        @if (session($key))
                            <div class="inline-alert {{ $class }}">
                                <span class="inline-alert__icon">
                                    @switch($key)
                                        @case('success') ✅ @break
                                        @case('error') ⚠️ @break
                                        @default ℹ️
                                    @endswitch
                                </span>
                                <div>{{ session($key) }}</div>
                            </div>
                        @endif
                    @endforeach

                    @if ($errors->any())
                        <div class="inline-alert inline-alert--warning">
                            <span class="inline-alert__icon">⚠️</span>
                            <div>
                                <strong>{{ __('auth.alerts.review_fields') }}</strong>
                                <ul class="list-disc space-y-1 pe-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="google-stack">
                    <div class="intent-switch" data-intent-switch="login">
                        <button type="button" class="intent-pill is-active" data-role="customer">{{ __('auth.intent.customer') }}</button>
                        <button type="button" class="intent-pill" data-role="chef">{{ __('auth.intent.chef') }}</button>
                    </div>
                    <button type="button" class="google-btn" data-google-button data-sync-role="hybrid">
                        <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google" loading="lazy" width="48" height="48" decoding="async">
                        {{ __('auth.google.cta') }}
                    </button>
                    <div class="micro-flags">
                        <span class="micro-flag">{{ __('auth.google.flags.secure') }}</span>
                        <span class="micro-flag">{{ __('auth.google.flags.support') }}</span>
                    </div>
                </div>

                <div class="divider">{{ __('auth.divider.email') }}</div>

                <form action="{{ route('login.password') }}" method="POST" class="auth-form" novalidate>
                    @csrf
                    <input type="hidden" name="form_source" value="login">
                    @if (!empty($pendingWorkshopBooking))
                        <input type="hidden" name="pending_workshop_booking" value="{{ $pendingWorkshopBooking }}">
                    @endif
                    <div class="field-group" data-field-switch="email">
                        <label for="login-email">{{ __('auth.form.email.label') }}</label>
                        <input
                            id="login-email"
                            name="email"
                            type="email"
                            dir="ltr"
                            autocomplete="email"
                            value="{{ old('form_source') === 'login' ? old('email') : '' }}"
                            class="input-control {{ $errors->has('email') && old('form_source') === 'login' ? 'has-error' : '' }}"
                        >
                        <p class="field-hint">{{ __('auth.form.email.hint') }}</p>
                        @if ($errors->has('email') && old('form_source') === 'login')
                            <p class="field-error">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <div class="field-group" data-field-switch="password">
                        <label for="login-password">{{ __('auth.form.password.label') }}</label>
                        <input
                            id="login-password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            class="input-control {{ $errors->has('password') && old('form_source') === 'login' ? 'has-error' : '' }}"
                        >
                        <p class="field-hint">{{ __('auth.form.password.hint') }}</p>
                        @if ($errors->has('password') && old('form_source') === 'login')
                            <p class="field-error">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <div class="actions-row">
                        <label class="remember" data-field-switch="remember">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ (old('form_source') === 'login' && old('remember')) ? 'checked' : '' }}>
                            {{ __('auth.form.remember') }}
                        </label>
                        <a href="{{ route('contact') }}" class="ghost-link">{{ __('auth.form.help') }}</a>
                    </div>

                    <button type="submit" class="primary-action">{{ __('auth.form.submit') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const pendingWorkshop = params.get('pending_workshop_booking');
    const returnTo = params.get('return_to');
    const intentContainer = document.querySelector('[data-intent-state]');
    const introDefault = document.querySelector('[data-text-switch="intro-default"]');
    const introChef = document.querySelector('[data-text-switch="intro-chef"]');
    const toggleIntroText = (intent) => {
        if (!introDefault || !introChef) {
            return;
        }
        const isChef = intent === 'chef';
        introDefault.classList.toggle('hidden', isChef);
        introChef.classList.toggle('hidden', !isChef);
        if (intentContainer) {
            intentContainer.dataset.intentState = intent;
        }
    };

    const redirectToGoogleAuth = (intent, flow) => {
        const url = new URL('{{ route('google.redirect') }}', window.location.origin);
        if (pendingWorkshop) {
            url.searchParams.set('pending_workshop_booking', pendingWorkshop);
        }
        if (returnTo) {
            url.searchParams.set('return_to', returnTo);
        }
        if (flow) {
            url.searchParams.set('flow', flow);
        }
        if (intent) {
            url.searchParams.set('intent', intent);
        }
        window.location.href = url.toString();
    };

    const loginIntentSwitch = document.querySelector('[data-intent-switch="login"]');
    if (loginIntentSwitch) {
        loginIntentSwitch.addEventListener('click', (event) => {
            const pill = event.target.closest('.intent-pill');
            if (!pill) {
                return;
            }
            loginIntentSwitch.querySelectorAll('.intent-pill').forEach((btn) => {
                btn.classList.toggle('is-active', btn === pill);
            });
            toggleIntroText(pill.dataset.role);
        });
    }

    const getSelectedIntent = () => {
        const activePill = loginIntentSwitch?.querySelector('.intent-pill.is-active');
        return activePill ? activePill.dataset.role : 'customer';
    };

    document.querySelectorAll('[data-google-button]').forEach((button) => {
        button.addEventListener('click', () => {
            let intent = button.dataset.googleIntent || getSelectedIntent();
            let flow = button.dataset.googleFlow || 'login';

            if (button.dataset.syncRole === 'hybrid' || button.dataset.syncRole === 'register') {
                intent = getSelectedIntent();
                flow = intent === 'chef' ? 'register_chef' : 'register_customer';
            } else if (button.dataset.syncRole === 'login') {
                intent = getSelectedIntent();
                flow = 'login';
            }

            redirectToGoogleAuth(intent, flow);
        });
    });
});
</script>
@endpush





