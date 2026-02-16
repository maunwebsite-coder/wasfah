@extends('thedolci.layouts.store')

@section('title', 'Shop | thedolci')
@section('body_class', 'dolci-page-shop')

@section('content')
@php
    $shopCollection = collect($products);
    $shopTotal = $shopCollection->count();
    $shopBest = $shopCollection->where('is_best_seller', true)->count();
    $shopSeasonal = $shopCollection->where('is_seasonal', true)->count();
@endphp
<section class="dolci-section dolci-section-tight dolci-shop-section">
    <div class="dolci-container">
        <div class="dolci-shop-hero">
            <p class="dolci-kicker">Curated Menu</p>
            <h1>Shop Tiramisu</h1>
            <p>Choose from our signature line, best-sellers, and limited seasonal drops.</p>
            <div class="dolci-shop-hero-stats">
                <article><strong>{{ $shopTotal }}</strong><span>Available flavors</span></article>
                <article><strong>{{ $shopBest }}</strong><span>Best-sellers</span></article>
                <article><strong>{{ $shopSeasonal }}</strong><span>Seasonal specials</span></article>
            </div>
        </div>
        <div class="dolci-section-head dolci-page-intro">
            <h2>Browse Collection</h2>
            <div class="dolci-filter-row">
                <a class="{{ $activeFilter === 'all' ? 'active' : '' }}" href="{{ route('thedolci.shop') }}">All</a>
                <a class="{{ $activeFilter === 'best-sellers' ? 'active' : '' }}" href="{{ route('thedolci.shop', ['filter' => 'best-sellers']) }}">Best Sellers</a>
                <a class="{{ $activeFilter === 'seasonal' ? 'active' : '' }}" href="{{ route('thedolci.shop', ['filter' => 'seasonal']) }}">Seasonal</a>
            </div>
        </div>

        <div class="dolci-product-grid">
            @forelse($products as $product)
                @php
                    $sizePrices = $product['size_prices'] ?? [];
                    $firstSize = array_key_first($sizePrices);
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
                        <p>{{ $product['description'] }}</p>
                        <div class="dolci-product-card-bottom">
                            <span>From JOD {{ number_format((float)($sizePrices[$firstSize] ?? 0), 2) }}</span>
                            <a href="{{ route('thedolci.product', $product['slug']) }}" class="dolci-btn dolci-btn-compact">Customize</a>
                        </div>
                    </div>
                </article>
            @empty
                <p>No products found.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection

