@extends('layouts.app')

@section('title', __('tools.meta.title'))

@push('styles')
<style>
    .tools-hero {
        position: relative;
        overflow: hidden;
    }
    .tools-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
        pointer-events: none;
    }
    .tools-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        align-items: center;
        text-align: center;
        padding-block: 1rem;
    }
    .tools-hero-intro {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: 36rem;
    }
    .tools-hero-intro h1 {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 800;
        margin: 0;
        color: #fff;
    }
    .tools-hero-intro p {
        margin: 0;
        line-height: 1.8;
        color: rgba(241, 245, 249, 0.88);
        font-size: 0.95rem;
    }
    .tools-hero-actions {
        display: inline-flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    .tools-hero-actions a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.4rem;
        border-radius: 0.9rem;
        font-weight: 700;
        font-size: 0.95rem;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        text-decoration: none;
    }
    .tools-hero-actions .intro-primary {
        background: #fff;
        color: #6b2e30;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.18);
    }
    .tools-hero-actions .intro-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 40px rgba(15, 23, 42, 0.22);
    }
    .tools-hero-actions .intro-secondary {
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .tools-hero-actions .intro-secondary:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.22);
    }
    .tool-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        border-radius: 1.125rem;
        border: 1px solid #f1f5f9;
        background: linear-gradient(180deg, #ffffff 0%, #ffffff 100%);
        box-shadow: 0 18px 28px rgba(15, 23, 42, 0.08);
        transition: transform 0.35s ease, box-shadow 0.35s ease;
        overflow: hidden;
    }
    .tool-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 26px 40px rgba(15, 23, 42, 0.12);
    }
    .tool-card__media {
        position: relative;
        aspect-ratio: 4 / 3;
        min-height: 210px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tool-slider {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    .tool-slider .swiper-wrapper {
        height: 100%;
    }
    .tool-slider .swiper-slide {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        height: 100%;
    }
    .why-section {
        padding-block: 3.5rem;
    }
    .why-section .container {
        padding-inline: 1.25rem;
    }
    @media (min-width: 768px) {
        .why-section {
            padding-block: 4rem;
        }
        .why-section .container {
            padding-inline: 1.5rem;
        }
    }
    .why-slider {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .why-slider .swiper-wrapper {
        padding-bottom: 0.5rem;
    }
    .why-slider .swiper-slide {
        display: flex;
        justify-content: center;
        padding: 0.5rem;
    }
    .why-feature-card {
        width: 100%;
        max-width: 18rem;
    }
    .why-slider .swiper-pagination {
        bottom: 0;
    }
    .why-slider .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: rgba(107, 46, 48, 0.3);
        opacity: 1;
    }
    .why-slider .swiper-pagination-bullet-active {
        background: #6b2e30;
    }
    .tool-card__image {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        transition: transform 0.35s ease;
    }
    .tool-card:hover .tool-card__image {
        transform: scale(1.05);
    }
    .tool-card__badge {
        align-self: flex-end;
        margin: 0.9rem 1.25rem 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(107, 46, 48, 0.92);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        box-shadow: 0 10px 20px rgba(107, 46, 48, 0.2);
    }
    .tool-card__content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex-grow: 1;
        padding: 1.25rem;
        background: #fff;
    }
    .tool-card__title {
        color: #0f172a;
        font-weight: 700;
        line-height: 1.4;
        font-size: 1rem;
    }
    .tool-card__title span {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .tool-card__price {
        color: #6b2e30;
        font-weight: 700;
        font-size: 1.125rem;
    }
    .tool-card .rating-stars {
        color: #9f5e63;
        font-size: 0.875rem;
    }
    .tool-card .empty-rating {
        color: #d1d5db;
    }
    .tool-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        row-gap: 0.4rem;
        gap: 0.5rem;
        color: #475569;
        font-size: 0.75rem;
    }
    .tool-card__meta > * {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .tool-card__actions {
        margin-top: auto;
        display: grid;
        gap: 0.5rem;
    }
    .tool-card__actions a,
    .tool-card__actions button {
        border-radius: 0.875rem;
        font-weight: 600;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .tool-card__actions a:active,
    .tool-card__actions button:active {
        transform: scale(0.97);
    }
    .tool-card__actions .amazon-btn {
        background: linear-gradient(135deg, #9f5e63, #8f4a50);
        color: #fff;
    }
    .tool-card__actions .amazon-btn:hover {
        background: linear-gradient(135deg, #8f4a50, #6b2e30);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
    }
    .tool-slider .swiper-button-next,
    .tool-slider .swiper-button-prev {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        background: rgba(15, 23, 42, 0.4);
        color: #fff;
        top: 50%;
        transform: translateY(-50%);
    }
    .tool-slider .swiper-button-next::after,
    .tool-slider .swiper-button-prev::after {
        font-size: 12px;
        font-weight: bold;
    }
    .tool-slider .swiper-pagination-bullet {
        background-color: rgba(107, 46, 48, 0.85);
        opacity: 1;
        width: 6px;
        height: 6px;
    }
    .tool-slider .swiper-pagination-bullet-active {
        background-color: #6b2e30;
    }
    .tool-slider .swiper-button-disabled {
        opacity: 0.35 !important;
    }
    .tools-grid-wrapper {
        position: relative;
        z-index: 0;
    }
    .tools-grid-wrapper::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top, rgba(143, 74, 80, 0.25), transparent 65%);
        z-index: -1;
        opacity: 0.7;
    }
    .tools-filter {
        background: #fff;
        border-radius: 1.75rem;
        border: 1px solid rgba(107, 46, 48, 0.2);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
    }
    .category-filter-btn {
        transition: all 0.3s ease;
    }
    .category-filter-btn.active {
        box-shadow: 0 10px 20px rgba(107, 46, 48, 0.25);
    }
    .save-for-later-btn:disabled {
        opacity: 0.85;
        cursor: not-allowed;
    }
    #tools-grid {
        gap: 2rem;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @media (max-width: 1024px) {
        .tool-card {
            border-radius: 1.25rem;
        }
        .tool-card__content {
            padding: 1rem 1.15rem 1.25rem;
        }
        #tools-grid {
            gap: 1.5rem;
        }
    }
    @media (max-width: 640px) {
        .tool-card {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }
        .tool-card__media {
            aspect-ratio: 1 / 1;
            min-height: 200px;
        }
        .tool-card__title {
            font-size: 0.9rem;
        }
        .tool-card__price {
            font-size: 1rem;
        }
        .tools-filter {
            border-radius: 1.25rem;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.15);
        }
        #tools-grid {
            gap: 1rem;
        }
        .why-section {
            padding-block: 2.75rem;
        }
        .why-section .container {
            padding-inline: 1rem;
        }
        .why-slider .swiper-slide {
            padding: 0.15rem;
        }
        .why-feature-card {
            max-width: 100%;
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <section class="tools-hero relative bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 text-white py-20">
        <div class="container mx-auto px-4 relative z-10">
            <div class="tools-hero-inner">
                <div class="tools-hero-intro">
                    <h1>{{ __('tools.hero.title') }}</h1>
                    <p>{{ __('tools.hero.subtitle') }}</p>
                </div>
                <div class="tools-hero-actions">
                    <a href="#tools-grid" class="intro-primary">
                        <span>{{ __('tools.hero.cta.browse') }}</span>
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <a href="{{ route('saved.index') }}" class="intro-secondary">
                        <span>{{ __('tools.hero.cta.saved') }}</span>
                        <i class="fas fa-bookmark"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Filter -->
    <section class="relative -mt-12 lg:-mt-16">
        <div class="container mx-auto px-4">
            <div class="tools-filter max-w-6xl mx-auto overflow-hidden">
                <div class="px-6 sm:px-8 lg:px-10 py-8">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <a href="{{ route('saved.index') }}" 
                           class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm sm:text-base">
                            <i class="fas fa-bookmark text-base"></i>
                            <span>{{ __('tools.saved.banner_cta') }}</span>
                        </a>
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle text-orange-500"></i>
                            <span>{{ __('tools.saved.banner_hint') }}</span>
                        </div>
                    </div>
                    
                    <!-- Category Filters -->
                    <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                        <button class="category-filter-btn px-4 sm:px-6 py-2.5 sm:py-3 bg-orange-500 text-white rounded-xl font-semibold transition-all active text-xs sm:text-sm" data-category="all">
                            {{ __('tools.categories.all') }}
                        </button>
                        @foreach($categories as $categoryName => $categoryTools)
                            <button class="category-filter-btn px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-orange-500 hover:text-white transition-all text-xs sm:text-sm" data-category="{{ $categoryName }}">
                                {{ $categoryName }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tools Grid -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="tools-grid-wrapper max-w-7xl mx-auto">
                <div id="tools-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($tools as $tool)
                        <div class="tool-card group" data-category="{{ $tool->category }}">
                            <!-- Image Section -->
                            @php
                                $galleryUrls = $tool->gallery_image_urls;
                                if (empty($galleryUrls)) {
                                    $galleryUrls = [$tool->image_url];
                                }
                            @endphp
                            <div class="tool-card__badge">{{ $tool->category }}</div>
                            
                            <div class="tool-card__media">
                                <div class="tool-slider swiper tool-swiper" data-tool-id="{{ $tool->id }}">
                                    <div class="swiper-wrapper">
                                        @foreach($galleryUrls as $image)
                                            <div class="swiper-slide">
                                                <img src="{{ $image }}"
                                                     alt="{{ $tool->name }}"
                                                     class="tool-card__image"
                                                    onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ __('tools.cards.image_fallback_alt') }}';" loading="lazy">
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            </div>

                            <!-- Content Section -->
                            <div class="tool-card__content">
                                <h3 class="tool-card__title text-sm sm:text-base">
                                    <a href="{{ route('tools.show', $tool) }}" class="block hover:text-orange-500 transition-colors">
                                        <span>{{ $tool->name }}</span>
                                    </a>
                                </h3>

                                <div class="tool-card__meta justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="flex rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                        <span class="text-xs font-semibold text-slate-600">
                                        {{ $tool->rating }}
                                    </span>
                                    </div>
                                    <span class="text-xs text-slate-400 hidden sm:inline-flex">
                                        ({{ rand(10, 2000) }})
                                    </span>
                                </div>

                                <!-- Price -->
                                <div class="tool-card__price text-sm sm:text-lg">
                                    {{ number_format($tool->price, 2) }} {{ __('tools.cards.currency') }}
                                </div>


                                <!-- Action Buttons -->
                                <div class="tool-card__actions">
                                    <a href="{{ route('tools.show', $tool) }}"
                                       class="w-full flex items-center justify-center gap-2 py-2.5 sm:py-3 px-4 text-xs sm:text-sm font-semibold text-orange-600 border border-orange-200 hover:bg-orange-50 rounded-xl transition-colors duration-300">
                                        <i class="fas fa-info-circle text-base"></i>
                                        <span>{{ __('tools.cards.buttons.details') }}</span>
                                    </a>
                                    @if($tool->amazon_url)
                                    <a href="{{ $tool->amazon_url }}" 
                                       target="_blank"
                                       class="amazon-btn w-full flex items-center justify-center gap-2 py-2.5 sm:py-3 px-4 text-xs sm:text-sm">
                                        <i class="fab fa-amazon text-base"></i>
                                        <span>{{ __('tools.cards.buttons.amazon') }}</span>
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                    @endif
                                    
                                    @if($tool->amazon_url || $tool->affiliate_url)
                                    <button class="save-for-later-btn w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all duration-300"
                                            data-tool-id="{{ $tool->id }}"
                                            data-tool-name="{{ $tool->name }}"
                                            data-tool-price="{{ $tool->price }}">
                                        <i class="fas fa-bookmark text-base"></i>
                                        <span class="btn-text">{{ __('tools.cards.buttons.save') }}</span>
                                        <i class="fas fa-spinner fa-spin hidden loading-icon text-sm"></i>
                                    </button>
                                    @else
                                    <div class="w-full bg-slate-100 text-slate-500 font-semibold py-2.5 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2">
                                        <i class="fas fa-exclamation-circle text-base"></i>
                                        {{ __('tools.cards.unavailable') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @php
        $whyFeatures = [
            [
                'icon' => 'fas fa-check-circle',
                'title' => __('tools.why.features.reviews.title'),
                'description' => __('tools.why.features.reviews.description'),
            ],
            [
                'icon' => 'fas fa-book-reader',
                'title' => __('tools.why.features.info.title'),
                'description' => __('tools.why.features.info.description'),
            ],
            [
                'icon' => 'fas fa-sync',
                'title' => __('tools.why.features.updates.title'),
                'description' => __('tools.why.features.updates.description'),
            ],
            [
                'icon' => 'fas fa-shopping-bag',
                'title' => __('tools.why.features.amazon.title'),
                'description' => __('tools.why.features.amazon.description'),
            ],
        ];
    @endphp
    <!-- Why Choose Our Tools Section -->
    <section class="why-section py-16 bg-gradient-to-br from-amber-50 via-orange-50/70 to-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ __('tools.why.title') }}</h2>
            <p class="text-gray-600 mb-12 max-w-3xl mx-auto">
                {{ __('tools.why.description') }}
            </p>
            <div class="md:hidden mb-10">
                <div class="why-slider swiper">
                    <div class="swiper-wrapper">
                        @foreach($whyFeatures as $feature)
                        <div class="swiper-slide">
                            <div class="why-feature-card flex flex-col items-center bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
                                <div class="bg-gradient-to-br from-amber-100 to-orange-100 text-amber-600 rounded-full h-20 w-20 flex items-center justify-center mb-4 shadow-lg">
                                    <i class="{{ $feature['icon'] }} text-4xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $feature['title'] }}</h3>
                                <p class="text-gray-600 text-center">{{ $feature['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($whyFeatures as $feature)
                <div class="flex flex-col items-center bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
                    <div class="bg-gradient-to-br from-amber-100 to-orange-100 text-amber-600 rounded-full h-20 w-20 flex items-center justify-center mb-4 shadow-lg">
                        <i class="{{ $feature['icon'] }} text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600 text-center">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
@php
    $toolsTranslations = array(
        'buttons' => array(
            'save' => __('tools.cards.buttons.save'),
            'saved' => __('tools.cards.buttons.saved'),
            'saving' => __('tools.cards.buttons.saving'),
            'save_error' => __('tools.cards.buttons.save_error'),
            'removing' => __('tools.cards.buttons.removing'),
        ),
        'notifications' => array(
            'saved' => __('tools.notifications.saved'),
            'save_error' => __('tools.notifications.save_error'),
            'removed' => __('tools.notifications.removed'),
            'remove_error' => __('tools.notifications.remove_error'),
            'remove_detailed_error' => __('tools.notifications.remove_detailed_error'),
        ),
    );
@endphp
<script>
const toolsTranslations = @json($toolsTranslations);

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        document.querySelectorAll('.tool-swiper').forEach(function (el) {
            const slidesCount = el.querySelectorAll('.swiper-slide').length;
            const paginationEl = el.querySelector('.swiper-pagination');
            const nextEl = el.querySelector('.swiper-button-next');
            const prevEl = el.querySelector('.swiper-button-prev');

            const options = {
                slidesPerView: 1,
                spaceBetween: 8,
                loop: slidesCount > 1
            };

            if (slidesCount > 1 && paginationEl) {
                options.pagination = {
                    el: paginationEl,
                    clickable: true
                };
            } else if (paginationEl) {
                paginationEl.classList.add('hidden');
            }

            if (slidesCount > 1 && nextEl && prevEl) {
                options.navigation = {
                    nextEl,
                    prevEl
                };
            } else {
                if (nextEl) {
                    nextEl.classList.add('hidden');
                }
                if (prevEl) {
                    prevEl.classList.add('hidden');
                }
            }

            if (slidesCount > 1) {
                options.autoplay = {
                    delay: 4000,
                    disableOnInteraction: false
                };
            }

            new Swiper(el, options);
        });

        const whySliderEl = document.querySelector('.why-slider');
        if (whySliderEl) {
            new Swiper(whySliderEl, {
                slidesPerView: 1,
                spaceBetween: 16,
                pagination: {
                    el: whySliderEl.querySelector('.swiper-pagination'),
                    clickable: true
                }
            });
        }
    }

    // Load saved status for all tools
    loadSavedStatus();
    
    // Category Filter Functionality
    const categoryButtons = document.querySelectorAll('.category-filter-btn');
    const toolCards = document.querySelectorAll('.tool-card');
    const defaultSaveClasses = ['bg-gradient-to-r', 'from-orange-500', 'to-amber-500', 'hover:from-orange-600', 'hover:to-amber-600'];
    const savedStateClasses = ['bg-green-500', 'hover:bg-green-600', 'saved'];

    function applySavedButtonState(button) {
        button.classList.remove(...defaultSaveClasses, 'bg-red-500');
        button.classList.add(...savedStateClasses);
    }

    function applyDefaultButtonState(button) {
        button.classList.remove(...savedStateClasses, 'bg-red-500');
        defaultSaveClasses.forEach(cls => {
            if (!button.classList.contains(cls)) {
                button.classList.add(cls);
            }
        });
    }

    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active', 'bg-orange-500', 'text-white'));
            categoryButtons.forEach(btn => btn.classList.add('bg-gray-100', 'text-gray-700'));
            this.classList.add('active', 'bg-orange-500', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');

            // Filter tools
            toolCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.removeProperty('display');
                    card.style.animation = 'fadeIn 0.5s ease-in-out';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Load saved status for all tools
    function loadSavedStatus() {
        fetch('/saved/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update each tool's saved status
                    document.querySelectorAll('.save-for-later-btn').forEach(btn => {
                        const toolId = btn.dataset.toolId;
                        const isSaved = data.saved_tools.includes(parseInt(toolId));
                        
                        if (isSaved) {
                            btn.querySelector('.btn-text').textContent = toolsTranslations.buttons.saved;
                            applySavedButtonState(btn);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading saved status:', error);
            });
    }

    // Save for Later Functionality
    document.querySelectorAll('.save-for-later-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const toolId = this.dataset.toolId;
            const toolName = this.dataset.toolName;
            const toolPrice = this.dataset.toolPrice;
            
            // Check if item is already saved
            if (this.classList.contains('saved')) {
                // Item is saved, remove it
                removeFromSaved(this, toolId);
                return;
            }
            
            // Show loading state
            this.disabled = true;
            this.querySelector('.btn-text').textContent = toolsTranslations.buttons.saving;
            this.querySelector('.loading-icon').classList.remove('hidden');
            
            // Save for later
            fetch('/saved/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    tool_id: toolId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success state - Item is now saved
                    this.querySelector('.btn-text').textContent = toolsTranslations.buttons.saved;
                    applySavedButtonState(this);
                    this.disabled = false;
                    
                    // Show success animation on the button
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                    
                    // Show toast notification
                    showToast(toolsTranslations.notifications.saved, 'success');
                    
                    // Hide loading icon
                    this.querySelector('.loading-icon').classList.add('hidden');
                } else {
                    // Show error state
                    this.querySelector('.btn-text').textContent = toolsTranslations.buttons.save_error;
                    this.classList.remove(...defaultSaveClasses, ...savedStateClasses);
                    this.classList.add('bg-red-500');
                    
                    showToast(data.message || toolsTranslations.notifications.save_error, 'error');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.disabled = false;
                        this.querySelector('.btn-text').textContent = toolsTranslations.buttons.save;
                        this.querySelector('.loading-icon').classList.add('hidden');
                        this.classList.remove('bg-red-500');
                        applyDefaultButtonState(this);
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.querySelector('.btn-text').textContent = toolsTranslations.buttons.save;
                this.querySelector('.loading-icon').classList.add('hidden');
                showToast(toolsTranslations.notifications.save_error, 'error');
            });
        });
    });

    // Remove from saved function
    function removeFromSaved(button, toolId) {
        // Show loading state
        button.disabled = true;
        button.querySelector('.btn-text').textContent = toolsTranslations.buttons.removing;
        button.querySelector('.loading-icon').classList.remove('hidden');
        
        // Remove from saved
        fetch('/saved/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                tool_id: toolId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success state - Item removed
                button.querySelector('.btn-text').textContent = toolsTranslations.buttons.save;
                applyDefaultButtonState(button);
                button.disabled = false;
                
                // Show success animation
                button.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    button.style.transform = 'scale(1)';
                }, 200);
                
                // Show success message
                showToast(toolsTranslations.notifications.removed, 'success');
                
                // Hide loading icon
                button.querySelector('.loading-icon').classList.add('hidden');
            } else {
                throw new Error(data.message || toolsTranslations.notifications.remove_error);
            }
        })
        .catch(error => {
            console.error('Error removing from saved:', error);
            button.disabled = false;
            button.querySelector('.btn-text').textContent = toolsTranslations.buttons.saved;
            button.querySelector('.loading-icon').classList.add('hidden');
            showToast(toolsTranslations.notifications.remove_detailed_error, 'error');
        });
    }


    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
        
        // Set color based on type
        if (type === 'success') {
            toast.classList.add('bg-green-500');
        } else if (type === 'error') {
            toast.classList.add('bg-red-500');
        } else if (type === 'warning') {
            toast.classList.add('bg-yellow-500');
        } else {
            toast.classList.add('bg-blue-500');
        }
        
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .cart-quantity {
            transition: all 0.3s ease;
            min-width: 20px;
            text-align: center;
        }
        
        .cart-quantity.animate-pulse {
            animation: pulse 1s ease-in-out;
        }
        
        .add-to-cart-btn:disabled {
            cursor: not-allowed;
            opacity: 0.9;
        }
        
        .add-to-cart-btn.bg-blue-500:hover {
            background-color: #3b82f6 !important;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush











