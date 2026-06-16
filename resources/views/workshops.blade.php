@extends('layouts.app')

@section('title', __('workshops.meta.title'))

@push('styles')
<style>
    body {
        background-color: #f9fafb;
    }
    .workshop-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .workshop-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .filter-btn.active {
        background-color: #6b2e30;
        color: white;
        border-color: #6b2e30;
    }
    /* Workshop Card Image Improvements */
    .workshop-card img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
        aspect-ratio: 16/9;
    }
    
    .workshop-card:hover img {
        transform: scale(1.05);
    }
    
    /* Mobile Responsive Images */
    @media (max-width: 768px) {
        .workshop-card img {
            height: 220px;
            object-fit: cover;
            object-position: center;
            min-height: 200px;
            max-height: 250px;
            aspect-ratio: 4/3;
        }
    }
    
    @media (max-width: 480px) {
        .workshop-card img {
            height: 200px;
            object-fit: cover;
            object-position: center;
            min-height: 180px;
            max-height: 220px;
            aspect-ratio: 4/3;
        }
    }
    
    /* Ensure images load properly */
    .workshop-card img {
        background-color: #f3f4f6;
        background-image: linear-gradient(45deg, #f3f4f6 25%, transparent 25%), 
                         linear-gradient(-45deg, #f3f4f6 25%, transparent 25%), 
                         linear-gradient(45deg, transparent 75%, #f3f4f6 75%), 
                         linear-gradient(-45deg, transparent 75%, #f3f4f6 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }

    /* Modern glass refresh */
    :root {
        --workshop-glass-surface: linear-gradient(145deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.62));
        --workshop-glass-border: rgba(255, 255, 255, 0.64);
        --workshop-glass-shadow: 0 26px 56px rgba(107, 46, 48, 0.14);
    }

    body {
        background:
            radial-gradient(circle at 10% 10%, rgba(143, 74, 80, 0.16), transparent 32%),
            radial-gradient(circle at 88% 8%, rgba(107, 46, 48, 0.12), transparent 36%),
            linear-gradient(180deg, #fffdfd 0%, #ffffff 46%, #fbfcff 100%);
        background-attachment: fixed;
    }

    .workshops-page {
        position: relative;
        overflow: hidden;
        background: transparent;
    }

    .workshops-page::before,
    .workshops-page::after {
        content: "";
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
        z-index: 0;
    }

    .workshops-page::before {
        inset-inline-start: -130px;
        inset-block-start: 10%;
        width: clamp(240px, 24vw, 360px);
        height: clamp(240px, 24vw, 360px);
        background: radial-gradient(circle, rgba(107, 46, 48, 0.1), rgba(107, 46, 48, 0));
    }

    .workshops-page::after {
        inset-inline-end: -140px;
        inset-block-end: 6%;
        width: clamp(240px, 26vw, 380px);
        height: clamp(240px, 26vw, 380px);
        background: radial-gradient(circle, rgba(143, 74, 80, 0.14), rgba(143, 74, 80, 0));
    }

    .workshops-page > * {
        position: relative;
        z-index: 1;
    }

    .workshops-featured-card {
        border: 1px solid rgba(255, 255, 255, 0.36);
        box-shadow: 0 30px 66px rgba(7, 9, 24, 0.24);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .workshops-featured-card > .grid > div:first-child {
        background: linear-gradient(140deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0));
    }

    .workshops-filters-section {
        padding-top: 1.5rem;
        padding-bottom: 1.75rem;
    }

    .workshops-filter-panel {
        width: fit-content;
        margin-inline: auto;
        padding: 0.6rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.52));
        border: 1px solid rgba(255, 255, 255, 0.64);
        box-shadow: 0 18px 40px rgba(107, 46, 48, 0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .filter-btn {
        color: #334155;
        border-color: rgba(107, 46, 48, 0.14);
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .filter-btn:hover {
        border-color: rgba(107, 46, 48, 0.34);
        color: #6b2e30;
        transform: translateY(-1px);
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #6b2e30, #8f4a50);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 14px 28px rgba(107, 46, 48, 0.3);
    }

    .workshops-list-section {
        background:
            radial-gradient(circle at 14% 0%, rgba(143, 74, 80, 0.11), transparent 32%),
            linear-gradient(180deg, rgba(245, 248, 255, 0.66), rgba(255, 255, 255, 0.95));
    }

    .workshops-grid .workshop-card {
        background: var(--workshop-glass-surface);
        border: 1px solid var(--workshop-glass-border);
        box-shadow: var(--workshop-glass-shadow);
        backdrop-filter: blur(12px) saturate(132%);
        -webkit-backdrop-filter: blur(12px) saturate(132%);
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .workshops-grid .workshop-card:hover {
        transform: translateY(-8px);
        border-color: rgba(107, 46, 48, 0.28);
        box-shadow: 0 30px 62px rgba(107, 46, 48, 0.2);
    }

    .workshops-grid .workshop-card > .relative::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(0, 0, 0, 0.1));
        pointer-events: none;
    }

    .workshop-price-chip {
        background: rgba(107, 46, 48, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.34);
        box-shadow: 0 14px 30px rgba(107, 46, 48, 0.3);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .workshops-grid .workshop-card .bg-gray-100 {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(107, 46, 48, 0.12);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .workshops-featured-card,
    .workshops-filter-panel,
    .workshops-grid .workshop-card {
        animation: workshops-fade-up 0.68s cubic-bezier(0.22, 0.61, 0.36, 1) both;
    }

    .workshops-filter-panel {
        animation-delay: 0.08s;
    }

    .workshops-grid .workshop-card {
        animation-delay: 0.14s;
    }

    @keyframes workshops-fade-up {
        0% {
            opacity: 0;
            transform: translate3d(0, 12px, 0) scale(0.99);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @media (max-width: 768px) {
        .workshops-filter-panel {
            width: 100%;
            border-radius: 1.1rem;
            justify-content: flex-start;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .workshops-filter-panel::-webkit-scrollbar {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .workshops-featured-card,
        .workshops-filter-panel,
        .workshops-grid .workshop-card {
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $showAdminMetrics = auth()->check() && auth()->user()->isAdmin();
    $whatsappBookingEnabled = data_get($whatsappBookingConfig ?? [], 'enabled', false);
    $whatsappBookingPayload = [
        'isLoggedIn' => data_get($whatsappBookingConfig ?? [], 'isLoggedIn', false),
        'whatsappNumber' => data_get($whatsappBookingConfig ?? [], 'number'),
        'bookingEndpoint' => data_get($whatsappBookingConfig ?? [], 'bookingEndpoint'),
        'bookingNotes' => data_get($whatsappBookingConfig ?? [], 'notes'),
        'loginUrl' => data_get($whatsappBookingConfig ?? [], 'loginUrl'),
        'registerUrl' => data_get($whatsappBookingConfig ?? [], 'registerUrl'),
        'user' => data_get($whatsappBookingConfig ?? [], 'user', []),
    ];
    $cardPlaceholderUrl = sprintf(
        'https://placehold.co/600x400/f87171/FFFFFF?text=%s',
        urlencode(__('workshops.labels.card_placeholder_text'))
    );
    $featuredPlaceholderUrl = sprintf(
        'https://placehold.co/800x600/f87171/FFFFFF?text=%s',
        urlencode(__('workshops.labels.featured_placeholder_text'))
    );
@endphp
<div class="min-h-screen bg-gray-50 workshops-page">

    <!-- Featured Workshop -->
    @if($featuredWorkshop)
    @php 
        $featuredIsFull = $featuredWorkshop->bookings_count >= $featuredWorkshop->max_participants; 
        $featuredIsRegistrationClosed = !$featuredWorkshop->is_registration_open;
        $featuredIsCompleted = $featuredWorkshop->is_completed;
        $featuredLocationLabel = $featuredWorkshop->is_online
            ? __('workshops.labels.online_workshop')
            : ($featuredWorkshop->location ?? __('workshops.labels.offline_workshop'));
        $featuredDeadlineLabel = $featuredWorkshop->registration_deadline
            ? $featuredWorkshop->registration_deadline->format('d/m/Y')
            : __('workshops.labels.unspecified');
        $featuredInstructor = $featuredWorkshop->instructor ?? __('workshops.labels.unspecified');
        $featuredIsBooked = !empty($bookedWorkshopIds) && in_array($featuredWorkshop->id, $bookedWorkshopIds, true);
        $featuredPriceLabel = trim($featuredWorkshop->formatted_price ?? ($featuredWorkshop->price.' '.$featuredWorkshop->currency));
        $featuredDateLabel = $featuredWorkshop->start_date
            ? $featuredWorkshop->start_date->format('d/m/Y h:i A')
            : __('workshops.labels.unspecified');
    @endphp
    <section class="container mx-auto px-4 pt-10 md:pt-16 relative z-20 workshops-featured-section">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-3xl overflow-hidden shadow-2xl workshops-featured-card">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                <!-- Text content -->
                <div class="p-5 sm:p-8 lg:p-12 text-white flex flex-col justify-center">
                    <div class="mb-5 sm:mb-6">
                        <span class="bg-white/20 text-white text-sm font-semibold px-3.5 py-1.5 rounded-full inline-block mb-3.5 sm:mb-4">
                            {{ __('workshops.featured.badge') }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 sm:mb-4 leading-tight">
                            {{ $featuredWorkshop->title }}
                        </h2>
                        <p class="text-base sm:text-lg text-amber-100 mb-5 sm:mb-6 leading-relaxed">
                            {{ $featuredWorkshop->featured_description ?: $featuredWorkshop->description }}
                        </p>
                    </div>
                    
                    <!-- Workshop details -->
                    <div class="space-y-1.5 sm:space-y-3 mb-6 sm:mb-8">
                        <div class="flex items-center text-amber-100 gap-2.5 sm:gap-3">
                            <i class="fas fa-calendar-alt w-5 text-center shrink-0"></i>
                            <span class="font-medium">{{ $featuredWorkshop->start_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center text-amber-100 gap-2.5 sm:gap-3">
                            <i class="fas {{ $featuredWorkshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }} w-5 text-center shrink-0"></i>
                            <span class="font-medium">{{ $featuredLocationLabel }}</span>
                        </div>
                        <div class="flex items-center text-amber-100 gap-2.5 sm:gap-3">
                            <i class="fas fa-user w-5 text-center shrink-0"></i>
                            <span class="font-medium">{{ __('workshops.labels.with') }} {{ $featuredInstructor }}</span>
                        </div>
                        @if($showAdminMetrics)
                            <div class="flex items-center text-amber-100 gap-2.5 sm:gap-3">
                                <i class="fas fa-users w-5 text-center shrink-0"></i>
                                <span class="font-medium">{{ $featuredWorkshop->bookings_count }}/{{ $featuredWorkshop->max_participants }} {{ __('workshops.labels.participants') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        @if($featuredIsCompleted)
                            <button type="button" class="bg-gray-400 text-gray-600 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-circle mr-2 rtl:ml-2 text-xl"></i>
                                {{ __('workshops.featured.completed') }}
                            </button>
                        @elseif($featuredIsBooked)
                            <button type="button" class="bg-green-500 text-white font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg" disabled>
                                <i class="fas fa-check mr-2 rtl:ml-2 text-xl booking-button-icon"></i>
                                <span class="booking-button-label">{{ __('workshops.featured.booked') }}</span>
                            </button>
                        @elseif($featuredIsFull)
                            <button type="button" class="bg-gray-400 text-gray-600 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg">
                                <i class="fas fa-lock mr-2 rtl:ml-2 text-xl"></i>
                                {{ __('workshops.featured.full') }}
                            </button>
                        @elseif($featuredIsRegistrationClosed)
                            <button type="button" class="bg-yellow-400 text-yellow-800 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl cursor-not-allowed flex items-center justify-center shadow-lg">
                                <i class="fas fa-clock mr-2 rtl:ml-2 text-xl"></i>
                                {{ __('workshops.featured.closed') }}
                            </button>
                        @else
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 flex-1">
                                <a href="{{ route('workshop.show', $featuredWorkshop->slug) }}#workshop-booking"
                                   class="flex-1 bg-white text-amber-600 hover:bg-amber-50 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl">
                                    <i class="fas fa-calendar-check mr-2 rtl:ml-2 text-xl booking-button-icon"></i>
                                    <span class="booking-button-label">{{ __('workshops.featured.primary_cta') }}</span>
                                </a>
                            </div>
                        @endif
                        <a href="{{ route('workshop.show', $featuredWorkshop->slug) }}" 
                           class="border-2 border-white text-white hover:bg-white hover:text-amber-600 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-info-circle mr-2 rtl:ml-2"></i>
                            {{ __('workshops.featured.secondary_cta') }}
                        </a>
                    </div>
                </div>
                
                <!-- Hero image -->
                <div class="relative h-48 sm:h-64 lg:h-auto">
                    <img src="{{ $featuredWorkshop->image ? asset('storage/' . $featuredWorkshop->image) : $featuredPlaceholderUrl }}" 
                         alt="{{ $featuredWorkshop->title }}" 
                         class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-l from-transparent to-amber-500/20"></div>
                </div>
            </div>
        </div>
    </section>
    @else
    <!-- No upcoming workshop message -->
    <section class="container mx-auto px-4 pt-10 md:pt-16 relative z-20 workshops-featured-section workshops-featured-section--empty">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-3xl overflow-hidden shadow-2xl workshops-featured-card">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                <!-- Text content -->
                <div class="p-5 sm:p-8 lg:p-12 text-white flex flex-col justify-center">
                    <div class="mb-5 sm:mb-6">
                        <span class="bg-white/20 text-white text-sm font-semibold px-3.5 py-1.5 rounded-full inline-block mb-3.5 sm:mb-4">
                            {{ __('workshops.featured.badge') }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 sm:mb-4 leading-tight">
                            {{ __('workshops.featured.no_upcoming_title') }}
                        </h2>
                        <p class="text-base sm:text-lg text-amber-100 mb-6 sm:mb-8 leading-relaxed">
                            {{ __('workshops.featured.no_upcoming_description') }}
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        <a href="{{ route('workshops') }}" 
                           class="bg-white text-amber-600 hover:bg-amber-50 font-bold py-2.5 px-5 sm:py-4 sm:px-8 rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl">
                            <i class="fas fa-list mr-2 rtl:ml-2 text-xl"></i>
                            {{ __('workshops.featured.no_upcoming_primary') }}
                        </a>
                    </div>
                </div>
                
                <!-- Illustration -->
                <div class="relative h-48 sm:h-64 lg:h-auto overflow-hidden flex items-center justify-center">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="w-36 h-36 sm:w-48 sm:h-48 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-5xl sm:text-8xl text-white/80"></i>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-l from-transparent to-amber-500/20"></div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Filters Section -->
    <section class="py-12 workshops-filters-section">
        <div class="container mx-auto px-4">
            <div id="filter-buttons" class="flex flex-wrap justify-center items-center gap-3 workshops-filter-panel">
                <button type="button" class="filter-btn active font-semibold px-5 py-2 border rounded-full transition-colors duration-300" data-filter="all">{{ __('workshops.filters.all') }}</button>
                <button type="button" class="filter-btn font-semibold px-5 py-2 border rounded-full transition-colors duration-300" data-filter="online">{{ __('workshops.filters.online') }}</button>
                <button type="button" class="filter-btn font-semibold px-5 py-2 border rounded-full transition-colors duration-300" data-filter="offline">{{ __('workshops.filters.offline') }}</button>
                <button type="button" class="filter-btn font-semibold px-5 py-2 border rounded-full transition-colors duration-300" data-filter="beginner">{{ __('workshops.filters.beginner') }}</button>
                <button type="button" class="filter-btn font-semibold px-5 py-2 border rounded-full transition-colors duration-300" data-filter="advanced">{{ __('workshops.filters.advanced') }}</button>
            </div>
        </div>
    </section>

    <!-- Workshops section -->
    <section class="py-5 bg-gradient-to-br from-gray-50 to-white workshops-list-section">
        <div class="container mx-auto px-4">
            <!-- Header Section -->
            <div class="text-center mb-12 workshops-list-header">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">{{ __('workshops.premium.title') }}</h2>
                <p class="text-gray-600 text-lg max-w-3xl mx-auto mb-6">{{ __('workshops.premium.subtitle') }}</p>
            </div>
            
            @if($workshops->count() > 0)
                <!-- Workshops Grid -->
                <div id="workshops-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 workshops-grid">
                    @foreach($workshops as $workshop)
                        @php 
                            $isFull = $workshop->bookings_count >= $workshop->max_participants; 
                            $isRegistrationClosed = !$workshop->is_registration_open;
                            $isCompleted = $workshop->is_completed;
                            $isBooked = !empty($bookedWorkshopIds) && in_array($workshop->id, $bookedWorkshopIds, true);
                            $bookingLocationLabel = $workshop->is_online
                                ? __('workshops.labels.online_workshop')
                                : ($workshop->location ?? __('workshops.labels.offline_workshop'));
                            $bookingDeadlineLabel = $workshop->registration_deadline
                                ? $workshop->registration_deadline->format('d/m/Y')
                                : __('workshops.labels.unspecified');
                            $bookingInstructor = $workshop->instructor ?? __('workshops.labels.unspecified');
                            $bookingButtonStateClasses = $isBooked
                                ? 'bg-green-500 text-white cursor-not-allowed'
                                : 'bg-green-500 hover:bg-green-600 text-white';
                            $bookingPriceLabel = trim($workshop->formatted_price ?? ($workshop->price.' '.$workshop->currency));
                            $bookingDateLabel = $workshop->start_date
                                ? $workshop->start_date->format('d/m/Y h:i A')
                                : __('workshops.labels.unspecified');
                        @endphp
                        <div class="workshop-card bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full" 
                             data-type="{{ $workshop->is_online ? 'online' : 'offline' }}" 
                             data-level="{{ \Illuminate\Support\Str::of($workshop->level)->lower() }}" 
                             data-category="{{ $workshop->category }}"
                             data-workshop-id="{{ $workshop->id }}">
                            <div class="relative">
                                <img src="{{ $workshop->image ? asset('storage/' . $workshop->image) : $cardPlaceholderUrl }}" 
                                     alt="{{ $workshop->title }}"
                                     onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ addslashes(__('workshops.labels.fallback_image_alt')) }}';" loading="lazy">
                                @if($isFull)
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                    <span class="text-white font-bold text-lg bg-red-500 px-4 py-2 rounded-full">{{ __('workshops.cards.overlay_full') }}</span>
                                </div>
                                @elseif($isRegistrationClosed)
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                    <span class="text-white font-bold text-lg bg-yellow-500 px-4 py-2 rounded-full">
                                        <i class="fas fa-clock mr-2 rtl:ml-2"></i>
                                        {{ __('workshops.cards.overlay_closed') }}
                                    </span>
                                </div>
                                @endif
                                <div class="absolute top-4 right-4 bg-orange-500 text-white text-sm font-semibold px-3 py-1 rounded-full workshop-price-chip">{{ $workshop->price }} {{ $workshop->currency }}</div>
                            </div>
                            
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="mb-2">
                                    <span class="text-sm font-semibold {{ $workshop->is_online ? 'text-blue-600' : 'text-green-600' }}">
                                        {{ $workshop->is_online ? __('workshops.cards.online_badge') : __('workshops.cards.onsite_badge') }}
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $workshop->title }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('workshops.labels.with') }} {{ $bookingInstructor }}</p>
                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="fas fa-calendar-alt mr-2 rtl:ml-2"></i> {{ $workshop->start_date->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="fas fa-map-marker-alt mr-2 rtl:ml-2"></i> {{ $workshop->is_online ? __('workshops.labels.online_live') : ($workshop->location ?? __('workshops.labels.unspecified')) }}
                                </div>
                                @if($showAdminMetrics)
                                    <div class="flex items-center text-gray-500 text-sm mb-4">
                                        <i class="fas fa-users mr-2 rtl:ml-2"></i> {{ $workshop->bookings_count }}/{{ $workshop->max_participants }} {{ __('workshops.labels.participants') }}
                                    </div>
                                @endif
                                
                                <!-- Fixed footer actions -->
                                <div class="mt-auto pt-4 border-t border-gray-100">
                                    <div class="flex gap-3 items-center">
                                        @if($isCompleted)
                                        <button type="button" class="flex-1 bg-gray-400 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                            <i class="fas fa-check-circle mr-2 rtl:ml-2"></i>
                                            {{ __('workshops.featured.completed') }}
                                        </button>
                                        @elseif($isBooked)
                                        <button type="button" class="flex-1 bg-green-500 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm" disabled>
                                            <i class="fas fa-check mr-2 rtl:ml-2 booking-button-icon"></i>
                                            <span class="booking-button-label">{{ __('workshops.featured.booked') }}</span>
                                        </button>
                                        @elseif($isFull)
                                        <button type="button" class="flex-1 bg-gray-400 text-white font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                            <i class="fas fa-lock mr-2 rtl:ml-2"></i>
                                            {{ __('workshops.featured.full') }}
                                        </button>
                                        @elseif($isRegistrationClosed)
                                            <button type="button" class="flex-1 bg-yellow-400 text-yellow-800 font-bold py-3 px-4 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
                                                <i class="fas fa-clock mr-2 rtl:ml-2"></i>
                                                {{ __('workshops.featured.closed') }}
                                            </button>
                                        @else
                                            <div class="flex flex-col gap-2 flex-1">
                                                <a href="{{ route('workshop.show', $workshop->slug) }}#workshop-booking"
                                                   class="flex-1 text-center font-bold py-3 px-4 rounded-full transition-colors flex items-center justify-center text-sm {{ $bookingButtonStateClasses }}">
                                                    <i class="fas fa-calendar-check mr-2 rtl:ml-2 booking-button-icon"></i>
                                                    <span class="booking-button-label">{{ __('workshops.cards.button_book') }}</span>
                                                </a>
                                            </div>
                                        @endif
                                        <a href="{{ route('workshop.show', $workshop->slug) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-full transition-colors flex items-center justify-center group">
                                            <i class="fas fa-info-circle text-sm mr-2 rtl:ml-2 group-hover:text-orange-500 transition-colors"></i>
                                            <span class="text-sm">{{ __('workshops.cards.button_details') }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-20 bg-white rounded-2xl shadow-lg">
                    <div class="w-24 h-24 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-coffee text-4xl text-amber-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 mb-3">{{ __('workshops.empty.title') }}</h3>
                    <p class="text-gray-500 text-lg max-w-md mx-auto mb-6">{{ __('workshops.empty.description') }}</p>
                </div>
            @endif
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
(() => {
const noResultsTitle = @json(__('workshops.no_results.title'));
const noResultsDescription = @json(__('workshops.no_results.description'));

const ensureNoResultsElement = () => {
    const existing = document.getElementById('no-results');
    if (existing) {
        return existing;
    }

    const container = document.getElementById('workshops-container');
    if (!container) {
        return null;
    }

    const noResultsHTML = `
        <div id="no-results" class="col-span-full text-center py-16 hidden">
            <i class="fas fa-search text-7xl text-gray-300 mb-6"></i>
            <h3 class="text-2xl font-semibold text-gray-700 mb-2">${noResultsTitle}</h3>
            <p class="text-gray-500 max-w-md mx-auto">${noResultsDescription}</p>
        </div>
    `;
    container.insertAdjacentHTML('afterend', noResultsHTML);
    return document.getElementById('no-results');
};

const filterWorkshops = (filter = 'all') => {
    const workshopsContainer = document.getElementById('workshops-container');
    const workshops = document.querySelectorAll('.workshop-card');
    if (!workshopsContainer || !workshops.length) {
        return;
    }

    const normalizedFilter = filter.toLowerCase();
    let visibleCount = 0;

    workshops.forEach((workshop) => {
        const type = (workshop.getAttribute('data-type') || '').toLowerCase();
        const level = (workshop.getAttribute('data-level') || '').toLowerCase();

        let show = false;
        switch (normalizedFilter) {
            case 'online':
                show = type === 'online';
                break;
            case 'offline':
                show = type === 'offline';
                break;
            case 'beginner':
                show = level === 'beginner';
                break;
            case 'advanced':
                show = level === 'advanced';
                break;
            case 'all':
            default:
                show = true;
        }

        workshop.style.display = show ? 'flex' : 'none';
        if (show) {
            visibleCount++;
        }
    });

    const noResultsElement = ensureNoResultsElement();
    if (noResultsElement) {
        noResultsElement.classList.toggle('hidden', visibleCount !== 0);
    }
};

const updateActiveButton = (activeButton) => {
    document.querySelectorAll('.filter-btn').forEach((btn) => {
        btn.classList.toggle('active', btn === activeButton);
    });
};

document.addEventListener('click', (event) => {
    const button = event.target.closest('.filter-btn');
    if (!button) {
        return;
    }

    event.preventDefault();

    const filter = button.dataset.filter || 'all';
    updateActiveButton(button);
    filterWorkshops(filter);
});

const bootstrapFilters = () => {
    ensureNoResultsElement();
    filterWorkshops(document.querySelector('.filter-btn.active')?.dataset.filter || 'all');
};

const bootstrapPage = () => {
    bootstrapFilters();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapPage, { once: true });
} else {
    bootstrapPage();
}

document.addEventListener('livewire:navigated', bootstrapPage);
document.addEventListener('livewire:load', bootstrapPage);

window.filterWorkshops = filterWorkshops;
})();
</script>

@if($whatsappBookingEnabled)
<script>
(function bootstrapWhatsAppBooking(config) {
    if (window.WhatsAppBooking) {
        window.WhatsAppBooking.configure(config);
        window.WhatsAppBooking.initButtons();
        window.WhatsAppBooking.initInquiryButtons();
        return;
    }

    window.__WHATSAPP_BOOKING_PENDING__ = window.__WHATSAPP_BOOKING_PENDING__ || [];
    window.__WHATSAPP_BOOKING_PENDING__.push(function(instance) {
        instance.configure(config);
        instance.initButtons();
        instance.initInquiryButtons();
    });
})(@json($whatsappBookingPayload));
</script>
@endif

@endpush



