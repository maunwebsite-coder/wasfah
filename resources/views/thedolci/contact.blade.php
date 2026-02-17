@extends('thedolci.layouts.store')

@section('title', 'Contact | thedolci')

@section('content')
@php
    $whatsappRaw = (string) config('services.whatsapp_booking.number', '962790553680');
    $whatsappDigits = preg_replace('/\D+/', '', $whatsappRaw) ?: '962790553680';
    $instagramHandle = '@thedolci.jo';
    $instagramUrl = 'https://www.instagram.com/thedolci.jo/';
@endphp
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <h1>Contact Us</h1>
        <ul class="dolci-contact-list">
            <li>
                WhatsApp:
                <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">
                    +{{ $whatsappDigits }}
                </a>
            </li>
            <li>
                Instagram:
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener">
                    {{ $instagramHandle }}
                </a>
            </li>
        </ul>
    </div>
</section>
@endsection
