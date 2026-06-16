@extends('thedolci.layouts.store')

@section('title', 'Seasonal Collection | thedolci')
@section('body_class', 'dolci-page-seasonal')

@section('content')
@php
    $seasonalCollection = collect($seasonalProducts);
    $seasonalTotal = $seasonalCollection->count();
    $preorderTotal = $seasonalCollection->where('preorder_enabled', true)->count();
    $limitedQtySum = (int) $seasonalCollection->sum(function ($item) {
        return (int) ($item['limited_quantity'] ?? 0);
    });
@endphp
<section class="dolci-section dolci-section-tight dolci-seasonal-section">
    <div class="dolci-container">
        <div class="dolci-seasonal-hero">
            <p class="dolci-kicker">Limited Collection</p>
            <h1>Seasonal & Limited Products</h1>
            <p>Small-batch releases available for a short time, crafted for special occasions.</p>
            <div class="dolci-seasonal-hero-stats">
                <article><strong>{{ $seasonalTotal }}</strong><span>Seasonal items</span></article>
                <article><strong>{{ $preorderTotal }}</strong><span>Open for pre-order</span></article>
                <article><strong>{{ $limitedQtySum }}</strong><span>Estimated boxes left</span></article>
            </div>
        </div>
        <div class="dolci-section-head dolci-page-intro">
            <h2>Current Drop</h2>
            <p>Reserve your box before this batch ends.</p>
        </div>

        <div class="dolci-product-grid">
            @forelse($seasonalProducts as $product)
                <article class="dolci-product-card">
                    <a href="{{ route('thedolci.product', $product['slug']) }}">
                        <img src="{{ $product['cover_image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <div class="dolci-product-card-body">
                        <div class="dolci-product-tags">
                            <span>Seasonal</span>
                            @if(!empty($product['show_limited_edition']))<span>Limited Edition</span>@endif
                            @if(!empty($product['preorder_enabled']))<span>Pre-order</span>@endif
                        </div>
                        <h3>{{ $product['name'] }}</h3>
                        <p>{{ $product['description'] }}</p>
                        @if(!empty($product['limited_quantity']))
                            <p class="dolci-limited">Remaining batch: {{ $product['limited_quantity'] }}</p>
                        @endif
                        @if(!empty($product['seasonal_ends_at']))
                            <div class="dolci-countdown-inline" data-countdown data-end="{{ $product['seasonal_ends_at'] }}">
                                <p>Ends in <strong>--d --h --m --s</strong></p>
                            </div>
                        @endif
                        <a href="{{ route('thedolci.product', $product['slug']) }}" class="dolci-btn dolci-btn-primary">Pre-order Now</a>
                    </div>
                </article>
            @empty
                <p>No seasonal products at the moment.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection

