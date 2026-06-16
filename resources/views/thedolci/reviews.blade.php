@extends('thedolci.layouts.store')

@section('title', 'Reviews | thedolci')
@section('body_class', 'dolci-page-reviews')

@section('content')
@php
    $reviewCollection = collect($reviews);
    $reviewCount = $reviewCollection->count();
@endphp
<section class="dolci-section dolci-section-tight dolci-reviews-section">
    <div class="dolci-container">
        <div class="dolci-reviews-hero">
            <p class="dolci-kicker">Guest Feedback</p>
            <h1>Customer Reviews</h1>
            <p>Trusted by dessert lovers for flavor, freshness, and premium presentation.</p>
            <div class="dolci-reviews-hero-stats">
                <article><strong>{{ $reviewCount }}</strong><span>Total reviews</span></article>
            </div>
        </div>
        <div class="dolci-section-head dolci-page-intro">
            <h2>What They Loved</h2>
            <p>Real feedback from customers after each order.</p>
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
@endsection

