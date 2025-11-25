<style data-critical="true">
    :root {
        color-scheme: light;
        font-feature-settings: 'kern';
        --layer-navbar: 40;
        --brand-navy: #0f4c73;
        --brand-navy-700: #0a344f;
        --brand-teal: #0fa289;
        --brand-teal-700: #0b705f;
        --brand-gold: #0fa289;
        --brand-gold-soft: #c8ede3;
        --brand-gold-tint: #a1dfd1;
        --brand-cream: #eaf3f6;
        --brand-ink: #0a2f46;
        --brand-muted: #4f6b82;
        --brand-navy-rgb: 15, 76, 115;
        --brand-teal-rgb: 15, 162, 137;
        --brand-gold-rgb: 15, 162, 137;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    body {
        font-family: 'Tajawal', var(--font-sans, ui-sans-serif), system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background-color: var(--brand-cream);
        min-height: 100vh;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
    }

    [data-navbar-layer] {
        z-index: var(--layer-navbar, 40);
    }

    body.mobile-menu-open {
        overflow: hidden;
    }

    body.mobile-menu-open > [data-navbar-layer] {
        z-index: 9998 !important;
    }

    body.mobile-menu-open #mobileMenu {
        z-index: 9999 !important;
    }

    [data-navbar-layer].mobile-menu-layer-active {
        z-index: 9998 !important;
    }

    [data-navbar-layer].mobile-menu-layer-active #mobileMenu {
        z-index: 9999 !important;
    }

    .mobile-menu {
        display: none;
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px);
        transition: opacity 0.25s ease, transform 0.25s ease;
        position: fixed;
        inset: 0;
        z-index: 2000;
        padding: calc(1rem + env(safe-area-inset-top)) 1.25rem calc(1.5rem + env(safe-area-inset-bottom));
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgb(var(--brand-navy-rgb) / 0.14);
        border-top: none;
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.3);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        backdrop-filter: blur(12px);
    }

    .mobile-menu.show {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0);
        z-index: 9999 !important;
    }

    .mobile-menu.hidden {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translateY(12px);
    }

    .mobile-menu-btn {
        border-radius: 0.75rem;
        padding: 0.35rem;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .mobile-menu-btn:hover {
        background-color: var(--brand-gold-soft);
    }

    header .header-container {
        gap: 1rem;
    }

    #dropdown-menu,
    #user-menu-dropdown {
        display: none;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    #dropdown-menu.show,
    #user-menu-dropdown.show {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0);
    }

    .mobile-tab-bar {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 60;
        display: block;
        padding: 0.35rem 0.85rem calc(0.75rem + env(safe-area-inset-bottom));
        background: rgba(255, 255, 255, 0.98);
        border-top: 1px solid rgb(var(--brand-navy-rgb) / 0.12);
        box-shadow: 0 -8px 20px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(10px);
        overflow: hidden;
        transform: translate3d(0, 0, 0);
        transition:
            transform 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            opacity 0.3s ease;
        will-change: transform, opacity;
    }

    .mobile-tab-bar--hidden {
        transform: translate3d(0, 120%, 0);
        opacity: 0;
        pointer-events: none;
    }

    .mobile-tab-bar__loading-line {
        position: absolute;
        inset-inline: 0.85rem;
        top: 0.35rem;
        height: 0.2rem;
        border-radius: 999px;
        background: rgb(var(--brand-gold-rgb) / 0.22);
        opacity: 0;
        transform: translateY(-0.35rem);
        transition: opacity 0.2s ease, transform 0.2s ease;
        pointer-events: none;
        overflow: hidden;
    }

    .mobile-tab-bar__loading-line::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, rgb(var(--brand-teal-rgb) / 0.18), rgb(var(--brand-gold-rgb) / 0.9));
        transform-origin: left center;
        transform: scaleX(0);
        opacity: 0;
        animation: mobile-tab-bar-line 1.2s ease-in-out infinite;
    }

    .mobile-tab-bar__inner {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.25rem;
    }

    .mobile-tab-bar__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        padding: 0.35rem 0.25rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--brand-muted);
        text-decoration: none;
    }

    .mobile-tab-bar__item.is-active {
        color: var(--brand-navy);
        background-color: rgb(var(--brand-gold-rgb) / 0.22);
        box-shadow: inset 0 0 0 1px rgb(var(--brand-navy-rgb) / 0.12);
    }

    .mobile-tab-bar__item:active {
        transform: translateY(1px);
    }

    .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item {
        animation: mobile-tab-bar-fade-up 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item:nth-child(2) {
        animation-delay: 0.05s;
    }

    .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item:nth-child(3) {
        animation-delay: 0.1s;
    }

    .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item:nth-child(4) {
        animation-delay: 0.15s;
    }

    .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item:nth-child(5) {
        animation-delay: 0.2s;
    }

    .mobile-tab-bar--loading {
        pointer-events: none;
    }

    .mobile-tab-bar--loading .mobile-tab-bar__loading-line {
        opacity: 1;
        transform: translateY(0);
    }

    .mobile-tab-bar--loading .mobile-tab-bar__item {
        opacity: 0.35;
    }

    @media (prefers-reduced-motion: reduce) {
        .mobile-tab-bar {
            transition: none;
        }

        .mobile-tab-bar:not(.mobile-tab-bar--hidden) .mobile-tab-bar__item {
            animation: none;
        }
    }
    @media (min-width: 768px) {
        .mobile-tab-bar {
            display: none !important;
        }
    }
    @keyframes mobile-tab-bar-fade-up {
        0% {
            opacity: 0;
            transform: translate3d(0, 30%, 0);
        }
        70% {
            opacity: 1;
            transform: translate3d(0, -6%, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes mobile-tab-bar-line {
        0% {
            transform: scaleX(0);
            opacity: 0.2;
        }
        50% {
            transform: scaleX(1);
            opacity: 1;
        }
        100% {
            transform: scaleX(0);
            opacity: 0.2;
        }
    }
</style>



