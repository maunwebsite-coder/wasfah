@extends('layouts.auth')

@section('title', __('auth.title'))

@php
    $hideLocaleSwitcher = true;
    $pendingWorkshopBooking = $pendingWorkshopBooking
        ?? $pendingWorkshop
        ?? request()->input('pending_workshop_booking');
    $currentLocale = app()->getLocale();
    $isArabic = $currentLocale === 'ar';
    $isAdminLoginMode = request()->query('mode') === 'admin';
    $alternateLocale = $isArabic ? 'en' : 'ar';
    $languageLabel = data_get(\Illuminate\Support\Facades\Lang::get('navbar.language'), 'short.' . $alternateLocale, strtoupper($alternateLocale));
    $authQuickLinks = $isAdminLoginMode
        ? [
            ['href' => route('home'), 'label' => 'Storefront'],
            ['href' => route('thedolci.track-order'), 'label' => 'Track order'],
            ['href' => route('contact'), 'label' => 'Support'],
            ['href' => route('legal.terms'), 'label' => 'Terms'],
            ['href' => route('legal.privacy'), 'label' => 'Privacy'],
        ]
        : [
            ['href' => route('thedolci.shop'), 'label' => 'Shop'],
            ['href' => route('thedolci.seasonal'), 'label' => 'Seasonal'],
            ['href' => route('about'), 'label' => 'About Thedolci'],
            ['href' => route('contact'), 'label' => 'Contact us'],
            ['href' => route('thedolci.track-order'), 'label' => 'Track order'],
            ['href' => route('legal.terms'), 'label' => 'Legal terms'],
            ['href' => route('legal.privacy'), 'label' => 'Privacy Policy'],
        ];
    $authNavAriaLabel = $isAdminLoginMode ? 'Thedolci admin quick links' : 'Thedolci account links';

    if ($isAdminLoginMode) {
        $storyKicker = 'Thedolci Admin Gateway';
        $storyHeadline = 'Sign in to access the new storefront admin workspace.';
        $storySubcopy = 'A focused workspace for orders, products, reviews, and storefront content in one place.';
        $storyIntroDefault = 'This entry point is tailored for administrator accounts only.';
        $storyPoints = [
            __('auth.google.flags.secure'),
            'Role-based admin access',
            'Fast storefront content control',
        ];
        $storyChips = ['Admin Panel', 'Orders', 'Storefront'];
        $panelKicker = 'Admin Sign In';
        $panelHeadline = 'Sign in to admin';
        $panelSubcopy = 'Password sign-in is available for approved administrator accounts.';
        $googleCallToAction = 'Continue with Google (existing account)';
        $emailDividerLabel = 'Or use administrator email and password';
        $submitLabel = 'Sign in to admin';
    } else {
        $storyKicker = __('auth.brand.eyebrow');
        $storyHeadline = __('auth.brand.headline');
        $storySubcopy = __('auth.brand.subcopy');
        $storyIntroDefault = __('auth.intro.default');
        $storyPoints = [
            __('auth.google.flags.secure'),
            __('auth.google.flags.support'),
            __('auth.form.remember'),
        ];
        $storyChips = [__('auth.intent.customer'), __('auth.intent.chef')];
        $panelKicker = __('auth.brand.eyebrow');
        $panelHeadline = __('auth.form.submit');
        $panelSubcopy = __('auth.brand.headline');
        $googleCallToAction = __('auth.google.cta');
        $emailDividerLabel = __('auth.divider.email');
        $submitLabel = __('auth.form.submit');
    }
@endphp

@push('preloads')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Playfair+Display:wght@600;700&display=swap" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Playfair+Display:wght@600;700&display=swap" media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Playfair+Display:wght@600;700&display=swap">
</noscript>
@endpush

