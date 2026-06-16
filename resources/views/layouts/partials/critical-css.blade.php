<style data-critical="true">
    :root {
        color-scheme: light;
        font-feature-settings: 'kern';
        --layer-navbar: 40;
        --brand-navy: #6b2e30;
        --brand-navy-700: #4d1f22;
        --brand-teal: #9f5e63;
        --brand-teal-700: #7f3a3d;
        --brand-gold: #d7a5ac;
        --brand-gold-soft: #f8edee;
        --brand-gold-tint: #f1d8da;
        --brand-cream: #ffffff;
        --brand-ink: #321417;
        --brand-muted: #8f4a50;
        --brand-navy-rgb: 107, 46, 48;
        --brand-teal-rgb: 143, 74, 80;
        --brand-gold-rgb: 215, 165, 172;
        --navbar-height: clamp(72px, 9vw, 96px);
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        top: 0 !important;
    }

    body {
        font-family: 'Tajawal', var(--font-sans, ui-sans-serif), system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background-color: var(--brand-cream);
        min-height: 100vh;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        padding-top: var(--navbar-height) !important;
    }

    [data-navbar-layer] {
        z-index: var(--layer-navbar, 40);
    }

    /* Force navbar to sit flush to the top with no stray stripes */
    header[data-navbar-layer] {
        position: fixed !important;
        top: 0 !important;
        left: 0;
        right: 0;
        z-index: 9999 !important;
        margin: 0 !important;
        padding-top: 0 !important;
        width: 100%;
        background: transparent;
        border-bottom: 0;
        box-shadow: none;
    }

    header[data-navbar-layer]::before {
        content: none !important;
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

    .header-icon-btn {
        position: relative;
        display: inline-flex;
        height: 2.5rem;
        width: 2.5rem;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgb(var(--brand-navy-rgb) / 0.12);
        background: rgba(255, 255, 255, 0.92);
        color: #475569;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.1);
    }

    .header-chip-btn {
        border: 1px solid rgb(var(--brand-navy-rgb) / 0.1);
        background: rgba(255, 255, 255, 0.88);
        box-shadow:
            0 8px 18px rgb(var(--brand-navy-rgb) / 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .header-icon-btn:focus-visible,
    .header-chip-btn:focus-visible {
        outline: 2px solid rgb(var(--brand-gold-rgb) / 0.55);
        outline-offset: 2px;
    }

    header .header-container {
        gap: 1rem;
    }

    /* TheDolci navbar skin */
    header[data-navbar-layer].thedolci-navbar .header-container {
        border: 1px solid rgba(107, 46, 48, 0.18);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(248, 238, 224, 0.78));
        box-shadow: 0 16px 32px rgba(107, 46, 48, 0.15);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-logo-link {
        color: #6b2e30;
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-primary-nav {
        border-color: rgba(107, 46, 48, 0.18);
        background: rgba(255, 255, 255, 0.62);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-nav-link {
        color: #6b2e30;
        border-color: transparent;
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-nav-link-idle:hover {
        color: #6b2e30;
        border-color: rgba(107, 46, 48, 0.24);
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 6px 16px rgba(107, 46, 48, 0.12);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-nav-link-active,
    header[data-navbar-layer].thedolci-navbar .thedolci-nav-link[aria-current='page'] {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #6b2e30, #7f3a3d);
        box-shadow: 0 8px 20px rgba(107, 46, 48, 0.25);
    }

    header[data-navbar-layer].thedolci-navbar .header-icon-btn,
    header[data-navbar-layer].thedolci-navbar .header-chip-btn,
    header[data-navbar-layer].thedolci-navbar .mobile-menu-btn {
        border-color: rgba(107, 46, 48, 0.2);
        background: rgba(255, 255, 255, 0.72);
        color: #6b2e30;
        box-shadow: 0 10px 20px rgba(107, 46, 48, 0.12);
    }

    header[data-navbar-layer].thedolci-navbar .header-icon-btn:focus-visible,
    header[data-navbar-layer].thedolci-navbar .header-chip-btn:focus-visible {
        outline-color: rgba(107, 46, 48, 0.35);
    }

    header[data-navbar-layer].thedolci-navbar .header-icon-btn:hover,
    header[data-navbar-layer].thedolci-navbar .header-chip-btn:hover,
    header[data-navbar-layer].thedolci-navbar .mobile-menu-btn:hover {
        color: #6b2e30;
        border-color: rgba(107, 46, 48, 0.3);
        box-shadow: 0 14px 24px rgba(107, 46, 48, 0.18);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-partnership-btn {
        border-color: rgba(107, 46, 48, 0.22);
        color: #6b2e30;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 238, 224, 0.78));
        box-shadow: 0 10px 22px rgba(107, 46, 48, 0.15);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-partnership-btn:hover {
        border-color: rgba(107, 46, 48, 0.32);
        color: #6b2e30;
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-notification-dropdown > div,
    header[data-navbar-layer].thedolci-navbar .thedolci-user-dropdown {
        border-color: rgba(107, 46, 48, 0.16);
        background: rgba(255, 253, 248, 0.95);
        box-shadow: 0 24px 46px rgba(107, 46, 48, 0.16);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-user-trigger {
        border-color: rgba(107, 46, 48, 0.2);
        color: #6b2e30;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 238, 224, 0.75));
        box-shadow: 0 10px 22px rgba(107, 46, 48, 0.14);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-user-trigger:hover {
        border-color: rgba(107, 46, 48, 0.32);
        box-shadow: 0 14px 24px rgba(107, 46, 48, 0.2);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-search-flyout .desktop-search-flyout-inner {
        border-color: rgba(107, 46, 48, 0.2);
        background: rgba(255, 253, 248, 0.98);
        box-shadow: 0 22px 40px rgba(107, 46, 48, 0.16);
    }

    header[data-navbar-layer].thedolci-navbar .desktop-search-action,
    header[data-navbar-layer].thedolci-navbar .desktop-search-close {
        color: #6b2e30;
        background: rgba(255, 255, 255, 0.82);
        border-color: rgba(107, 46, 48, 0.2);
    }

    header[data-navbar-layer].thedolci-navbar .thedolci-mobile-menu {
        background: linear-gradient(160deg, rgba(255, 252, 247, 0.98), rgba(248, 238, 224, 0.94));
        border-color: rgba(107, 46, 48, 0.18);
        box-shadow: 0 28px 58px rgba(107, 46, 48, 0.24);
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



