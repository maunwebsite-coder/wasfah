@extends('thedolci.layouts.store')

@section('title', 'thedolci | Premium Tiramisu')
@section('body_class', 'dolci-page-home')

@section('content')
<section class="dolci-hero dolci-hero-home">
    <div class="dolci-container dolci-hero-grid">
        <div class="dolci-hero-copy dolci-panel">
            <p class="dolci-kicker">{{ $hero['kicker'] ?? '' }}</p>
            <h1>{{ $hero['title'] ?? '' }}</h1>
            <p>{{ $hero['description'] ?? '' }}</p>
            <div class="dolci-hero-actions">
                <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-primary">{{ $hero['primary_button_text'] ?? '' }}</a>
                <a href="{{ route('thedolci.seasonal') }}" class="dolci-btn dolci-btn-secondary">{{ $hero['secondary_button_text'] ?? '' }}</a>
            </div>
            <div class="dolci-hero-metrics">
                <article>
                    <strong>{{ $hero['metric_1_title'] ?? '' }}</strong>
                    <span>{{ $hero['metric_1_subtitle'] ?? '' }}</span>
                </article>
                <article>
                    <strong>{{ $hero['metric_2_title'] ?? '' }}</strong>
                    <span>{{ $hero['metric_2_subtitle'] ?? '' }}</span>
                </article>
                <article>
                    <strong>{{ $hero['metric_3_title'] ?? '' }}</strong>
                    <span>{{ $hero['metric_3_subtitle'] ?? '' }}</span>
                </article>
            </div>
        </div>
        <div class="dolci-hero-media">
            <img src="{{ $hero['image_url'] ?? '' }}" alt="{{ $hero['image_alt'] ?? '' }}" loading="eager">
        </div>
    </div>
</section>

<section class="dolci-section dolci-home-products">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>Our Products</h2>
            <a href="{{ route('thedolci.shop') }}">View full menu</a>
        </div>

        <div class="dolci-product-grid">
            @foreach($products as $product)
                @php
                    $sizePrices = $product['size_prices'] ?? [];
                    $firstSizeKey = array_key_first($sizePrices);
                @endphp
                <article class="dolci-product-card">
                    <a href="{{ route('thedolci.product', $product['slug']) }}">
                        <img src="{{ $product['cover_image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <div class="dolci-product-card-body">
                        <div class="dolci-product-tags">
                            @if(!empty($product['is_best_seller']))<span>Best Seller</span>@endif
                            @if(!empty($product['is_seasonal']))<span>Seasonal</span>@endif
                            @if(!empty($product['show_limited_edition']))<span>Limited Edition</span>@endif
                        </div>
                        <h3>{{ $product['name'] }}</h3>
                        <p>{{ $product['headline'] }}</p>
                        <div class="dolci-product-card-bottom">
                            <span>From JOD {{ number_format((float)($sizePrices[$firstSizeKey] ?? 0), 2) }}</span>
                            <a href="{{ route('thedolci.product', $product['slug']) }}" class="dolci-btn dolci-btn-compact">Customize</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@if($featuredSeasonal)
<section class="dolci-section dolci-home-seasonal">
    <div class="dolci-container">
        <div class="dolci-seasonal-banner">
            <div>
                <span class="dolci-badge">Limited Edition</span>
                <h2>{{ $featuredSeasonal['name'] }}</h2>
                <p>{{ $featuredSeasonal['description'] }}</p>
                <div class="dolci-seasonal-meta">
                    @if(!empty($featuredSeasonal['limited_quantity']))
                        <span class="dolci-pill">Only {{ $featuredSeasonal['limited_quantity'] }} boxes</span>
                    @endif
                    @if(!empty($featuredSeasonal['preorder_enabled']))
                        <span class="dolci-pill">Pre-order available</span>
                    @endif
                </div>
            </div>
            <div class="dolci-countdown" data-countdown data-end="{{ $featuredSeasonal['seasonal_ends_at'] ?? '' }}">
                <p>Ends in:</p>
                <strong>--d --h --m --s</strong>
                <a href="{{ route('thedolci.product', $featuredSeasonal['slug']) }}" class="dolci-btn dolci-btn-primary">Pre-order Now</a>
            </div>
        </div>
    </div>
</section>
@endif

<section class="dolci-section dolci-home-bestsellers">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>Best Sellers</h2>
            <a href="{{ route('thedolci.shop', ['filter' => 'best-sellers']) }}">View all</a>
        </div>

        <div class="dolci-product-grid">
            @foreach($bestSellers as $product)
                @php
                    $sizePrices = $product['size_prices'] ?? [];
                    $firstSizeKey = array_key_first($sizePrices);
                @endphp
                <article class="dolci-product-card">
                    <a href="{{ route('thedolci.product', $product['slug']) }}">
                        <img src="{{ $product['cover_image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <div class="dolci-product-card-body">
                        <h3>{{ $product['name'] }}</h3>
                        <p>{{ $product['headline'] }}</p>
                        <div class="dolci-product-card-bottom">
                            <span>From JOD {{ number_format((float)($sizePrices[$firstSizeKey] ?? 0), 2) }}</span>
                            <a href="{{ route('thedolci.product', $product['slug']) }}" class="dolci-btn dolci-btn-compact">Customize</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="dolci-section dolci-home-trust">
    <div class="dolci-container">
        <div class="dolci-trust-row" data-trust-slider>
            <div data-trust-slide><strong>Fresh Daily</strong><span>Small-batch production</span></div>
            <div data-trust-slide><strong>Premium Ingredients</strong><span>Authentic mascarpone and cocoa</span></div>
            <div data-trust-slide><strong>Flexible Delivery</strong><span>Delivery or pickup scheduling</span></div>
            <div data-trust-slide><strong>WhatsApp Confirmation</strong><span>Pay via Click after placing order</span></div>
        </div>
        <div class="dolci-trust-dots" aria-label="Trust highlights slider navigation">
            <button type="button" class="is-active" data-trust-dot="0" aria-label="Fresh Daily" aria-current="true"></button>
            <button type="button" data-trust-dot="1" aria-label="Premium Ingredients"></button>
            <button type="button" data-trust-dot="2" aria-label="Flexible Delivery"></button>
            <button type="button" data-trust-dot="3" aria-label="WhatsApp Confirmation"></button>
        </div>
    </div>
</section>

<section class="dolci-section dolci-home-reviews">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>Customer Reviews</h2>
            <a href="{{ route('thedolci.reviews') }}">See all reviews</a>
        </div>

        <div class="dolci-review-grid">
            @foreach($reviews as $review)
                <article class="dolci-review-card">
                    <div class="dolci-stars">{{ str_repeat('*', (int)$review['rating']) }}</div>
                    <p>{{ $review['text'] }}</p>
                    <strong>{{ $review['name'] }}</strong>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="dolci-section dolci-home-social">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>{{ $instagram['section_title'] ?? 'From Instagram' }}</h2>
            <a href="{{ $instagram['profile_url'] ?? 'https://www.instagram.com/thedolci.jo/' }}" target="_blank" rel="noopener">
                {{ $instagram['handle'] ?? '@thedolci.jo' }}
            </a>
        </div>

        @php($instagramPostCount = $instagramPosts->count())

        <div class="dolci-instagram-grid" data-instagram-slider>
            @foreach($instagramPosts as $index => $post)
                <a href="{{ $post['url'] }}" target="_blank" rel="noopener" class="dolci-instagram-item" data-instagram-slide>
                    <img src="{{ $post['image'] }}" alt="{{ $post['caption'] ?? 'Instagram post' }}" loading="lazy">
                </a>
            @endforeach
        </div>

        @if($instagramPostCount > 1)
            <div class="dolci-trust-dots dolci-instagram-dots" aria-label="Instagram slider navigation">
                @foreach($instagramPosts as $index => $post)
                    <button
                        type="button"
                        data-instagram-dot="{{ $index }}"
                        aria-label="Instagram post {{ $index + 1 }}"
                        @class(['is-active' => $index === 0])
                        @if($index === 0) aria-current="true" @endif
                    ></button>
                @endforeach
            </div>
        @endif
    </div>
</section>

<div id="first-order-popup" class="dolci-popup" aria-hidden="true">
    <div class="dolci-popup-card">
        <button id="close-first-order-popup" class="dolci-popup-close" type="button" aria-label="Close">x</button>
        <p class="dolci-kicker">Welcome to thedolci</p>
        <h3>Fresh tiramisu made daily</h3>
        <p>Explore the menu and place your first order today.</p>
        <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-primary">Start Ordering</a>
    </div>
</div>
@endsection

