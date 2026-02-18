@php
    $homeActive = request()->routeIs('home');
    $shopActive = request()->routeIs('thedolci.shop') || request()->routeIs('thedolci.product');
    $seasonalActive = request()->routeIs('thedolci.seasonal');
    $aboutActive = request()->routeIs('about');
    $reviewsActive = request()->routeIs('thedolci.reviews');
    $loyaltyActive = request()->routeIs('thedolci.loyalty');
    $faqActive = request()->routeIs('thedolci.faq');
    $contactActive = request()->routeIs('contact');
    $trackActive = request()->routeIs('thedolci.track-order');
    $cartActive = request()->routeIs('thedolci.cart')
        || request()->routeIs('thedolci.checkout')
        || request()->routeIs('thedolci.order.success')
        || request()->routeIs('thedolci.track-order');
@endphp

<header class="dolci-topbar-v2" data-dolci-topbar>
    <div class="dolci-container">
        <div class="dolci-topbar-shell">
            <a href="{{ route('home') }}" class="dolci-logo dolci-logo-v2" aria-label="thedolci home">
                <span class="dolci-logo-word">thedolci</span>
                <span class="dolci-logo-tag">premium tiramisu</span>
            </a>

            <nav class="dolci-primary-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}" class="dolci-nav-link {{ $homeActive ? 'is-active' : '' }}" @if($homeActive) aria-current="page" @endif>Home</a>
                <a href="{{ route('thedolci.shop') }}" class="dolci-nav-link {{ $shopActive ? 'is-active' : '' }}" @if($shopActive) aria-current="page" @endif>Shop</a>
                <a href="{{ route('thedolci.seasonal') }}" class="dolci-nav-link {{ $seasonalActive ? 'is-active' : '' }}" @if($seasonalActive) aria-current="page" @endif>Seasonal</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('about') }}" class="dolci-nav-link {{ $aboutActive ? 'is-active' : '' }}" @if($aboutActive) aria-current="page" @endif>Story</a>
                    @endif
                @endauth
                <a href="{{ route('thedolci.reviews') }}" class="dolci-nav-link {{ $reviewsActive ? 'is-active' : '' }}" @if($reviewsActive) aria-current="page" @endif>Reviews</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('thedolci.loyalty') }}" class="dolci-nav-link {{ $loyaltyActive ? 'is-active' : '' }}" @if($loyaltyActive) aria-current="page" @endif>Loyalty</a>
                    @endif
                @endauth
            </nav>

            <div class="dolci-topbar-tools">
                <a href="{{ route('thedolci.track-order') }}" class="dolci-track-btn {{ $trackActive ? 'is-active' : '' }}" @if($trackActive) aria-current="page" @endif>
                    Track
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a class="dolci-user-link" href="{{ route('thedolci.admin.dashboard') }}">Admin</a>
                    @endif
                @endauth
                <a href="{{ route('thedolci.cart') }}" class="dolci-cart-btn {{ $cartActive ? 'is-active' : '' }}" aria-label="Cart" @if($cartActive) aria-current="page" @endif>
                    <span>Cart</span>
                    <span class="dolci-cart-count">{{ $cartCount ?? 0 }}</span>
                </a>
                <button type="button" class="dolci-menu-toggle" data-dolci-menu-toggle aria-expanded="false" aria-controls="dolci-mobile-drawer">
                    Menu
                </button>
            </div>
        </div>

        <div id="dolci-mobile-drawer" class="dolci-mobile-drawer" data-dolci-mobile-drawer aria-hidden="true">
            <button type="button" class="dolci-mobile-drawer-backdrop" data-dolci-mobile-close aria-label="Close menu"></button>
            <div class="dolci-mobile-drawer-panel" role="dialog" aria-modal="true" aria-label="Mobile menu">
                <div class="dolci-mobile-drawer-head">
                    <span>Menu</span>
                    <button type="button" class="dolci-mobile-close" data-dolci-mobile-close aria-label="Close menu">Close</button>
                </div>
                <nav class="dolci-mobile-nav-grid" aria-label="Mobile navigation">
                    <a href="{{ route('home') }}" class="dolci-mobile-link {{ $homeActive ? 'is-active' : '' }}" @if($homeActive) aria-current="page" @endif>Home</a>
                    <a href="{{ route('thedolci.shop') }}" class="dolci-mobile-link {{ $shopActive ? 'is-active' : '' }}" @if($shopActive) aria-current="page" @endif>Shop</a>
                    <a href="{{ route('thedolci.seasonal') }}" class="dolci-mobile-link {{ $seasonalActive ? 'is-active' : '' }}" @if($seasonalActive) aria-current="page" @endif>Seasonal</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('about') }}" class="dolci-mobile-link {{ $aboutActive ? 'is-active' : '' }}" @if($aboutActive) aria-current="page" @endif>Story</a>
                        @endif
                    @endauth
                    <a href="{{ route('thedolci.reviews') }}" class="dolci-mobile-link {{ $reviewsActive ? 'is-active' : '' }}" @if($reviewsActive) aria-current="page" @endif>Reviews</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('thedolci.loyalty') }}" class="dolci-mobile-link {{ $loyaltyActive ? 'is-active' : '' }}" @if($loyaltyActive) aria-current="page" @endif>Loyalty</a>
                        @endif
                    @endauth
                    <a href="{{ route('thedolci.faq') }}" class="dolci-mobile-link {{ $faqActive ? 'is-active' : '' }}" @if($faqActive) aria-current="page" @endif>FAQ</a>
                    <a href="{{ route('contact') }}" class="dolci-mobile-link {{ $contactActive ? 'is-active' : '' }}" @if($contactActive) aria-current="page" @endif>Contact</a>
                    <a href="{{ route('thedolci.track-order') }}" class="dolci-mobile-link {{ $trackActive ? 'is-active' : '' }}" @if($trackActive) aria-current="page" @endif>Track order</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('thedolci.admin.dashboard') }}" class="dolci-mobile-link">Admin</a>
                        @endif
                    @endauth
                    <a href="{{ route('thedolci.cart') }}" class="dolci-mobile-link dolci-mobile-link-cart {{ $cartActive ? 'is-active' : '' }}" @if($cartActive) aria-current="page" @endif>
                        Cart
                        <span class="dolci-cart-count">{{ $cartCount ?? 0 }}</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</header>

