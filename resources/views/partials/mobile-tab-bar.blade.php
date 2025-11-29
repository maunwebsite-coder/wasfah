@php
    $authUser = Auth::user();
    $tabBarLabels = trans('navbar.tab_bar');
    $isChef = $authUser?->isChef();
    $hasBookingsAccess = (bool) $authUser;
    $lockVisibility = (bool) ($lockVisibility ?? false);

    $tabItems = [
        [
            'key' => 'home',
            'href' => route('home'),
            'icon' => 'fa-solid fa-house-chimney',
            'label' => data_get($tabBarLabels, 'home', __('navbar.links.home')),
            'active' => request()->routeIs('home', 'home.*'),
        ],
        [
            'key' => 'recipes',
            'href' => route('recipes'),
            'icon' => 'fa-solid fa-play',
            'label' => data_get($tabBarLabels, 'recipes', __('navbar.links.recipes')),
            'active' => request()->routeIs('recipes', 'recipe.*'),
        ],
        [
            'key' => 'workshops',
            'href' => route('workshops'),
            'icon' => 'fa-solid fa-graduation-cap',
            'label' => data_get($tabBarLabels, 'workshops', __('navbar.links.workshops')),
            'active' => request()->routeIs('workshops', 'workshops.*', 'workshop.*'),
        ],
    ];

    $isChefRoute = $isChef
        ? request()->routeIs('chef.*') && ! request()->routeIs('chef.public.*')
        : false;

    $tabItems[] = [
        'key' => $isChef ? 'chef' : 'profile',
        'href' => $isChef ? route('chef.dashboard') : route('profile'),
        'icon' => $isChef ? 'fa-solid fa-bowl-food' : 'fa-solid fa-user',
        'label' => data_get(
            $tabBarLabels,
            $isChef ? 'chef' : 'profile',
            $isChef ? data_get($tabBarLabels, 'chef', __('navbar.chef_links.dashboard')) : __('navbar.account_menu.links.profile')
        ),
        'active' => $isChef ? $isChefRoute : request()->routeIs('profile*'),
    ];

    if ($hasBookingsAccess) {
        array_splice($tabItems, 2, 0, [[
            'key' => 'bookings',
            'href' => route('bookings.index'),
            'icon' => 'fa-solid fa-calendar-check',
            'label' => data_get($tabBarLabels, 'bookings', __('navbar.account_menu.links.bookings')),
            'active' => request()->routeIs('bookings*'),
        ]]);
    }

    $loaderLabel = data_get($tabBarLabels, 'loading_label', 'Updating your page');
    $loaderHint = data_get($tabBarLabels, 'loading_hint', 'Hang tight for a moment.');
@endphp

<nav
    class="mobile-tab-bar md:hidden {{ $lockVisibility ? 'mobile-tab-bar--locked' : '' }}"
    data-mobile-tab-bar
    data-lock-visibility="{{ $lockVisibility ? 'true' : 'false' }}"
    aria-label="{{ data_get($tabBarLabels, 'sr_label', __('navbar.mobile_nav_label')) }}"
    aria-busy="false"
>
    <div class="mobile-tab-bar__loading-line" role="status" aria-live="polite">
        <span class="sr-only">{{ $loaderLabel }} — {{ $loaderHint }}</span>
    </div>
    <div class="mobile-tab-bar__inner">
        @foreach ($tabItems as $tab)
            <a href="{{ $tab['href'] }}"
               class="mobile-tab-bar__item {{ $tab['active'] ? 'is-active' : '' }}"
               @if ($tab['active']) aria-current="page" @endif>
                <span class="mobile-tab-bar__icon" aria-hidden="true">
                    <i class="{{ $tab['icon'] }}"></i>
                </span>
                <span class="mobile-tab-bar__label">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>

@once
    @push('scripts')
        <script>
            (() => {
                const init = () => {
                    if (typeof window === 'undefined' || typeof document === 'undefined') {
                        return;
                    }
                    if (window.__mobileTabBarScrollVisibilityInit) {
                        return;
                    }
                    const nav = document.querySelector('[data-mobile-tab-bar]');
                    if (!nav) {
                        return;
                    }
                    window.__mobileTabBarScrollVisibilityInit = true;

                    const locked = nav.dataset.lockVisibility === 'true';
                    if (locked) {
                        nav.classList.remove('mobile-tab-bar--hidden');
                        nav.setAttribute('aria-busy', 'false');
                        return;
                    }

                    const mediaQuery = window.matchMedia('(max-width: 768px)');
                    const getScrollY = () =>
                        window.scrollY ??
                        window.pageYOffset ??
                        document.documentElement?.scrollTop ??
                        0;

                    const MIN_DELTA = 6;
                    const HIDE_OFFSET = 72;
                    let lastScrollY = getScrollY();
                    let ticking = false;

                    const isMobile = () => !mediaQuery || mediaQuery.matches;
                    const reveal = () => nav.classList.remove('mobile-tab-bar--hidden');

                    const updateVisibility = () => {
                        ticking = false;
                        const currentY = Math.max(getScrollY(), 0);

                        if (!isMobile()) {
                            reveal();
                            lastScrollY = currentY;
                            return;
                        }

                        if (Math.abs(currentY - lastScrollY) < MIN_DELTA) {
                            return;
                        }

                        const scrollingDown = currentY > lastScrollY;
                        if (scrollingDown && currentY > HIDE_OFFSET) {
                            nav.classList.add('mobile-tab-bar--hidden');
                        } else {
                            reveal();
                        }

                        lastScrollY = currentY;
                    };

                    const requestFrame =
                        window.requestAnimationFrame ?? ((cb) => window.setTimeout(cb, 16));

                    const handleScroll = () => {
                        if (!ticking) {
                            ticking = true;
                            requestFrame(updateVisibility);
                        }
                    };

                    const handleResize = () => {
                        lastScrollY = getScrollY();
                        if (!isMobile()) {
                            reveal();
                        } else {
                            updateVisibility();
                        }
                    };

                    window.addEventListener('scroll', handleScroll, { passive: true });
                    window.addEventListener('resize', handleResize);
                    updateVisibility();
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init, { once: true });
                } else {
                    init();
                }
            })();
        </script>
    @endpush
@endonce