@push('styles')
<style>
:root {
    --auth-cream-50: #f8f3ec;
    --auth-beige-100: #efe4d6;
    --auth-cocoa-600: #6b3f2b;
    --auth-espresso-800: #3d2418;
    --auth-espresso-900: #28170f;
    --auth-gold-accent: #c7a66a;
    --auth-success: #2f7a4a;
    --auth-error: #a63a2f;
    --auth-ink: #23180f;
    --auth-muted: #5f4e42;
    --auth-border: rgba(61, 36, 24, 0.16);
    --auth-card-bg: rgba(255, 251, 245, 0.88);
    --auth-transition: 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.auth-shell {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    background:
        radial-gradient(circle at 16% 8%, rgba(199, 166, 106, 0.34), transparent 36%),
        radial-gradient(circle at 85% 16%, rgba(107, 63, 43, 0.28), transparent 34%),
        linear-gradient(140deg, #f9f4ed 0%, #f2e7da 46%, #f9f4eb 100%);
    padding: clamp(1rem, 3vw, 2.8rem);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Manrope', 'Tajawal', sans-serif;
}

.auth-shell::before,
.auth-shell::after {
    content: "";
    position: absolute;
    z-index: 0;
    border-radius: 50%;
    opacity: 0.52;
    animation: floatDrift 15s ease-in-out infinite;
    pointer-events: none;
}

.auth-shell::before {
    width: clamp(240px, 32vw, 410px);
    height: clamp(240px, 32vw, 410px);
    top: -140px;
    right: -120px;
    background: radial-gradient(circle, rgba(199, 166, 106, 0.34) 0%, rgba(199, 166, 106, 0) 72%);
}

.auth-shell::after {
    width: clamp(220px, 34vw, 460px);
    height: clamp(220px, 34vw, 460px);
    left: -130px;
    bottom: -150px;
    background: radial-gradient(circle, rgba(61, 36, 24, 0.32) 0%, rgba(61, 36, 24, 0) 72%);
    animation-delay: -4s;
}

.auth-stack {
    width: min(1200px, 100%);
    position: relative;
    z-index: 1;
    display: grid;
    gap: 1rem;
}

.auth-utility {
    border: 1px solid var(--auth-border);
    background: rgba(255, 252, 248, 0.72);
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(40, 23, 15, 0.12);
    backdrop-filter: blur(8px);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.auth-language-form {
    margin: 0;
}

.auth-language-btn {
    border-radius: 999px;
    border: 1px solid rgba(61, 36, 24, 0.25);
    background: linear-gradient(140deg, #fff9f2, #f7ebdd);
    color: var(--auth-espresso-900);
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 700;
    padding: 0.42rem 0.8rem;
    cursor: pointer;
    transition: transform var(--auth-transition), box-shadow var(--auth-transition), border-color var(--auth-transition);
}

.auth-language-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(61, 36, 24, 0.38);
    box-shadow: 0 8px 16px rgba(40, 23, 15, 0.14);
}

.auth-nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.38rem 0.68rem;
}

.auth-nav-link {
    color: var(--auth-muted);
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 600;
    transition: color var(--auth-transition);
}

.auth-nav-link:hover {
    color: var(--auth-espresso-900);
}

.auth-nav-separator {
    color: rgba(40, 23, 15, 0.42);
    font-size: 0.72rem;
}

.auth-grid {
    display: grid;
    grid-template-columns: minmax(360px, 1.08fr) minmax(320px, 0.92fr);
    gap: clamp(0.95rem, 2vw, 1.4rem);
    align-items: stretch;
}

.auth-grid[data-auth-mode="admin"] {
    grid-template-columns: minmax(340px, 0.95fr) minmax(360px, 1.05fr);
}

.auth-grid[data-auth-mode="admin"] .auth-story {
    background:
        radial-gradient(circle at 82% 18%, rgba(199, 166, 106, 0.22), transparent 42%),
        linear-gradient(155deg, rgba(255, 255, 255, 0.95), rgba(247, 236, 224, 0.9));
}

.auth-grid[data-auth-mode="admin"] .story-kicker,
.auth-grid[data-auth-mode="admin"] .panel-kicker,
.auth-grid[data-auth-mode="admin"] .micro-flag {
    color: #6b2e30;
}

.auth-grid[data-auth-mode="admin"] .google-btn {
    background: linear-gradient(145deg, #fffdf9, #f4e8db);
    border-color: rgba(107, 46, 48, 0.28);
    color: #4d1f22;
}

.auth-grid[data-auth-mode="admin"] .primary-action {
    background: linear-gradient(135deg, #6b2e30, #7f3a3d);
}

.auth-grid[data-auth-mode="admin"] .ghost-link {
    color: #6b2e30;
}

.auth-grid[data-auth-mode="admin"] .ghost-link:hover {
    color: #3d2418;
}

.auth-story,
.auth-panel {
    border-radius: 24px;
    border: 1px solid var(--auth-border);
    background: var(--auth-card-bg);
    box-shadow: 0 30px 70px rgba(40, 23, 15, 0.16);
    backdrop-filter: blur(9px);
}

.auth-story {
    display: grid;
    align-content: start;
    gap: 0.9rem;
    padding: clamp(1.25rem, 3vw, 2.4rem);
    position: relative;
    overflow: hidden;
    animation: riseIn 520ms ease-out;
}

.auth-story::after {
    content: "";
    position: absolute;
    width: clamp(170px, 24vw, 280px);
    height: clamp(170px, 24vw, 280px);
    right: -90px;
    top: -110px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(199, 166, 106, 0.26) 0%, rgba(199, 166, 106, 0) 72%);
    pointer-events: none;
}

.brand-logo {
    width: max-content;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.16rem;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    text-decoration: none;
    border: 1px solid rgba(40, 23, 15, 0.2);
    background: linear-gradient(145deg, #fff6eb, #f5e6d6);
    box-shadow: 0 10px 20px rgba(40, 23, 15, 0.13);
    color: var(--auth-espresso-800);
    font-size: 0.98rem;
    letter-spacing: 0.04em;
    font-weight: 700;
    text-transform: lowercase;
}

.brand-logo__lead {
    color: #6a4a3b;
}

.brand-logo__accent {
    color: var(--auth-espresso-900);
}

.brand-logo__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--auth-gold-accent);
    display: inline-block;
    margin-inline-start: 0.14rem;
}

.story-kicker {
    margin: 0;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--auth-cocoa-600);
    font-weight: 700;
}

.story-headline {
    margin: 0;
    font-family: 'Playfair Display', 'Cairo', serif;
    font-size: clamp(1.55rem, 2.8vw, 2.32rem);
    line-height: 1.2;
    color: var(--auth-espresso-900);
    max-width: 23ch;
}

.story-subcopy {
    margin: 0;
    color: var(--auth-muted);
    line-height: 1.72;
    max-width: 56ch;
}

.story-intro {
    display: grid;
    gap: 0.5rem;
    border: 1px solid rgba(61, 36, 24, 0.18);
    background: linear-gradient(140deg, rgba(199, 166, 106, 0.16), rgba(255, 253, 250, 0.4));
    border-radius: 14px;
    padding: 0.9rem 1rem;
}

.story-intro p {
    margin: 0;
    line-height: 1.63;
    color: #4e3f36;
}

.story-points {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.62rem;
}

.story-points li {
    display: flex;
    align-items: center;
    gap: 0.56rem;
    color: var(--auth-ink);
    font-weight: 600;
    font-size: 0.92rem;
}

.story-points i {
    color: var(--auth-success);
}

.story-chips {
    display: flex;
    gap: 0.55rem;
    flex-wrap: wrap;
}

.story-chip {
    font-size: 0.75rem;
    background: rgba(255, 249, 239, 0.95);
    border: 1px solid rgba(61, 36, 24, 0.17);
    border-radius: 999px;
    padding: 0.34rem 0.78rem;
    color: var(--auth-muted);
    font-weight: 700;
}

.auth-panel {
    display: grid;
    align-content: start;
    gap: 0.95rem;
    padding: clamp(1.2rem, 3vw, 2.2rem);
    animation: riseIn 560ms ease-out 90ms both;
}

.panel-head {
    display: grid;
    gap: 0.32rem;
}

.panel-kicker {
    margin: 0;
    color: var(--auth-cocoa-600);
    font-size: 0.73rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-weight: 700;
}

.panel-headline {
    margin: 0;
    font-family: 'Playfair Display', 'Cairo', serif;
    color: var(--auth-espresso-900);
    font-size: clamp(1.32rem, 2.1vw, 1.8rem);
}

.panel-subcopy {
    margin: 0;
    color: var(--auth-muted);
    font-size: 0.9rem;
    line-height: 1.62;
}

.intent-switch {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.4rem;
    padding: 0.32rem;
    border-radius: 999px;
    border: 1px solid rgba(61, 36, 24, 0.16);
    background: rgba(255, 247, 236, 0.92);
}

.intent-pill {
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    padding: 0.48rem 0.7rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--auth-muted);
    cursor: pointer;
    transition: all var(--auth-transition);
}

.intent-pill.is-active {
    background: linear-gradient(145deg, #fffdf8, #f6ede2);
    border-color: rgba(61, 36, 24, 0.25);
    color: var(--auth-espresso-900);
    box-shadow: 0 10px 16px rgba(40, 23, 15, 0.16);
}

.intent-pill[data-role="chef"].is-active {
    background: linear-gradient(145deg, #fff3ea, #f8e1ca);
    border-color: rgba(107, 63, 43, 0.4);
    color: var(--auth-cocoa-600);
}

.google-stack {
    display: grid;
    gap: 0.72rem;
}

.google-label {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--auth-espresso-800);
}

.google-btn {
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(61, 36, 24, 0.22);
    padding: 0.9rem 1rem;
    font-weight: 700;
    font-size: 0.92rem;
    line-height: 1.35;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    cursor: pointer;
    background: linear-gradient(145deg, #fffdf7, #f7ebdc);
    color: var(--auth-espresso-900);
    box-shadow: 0 12px 24px rgba(40, 23, 15, 0.16);
    transition: transform var(--auth-transition), box-shadow var(--auth-transition), border-color var(--auth-transition);
}

.google-btn:hover {
    transform: translateY(-2px);
    border-color: rgba(61, 36, 24, 0.38);
    box-shadow: 0 18px 30px rgba(40, 23, 15, 0.2);
}

.google-btn img {
    width: 22px;
    height: 22px;
}

.google-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.micro-flag {
    font-size: 0.72rem;
    padding: 0.3rem 0.68rem;
    border-radius: 999px;
    background: rgba(199, 166, 106, 0.22);
    color: var(--auth-cocoa-600);
    font-weight: 600;
}

.divider {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: var(--auth-muted);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(61, 36, 24, 0.18);
}

.auth-form {
    display: grid;
    gap: 0.88rem;
}

.field-group {
    display: grid;
    gap: 0.32rem;
}

.field-group label {
    font-weight: 700;
    color: var(--auth-ink);
    font-size: 0.88rem;
}

.input-control {
    border-radius: 12px;
    border: 1px solid var(--auth-border);
    padding: 0.78rem 0.9rem;
    transition: border-color var(--auth-transition), box-shadow var(--auth-transition), background-color var(--auth-transition);
    background: rgba(255, 252, 248, 0.95);
    color: var(--auth-ink);
}

.input-control.has-error {
    border-color: var(--auth-error);
    box-shadow: 0 0 0 3px rgba(166, 58, 47, 0.16);
}

.input-control:focus {
    outline: none;
    border-color: var(--auth-cocoa-600);
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(107, 63, 43, 0.2);
}

.field-hint {
    font-size: 0.77rem;
    color: var(--auth-muted);
}

.field-error {
    font-size: 0.8rem;
    color: var(--auth-error);
}

.actions-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.7rem;
}

.remember {
    display: flex;
    align-items: center;
    gap: 0.42rem;
    font-size: 0.84rem;
    color: var(--auth-muted);
    font-weight: 600;
}

.ghost-link {
    color: var(--auth-cocoa-600);
    font-weight: 600;
    text-decoration: none;
    transition: color var(--auth-transition);
}

.ghost-link:hover {
    color: var(--auth-espresso-900);
}

.primary-action {
    border: none;
    border-radius: 12px;
    padding: 0.83rem 1.32rem;
    background: linear-gradient(145deg, var(--auth-espresso-800), var(--auth-espresso-900));
    color: #fff;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    font-size: 0.79rem;
    cursor: pointer;
    transition: transform var(--auth-transition), box-shadow var(--auth-transition), filter var(--auth-transition);
}

.primary-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 26px rgba(40, 23, 15, 0.32);
    filter: brightness(1.04);
}

