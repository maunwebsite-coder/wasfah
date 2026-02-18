@extends('thedolci.layouts.store')

@section('title', 'Contact | thedolci')
@section('body_class', 'dolci-page-contact')

@section('content')
@php
    $whatsappRaw = (string) config('services.whatsapp_booking.number', '962790553680');
    $whatsappDigits = preg_replace('/\D+/', '', $whatsappRaw) ?: '962790553680';
    $formattedWhatsapp = '+' . trim(chunk_split($whatsappDigits, 3, ' '));
    $whatsappMessage = rawurlencode('Hi thedolci team, I want help with my order.');
    $whatsappUrl = "https://wa.me/{$whatsappDigits}?text={$whatsappMessage}";
    $instagramHandle = '@thedolci.jo';
    $instagramUrl = 'https://www.instagram.com/thedolci.jo/';
@endphp
<section class="dolci-section dolci-section-tight dolci-contact-section">
    <div class="dolci-container">
        <div class="dolci-contact-hero">
            <p class="dolci-kicker">Let's Talk</p>
            <h1>Contact thedolci</h1>
            <p>
                For order help, delivery updates, custom boxes, and event desserts, choose your preferred channel below.
            </p>
            <div class="dolci-contact-hero-actions">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="dolci-btn dolci-btn-primary">
                    Chat on WhatsApp
                </a>
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="dolci-btn dolci-btn-secondary">
                    Open Instagram
                </a>
            </div>
        </div>

        <div class="dolci-contact-grid">
            <article class="dolci-contact-card dolci-contact-card-whatsapp">
                <span class="dolci-contact-chip">Fastest support</span>
                <h2>WhatsApp</h2>
                <p>Ideal for urgent updates, order edits, and confirmation checks.</p>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="dolci-contact-value">
                    {{ $formattedWhatsapp }}
                </a>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="dolci-contact-link">
                    Start chat now
                </a>
            </article>

            <article class="dolci-contact-card dolci-contact-card-instagram">
                <span class="dolci-contact-chip">Daily inspiration</span>
                <h2>Instagram</h2>
                <p>Browse new flavors, seasonal drops, and customer favorites.</p>
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="dolci-contact-value">
                    {{ $instagramHandle }}
                </a>
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="dolci-contact-link">
                    Visit profile
                </a>
            </article>
        </div>

        <div class="dolci-contact-notes">
            <article class="dolci-contact-note">
                <h3>Order support</h3>
                <p>Share your order number to receive the fastest update.</p>
            </article>
            <article class="dolci-contact-note">
                <h3>Custom requests</h3>
                <p>Tell us your preferred date, quantity, and special notes.</p>
            </article>
            <article class="dolci-contact-note">
                <h3>Events and gifting</h3>
                <p>We can help with elegant dessert boxes for gatherings and gifts.</p>
            </article>
        </div>
    </div>
</section>
@endsection
