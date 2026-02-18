@extends('thedolci.layouts.store')

@section('title', 'About thedolci')
@section('body_class', 'dolci-page-about')

@section('content')
<section class="dolci-section dolci-section-tight dolci-about-hero">
    <div class="dolci-container dolci-split">
        <div class="dolci-panel">
            <p class="dolci-kicker">Our Story</p>
            <h1>From Italian Tradition to Modern Cloud Kitchen</h1>
            <p>
                thedolci was created to deliver a premium tiramisu experience with consistency, elegance, and authentic flavor.
                Our cloud kitchen model allows us to focus on what matters most: freshness, quality, and exceptional dessert craftsmanship.
            </p>
            <p>
                Every batch is made daily using premium mascarpone, fine cocoa, and carefully balanced sweetness levels for every taste profile.
            </p>
        </div>
        <div class="dolci-about-media">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80" alt="Behind the scenes at thedolci" class="dolci-rounded-img">
        </div>
    </div>
</section>

<section class="dolci-section dolci-about-values">
    <div class="dolci-container">
        <div class="dolci-three-cols">
            <article>
                <h3>Quality Commitment</h3>
                <p>Strict ingredient standards and daily preparation cycles for premium freshness.</p>
            </article>
            <article>
                <h3>Elegant Packaging</h3>
                <p>Luxury-ready boxes with optional personalization for gifting and special events.</p>
            </article>
            <article>
                <h3>Reliable Delivery</h3>
                <p>Pickup or delivery with scheduled time slots built for convenience and precision.</p>
            </article>
        </div>
    </div>
</section>

<section class="dolci-section dolci-about-journey">
    <div class="dolci-container">
        <div class="dolci-section-head dolci-page-intro">
            <h2>Our Journey</h2>
            <p>Built step by step to deliver a luxury dessert experience with cloud-kitchen speed.</p>
        </div>
        <div class="dolci-about-timeline">
            <article>
                <span>Step 1</span>
                <h3>Recipe Development</h3>
                <p>Italian-inspired formulas tested for balance, texture, and consistency.</p>
            </article>
            <article>
                <span>Step 2</span>
                <h3>Premium Sourcing</h3>
                <p>Ingredient selection focused on authentic flavor and reliable quality.</p>
            </article>
            <article>
                <span>Step 3</span>
                <h3>Daily Fresh Prep</h3>
                <p>Small-batch production with strict prep windows to protect freshness.</p>
            </article>
            <article>
                <span>Step 4</span>
                <h3>Elegant Delivery</h3>
                <p>Careful packaging and scheduled fulfillment for a polished final experience.</p>
            </article>
        </div>
    </div>
</section>
@endsection