.inline-alerts {
    display: grid;
    gap: 0.52rem;
}

.inline-alert {
    border-radius: 12px;
    padding: 0.66rem 0.8rem;
    font-size: 0.82rem;
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
    border: 1px solid transparent;
}

.inline-alert__icon {
    font-size: 0.95rem;
}

.inline-alert--success {
    background: #ecfdf3;
    border-color: #94e2b0;
    color: #0f6f3f;
}

.inline-alert--error {
    background: #fff2f1;
    border-color: #f4bbb5;
    color: #9d3328;
}

.inline-alert--warning {
    background: #fff8ec;
    border-color: #f4d7a8;
    color: #734317;
}

.inline-alert--info {
    background: #f5f3ff;
    border-color: #d6cbff;
    color: #4a3b88;
}

[data-intent-state="chef"] .story-intro {
    border-color: rgba(107, 63, 43, 0.34);
    background: linear-gradient(140deg, rgba(199, 166, 106, 0.24), rgba(107, 63, 43, 0.08));
}

[data-intent-state="chef"] .google-btn {
    background: linear-gradient(145deg, #fff4e7, #f6dfc4);
    color: var(--auth-cocoa-600);
}

[data-intent-state="chef"] .google-btn img {
    filter: drop-shadow(0 2px 3px rgba(107, 63, 43, 0.24));
}

[data-intent-state="chef"] .divider {
    color: var(--auth-cocoa-600);
}

[data-intent-state="chef"] .panel-kicker,
[data-intent-state="chef"] .field-hint,
[data-intent-state="chef"] .micro-flag {
    color: #7d4f31;
}

[data-intent-state="chef"] .ghost-link {
    color: #7d4f31;
}

.field-error-list {
    margin: 0.3rem 0 0;
    padding-inline-start: 1.1rem;
    display: grid;
    gap: 0.16rem;
}

.hidden {
    display: none !important;
}

@media (max-width: 1080px) {
    .auth-grid {
        grid-template-columns: 1fr;
    }

    .auth-utility {
        flex-direction: column;
        align-items: stretch;
        gap: 0.62rem;
    }

    .auth-language-form {
        display: flex;
        justify-content: center;
    }

    .story-headline {
        max-width: none;
    }
}

@media (max-width: 740px) {
    .auth-shell {
        padding: 0.9rem;
    }

    .auth-utility {
        border-radius: 16px;
        padding: 0.62rem 0.7rem;
    }

    .auth-nav {
        gap: 0.2rem 0.46rem;
    }

    .auth-nav-link {
        font-size: 0.72rem;
    }

    .auth-story,
    .auth-panel {
        border-radius: 19px;
        padding: 1rem;
    }

    .intent-switch {
        grid-template-columns: 1fr;
    }

    .actions-row {
        flex-direction: column;
        align-items: stretch;
    }

    .primary-action {
        width: 100%;
    }
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes floatDrift {
    0%,
    100% {
        transform: translateY(0) translateX(0);
    }
    50% {
        transform: translateY(12px) translateX(-10px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .auth-shell::before,
    .auth-shell::after,
    .auth-story,
    .auth-panel,
    .google-btn,
    .primary-action {
        animation: none;
    }
}
</style>
@endpush

@section('content')
<div class="auth-shell">
    <div class="auth-stack">
        <div class="auth-utility">
            <form method="POST" action="{{ route('locale.switch') }}" class="auth-language-form">
                @csrf
                <input type="hidden" name="locale" value="{{ $alternateLocale }}">
                <button type="submit" class="auth-language-btn">{{ $languageLabel }}</button>
            </form>

            <nav class="auth-nav" aria-label="{{ $authNavAriaLabel }}">
                @foreach ($authQuickLinks as $link)
                    @if (! $loop->first)
                        <span class="auth-nav-separator" aria-hidden="true">&bull;</span>
                    @endif
                    <a href="{{ $link['href'] }}" class="auth-nav-link">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </div>

        <div class="auth-grid" data-auth-mode="{{ $isAdminLoginMode ? 'admin' : 'default' }}" data-intent-state="{{ $isAdminLoginMode ? 'admin' : 'customer' }}">
            <section class="auth-story">
                <a href="{{ route('home') }}" class="brand-logo" aria-label="{{ __('auth.logo_alt') }}">
                    <span class="brand-logo__lead">the</span><span class="brand-logo__accent">dolci</span><span class="brand-logo__dot" aria-hidden="true"></span>
                </a>

                <p class="story-kicker">{{ $storyKicker }}</p>
                <h1 class="story-headline">{{ $storyHeadline }}</h1>
                <p class="story-subcopy">{{ $storySubcopy }}</p>

                <div class="story-intro">
                    <p data-text-switch="intro-default">{{ $storyIntroDefault }}</p>
                    @unless($isAdminLoginMode)
                        <p class="hidden" data-text-switch="intro-chef">{{ __('auth.intro.chef') }}</p>
                    @endunless
                </div>

                <ul class="story-points">
                    @foreach ($storyPoints as $point)
                        <li><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> {{ $point }}</li>
                    @endforeach
                </ul>

                <div class="story-chips">
                    @foreach ($storyChips as $chip)
                        <span class="story-chip">{{ $chip }}</span>
                    @endforeach
                </div>
            </section>

            <section class="auth-panel">
                <div class="panel-head">
                    <p class="panel-kicker">{{ $panelKicker }}</p>
                    <h2 class="panel-headline">{{ $panelHeadline }}</h2>
                    <p class="panel-subcopy">{{ $panelSubcopy }}</p>
                </div>

                <div class="inline-alerts" aria-live="polite" data-copy-switch>
                    @foreach (['success' => 'inline-alert--success', 'status' => 'inline-alert--info', 'info' => 'inline-alert--info', 'error' => 'inline-alert--error'] as $key => $class)
                        @if (session($key))
                            <div class="inline-alert {{ $class }}">
                                <span class="inline-alert__icon">
                                    @switch($key)
                                        @case('success')
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            @break
                                        @case('error')
                                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                            @break
                                        @default
                                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                    @endswitch
                                </span>
                                <div>{{ session($key) }}</div>
                            </div>
                        @endif
                    @endforeach

                    @if ($errors->any())
                        <div class="inline-alert inline-alert--warning">
                            <span class="inline-alert__icon">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                            </span>
                            <div>
                                <strong>{{ __('auth.alerts.review_fields') }}</strong>
                                <ul class="field-error-list">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                @unless($isAdminLoginMode)
                    <div class="intent-switch" data-intent-switch="login">
                        <button type="button" class="intent-pill is-active" data-role="customer">{{ __('auth.intent.customer') }}</button>
                        <button type="button" class="intent-pill" data-role="chef">{{ __('auth.intent.chef') }}</button>
                    </div>
                @endunless

                <div class="google-stack">
                    <p class="google-label">Google</p>
                    <button type="button" class="google-btn" data-google-button data-sync-role="{{ $isAdminLoginMode ? 'login' : 'hybrid' }}">
                        <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google" loading="lazy" width="48" height="48" decoding="async">
                        <span>{{ $googleCallToAction }}</span>
                    </button>
                    <div class="google-meta">
                        <span class="micro-flag">{{ __('auth.google.flags.secure') }}</span>
                        <span class="micro-flag">{{ __('auth.google.flags.support') }}</span>
                    </div>
                </div>

                <div class="divider">{{ $emailDividerLabel }}</div>

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

                    <button type="submit" class="primary-action">{{ $submitLabel }}</button>
                </form>
            </section>
        </div>
    </div>
</div>
@include('layouts.partials.footer-auth-thedolci')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const pendingWorkshop = params.get('pending_workshop_booking');
    const returnTo = params.get('return_to');
    const intentContainer = document.querySelector('.auth-grid[data-intent-state]');
    const introDefault = document.querySelector('[data-text-switch="intro-default"]');
    const introChef = document.querySelector('[data-text-switch="intro-chef"]');

    const applyIntentState = (intent) => {
        if (!introDefault || !introChef) {
            if (intentContainer) {
                intentContainer.dataset.intentState = intent;
            }
            return intent;
        }

        const isChef = intent === 'chef';
        introDefault.classList.toggle('hidden', isChef);
        introChef.classList.toggle('hidden', !isChef);

        if (intentContainer) {
            intentContainer.dataset.intentState = intent;
        }

        return intent;
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
    const getSelectedIntent = () => {
        const activePill = loginIntentSwitch?.querySelector('.intent-pill.is-active');
        return activePill ? activePill.dataset.role : 'customer';
    };

    if (loginIntentSwitch) {
        loginIntentSwitch.addEventListener('click', (event) => {
            const pill = event.target.closest('.intent-pill');
            if (!pill) {
                return;
            }

            loginIntentSwitch.querySelectorAll('.intent-pill').forEach((btn) => {
                btn.classList.toggle('is-active', btn === pill);
            });

            applyIntentState(pill.dataset.role);
        });
    }

    applyIntentState(getSelectedIntent());

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

            redirectToGoogleAuth(applyIntentState(intent), flow);
        });
    });
});
</script>
@endpush
