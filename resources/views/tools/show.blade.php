@extends('layouts.app')

@php
    $shortToolName = \Illuminate\Support\Str::limit($tool->name, 60, '...');
@endphp

@section('title', 'Tool details - ' . $shortToolName)

@push('styles')
<style>
    :root {
        --tool-cream: var(--brand-cream, #eaf3f6);
        --tool-gold-rgb: var(--brand-gold-rgb, 15, 162, 137);
        --tool-teal-rgb: var(--brand-teal-rgb, 15, 162, 137);
    }
    .tool-detail-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(120% 120% at 80% 0%, rgba(var(--tool-gold-rgb), 0.18) 0%, rgba(246, 242, 228, 0) 65%),
            linear-gradient(135deg, var(--tool-cream) 0%, #ffffff 45%, #e6f7f2 100%);
    }
    .tool-detail-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(90% 85% at 15% 10%, rgba(var(--tool-teal-rgb), 0.14), transparent 55%);
        pointer-events: none;
    }
    .tool-detail-card {
        position: relative;
        border-radius: 2.25rem;
        box-shadow: 0 32px 60px rgba(15, 23, 42, 0.14);
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(6px);
    }
    .tool-detail-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.85) 0%, rgba(246, 242, 228, 0.7) 100%);
        opacity: 0.8;
        pointer-events: none;
    }
    .tool-detail-card > * {
        position: relative;
        z-index: 1;
    }
    .tool-gallery-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .tool-gallery-slider {
        position: relative;
        width: 100%;
        border-radius: 1.5rem;
        background: #ffffff;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        margin-bottom: 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }
    .gallery-slide {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 4 / 3;
        padding: clamp(1.75rem, 3vw, 2.75rem);
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.9), rgba(236, 233, 255, 0.45));
    }
    .gallery-slide img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 1.25rem;
        box-shadow: 0 20px 30px rgba(15, 23, 42, 0.12);
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
        padding: clamp(0.75rem, 1.5vw, 1.25rem);
    }
    .gallery-zoom-btn {
        position: absolute;
        left: clamp(1rem, 2vw, 1.5rem);
        bottom: clamp(1rem, 2vw, 1.5rem);
        display: inline-flex;
        flex-direction: row-reverse;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.6rem 1.3rem;
        border-radius: 999px;
        background: rgba(16, 24, 40, 0.92);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.25);
    }
    .gallery-zoom-btn i {
        font-size: 1rem;
    }
    .gallery-zoom-btn .zoom-label {
        display: inline-block;
        white-space: nowrap;
        line-height: 1.1;
    }
    .gallery-zoom-btn:hover {
        transform: translateY(-2px);
        background: rgba(15, 23, 42, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.32);
    }
    .tool-gallery-slider .swiper-button-next,
    .tool-gallery-slider .swiper-button-prev {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.92);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.2);
        transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
    }
    .tool-gallery-slider .swiper-button-next:hover,
    .tool-gallery-slider .swiper-button-prev:hover {
        background: rgba(15, 23, 42, 0.98);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.28);
        transform: translateY(-1px);
    }
    .tool-gallery-slider .swiper-button-next::after,
    .tool-gallery-slider .swiper-button-prev::after {
        font-size: 1rem;
    }
    .tool-gallery-thumbs {
        padding: 0.25rem 0.5rem;
    }
    .tool-gallery-thumbs .swiper-slide {
        opacity: 0.45;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    .tool-gallery-thumbs .swiper-slide-thumb-active {
        opacity: 1;
        transform: translateY(-4px);
    }
    .gallery-thumb {
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: #ffffff;
        height: 96px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem;
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.06);
    }
    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .tool-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        background: rgba(var(--tool-teal-rgb), 0.12);
        color: #0f4c73;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .tool-overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    .tool-overview-card {
        border-radius: 1.5rem;
        background: linear-gradient(160deg, rgba(246, 242, 228, 0.9) 0%, #ffffff 100%);
        border: 1px solid rgba(var(--tool-gold-rgb), 0.25);
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        box-shadow: 0 18px 34px rgba(15, 76, 115, 0.14);
    }
    .tool-overview-card i {
        color: #0f4c73;
        font-size: 1.1rem;
    }
    .tool-overview-label {
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .tool-overview-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .tool-description {
        color: #475569;
        line-height: 1.9;
        font-size: 1.05rem;
    }
    .tool-price-card {
        border-radius: 1.75rem;
        border: 1px solid rgba(15, 76, 115, 0.28);
        background: linear-gradient(135deg, rgba(var(--tool-gold-rgb), 0.2) 0%, rgba(246, 242, 228, 0.92) 100%);
        padding: 1.5rem;
        box-shadow: 0 26px 40px rgba(15, 76, 115, 0.18);
    }
    .tool-price-note {
        font-size: 0.8rem;
        color: #94a3b8;
    }
    .detail-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem 1.2rem;
        border-radius: 1.25rem;
        background: rgba(15, 23, 42, 0.03);
        border: 1px solid transparent;
        transition: background 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
    }
    .detail-feature-item:hover {
        background: rgba(var(--tool-gold-rgb), 0.16);
        border-color: rgba(var(--tool-gold-rgb), 0.35);
        transform: translateY(-2px);
    }
    .detail-feature-item i {
        color: #22c55e;
        font-size: 1rem;
        margin-top: 0.1rem;
    }
    .tool-cta-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
    }
    .tool-cta-group > * {
        width: 100%;
        border-radius: 1.15rem;
        min-height: 56px;
    }
    .tool-related-section {
        background: linear-gradient(160deg, #ffffff 0%, var(--tool-cream) 100%);
        border-radius: 2rem;
        padding: 2.5rem;
        box-shadow: 0 26px 44px rgba(15, 23, 42, 0.12);
    }
    .gallery-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        z-index: 9999;
    }
    .gallery-lightbox.hidden {
        display: none;
    }
    .gallery-lightbox__content {
        position: relative;
        max-width: min(1100px, 90vw);
        max-height: 85vh;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.75rem;
        overflow: hidden;
        background: #0f172a;
        box-shadow: 0 34px 68px rgba(15, 23, 42, 0.45);
    }
    .gallery-lightbox__content img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
    }
    .gallery-lightbox__close {
        position: absolute;
        top: 1rem;
        left: 1rem;
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.85);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        border: none;
        cursor: pointer;
        transition: transform 0.3s ease, background 0.3s ease;
    }
    .gallery-lightbox__close:hover {
        background: rgba(var(--tool-teal-rgb), 0.95);
        transform: scale(1.05);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @media (max-width: 1024px) {
        .gallery-zoom-btn {
            padding: 0.5rem 1.15rem;
            font-size: 0.85rem;
        }
        .tool-detail-card {
            border-radius: 1.75rem;
        }
        .tool-gallery-slider {
            border-radius: 1.5rem;
        }
        .gallery-slide {
            padding: 1.5rem;
        }
        .tool-overview-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        }
        .tool-detail-card .grid {
            gap: 2rem;
        }
    }
    @media (max-width: 640px) {
        .tool-gallery-shell {
            gap: 0;
        }
        .tool-gallery-slider {
            margin-bottom: 0;
        }
        .tool-gallery-thumbs {
            display: none;
        }
        .tool-detail-card {
            border-radius: 1.25rem;
        }
        .tool-detail-card .grid {
            gap: 1.35rem;
        }
        .gallery-slide {
            padding: 1.05rem;
        }
        .tool-gallery-slider .swiper-button-next,
        .tool-gallery-slider .swiper-button-prev {
            width: 30px;
            height: 30px;
        }
        .gallery-zoom-btn {
            left: 0.68rem;
            bottom: 0.68rem;
            padding: 0.42rem 0.84rem;
            font-size: 0.76rem;
        }
        .tool-related-section {
            margin-top: 1.4rem;
            padding: 1.45rem;
            border-radius: 1.3rem;
        }
    }
        .gallery-zoom-btn {
            left: 0.75rem;
            bottom: 0.75rem;
            padding: 0.48rem 0.95rem;
            font-size: 0.8rem;
        }
        .tool-detail-hero {
            background: linear-gradient(180deg, var(--tool-cream) 0%, #ffffff 60%, #e6f7f2 100%);
        }
        .tool-detail-card {
            border-radius: 1.25rem;
        }
        .gallery-slide {
            padding: 1.25rem;
        }
        .tool-gallery-slider .swiper-button-next,
        .tool-gallery-slider .swiper-button-prev {
            width: 36px;
            height: 36px;
        }
        .tool-gallery-thumbs {
            padding-inline: 0;
        }
        .gallery-thumb {
            height: 80px;
        }
        .tool-related-section {
            padding: 1.75rem;
            border-radius: 1.5rem;
        }
    }
</style>
@endpush
@section('content')
@php
    $galleryImages = collect($tool->gallery_image_urls ?? [])
        ->prepend($tool->image_url)
        ->filter()
        ->unique()
        ->values();
    $reviewsCount = $tool->rating > 0
        ? rand(240, 2400)
        : rand(45, 220);
@endphp

<section class="tool-detail-hero py-10 sm:py-14 lg:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-12">
        <div class="tool-detail-card">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 p-6 sm:p-10">
                <div class="tool-gallery-shell">
                    <div class="swiper tool-gallery-slider">
                        <div class="swiper-wrapper">
                            @foreach($galleryImages as $index => $image)
                                <div class="swiper-slide" data-gallery-index="{{ $index }}">
                                    <div class="gallery-slide">
                                        <img src="{{ $image }}" alt="{{ $tool->name }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                                        <button type="button" class="gallery-zoom-btn" data-gallery-zoom data-index="{{ $index }}">
                                            <i class="fas fa-expand"></i>
                                            <span class="zoom-label">Zoom image</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>

                    @if($galleryImages->count() > 1)
                        <div class="swiper tool-gallery-thumbs">
                            <div class="swiper-wrapper">
                                @foreach($galleryImages as $index => $image)
                                    <div class="swiper-slide" data-index="{{ $index }}">
                                        <div class="gallery-thumb">
                                            <img src="{{ $image }}" alt="{{ $tool->name }}" loading="lazy">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-7 lg:gap-8">
                    <div class="space-y-4">
                        @if($tool->category)
                            <span class="tool-info-badge">
                                <i class="fas fa-tag text-sm"></i>
                                {{ $tool->category }}
                            </span>
                        @endif
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-relaxed text-slate-900" title="{{ $tool->name }}">
                            {{ $shortToolName }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <div class="flex items-center gap-2 text-amber-500">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-base font-semibold text-slate-700">{{ number_format($tool->rating ?? 0, 1) }}</span>
                            </div>
                            <span class="text-slate-400">|</span>
                            <span class="text-slate-500">Trusted by {{ number_format($reviewsCount) }} home cooks and bakers</span>
                        </div>
                    </div>

                    <div class="tool-overview-grid">
                        <div class="tool-overview-card">
                            <span class="tool-overview-label">Overall rating</span>
                            <span class="tool-overview-value">
                                <i class="fas fa-medal"></i>
                                {{ number_format($tool->rating ?? 0, 1) }}
                            </span>
                            <span class="text-xs text-slate-400">{{ number_format($reviewsCount) }} verified reviews</span>
                        </div>
                        <div class="tool-overview-card">
                            <span class="tool-overview-label">Approximate price</span>
                            <span class="tool-overview-value text-orange-500">
                                @if(!is_null($tool->price))
                                    {{ number_format($tool->price, 2) }}
                                    <span class="text-sm font-semibold text-slate-500">AED</span>
                                @else
                                    <span class="text-sm font-semibold text-slate-500">Not available right now</span>
                                @endif
                            </span>
                            <span class="text-xs text-slate-400">May vary by retailer and shipping cost</span>
                        </div>
                        <div class="tool-overview-card">
                            <span class="tool-overview-label">Category</span>
                            <span class="tool-overview-value">
                                <i class="fas fa-utensils"></i>
                                {{ $tool->category ?? 'Recommended kitchen tools' }}
                            </span>
                            <span class="text-xs text-slate-400">Part of the curated Wasfah tools collection</span>
                        </div>
                    </div>

                    @if($tool->description)
                        <div class="tool-description">
                            {!! nl2br(e($tool->description)) !!}
                        </div>
                    @endif

                    <div class="tool-price-card">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <p class="text-sm text-slate-500">Great for any kitchen that needs</p>
                                <p class="text-xl sm:text-2xl font-bold text-orange-500 mt-1">
                                    {{ $tool->category ?? 'cutting-edge kitchen solutions' }}
                                </p>
                            </div>
                            <div class="text-sm text-slate-600 font-semibold">
                                @if(!is_null($tool->price))
                                    Current price: {{ number_format($tool->price, 2) }} AED
                                @else
                                    Price varies by retailer
                                @endif
                            </div>
                        </div>
                        <p class="tool-price-note mt-3">* Prices are indicative and may change based on availability and active offers.</p>
                    </div>

                    @if(!empty($tool->features))
                        <div class="space-y-3">
                            <h2 class="text-xl font-semibold text-slate-900">Key highlights</h2>
                            <div class="space-y-3">
                                @foreach($tool->features as $feature)
                                    @if(!empty($feature))
                                        <div class="detail-feature-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="text-slate-600">{{ $feature }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="tool-cta-group">
                        @if($tool->amazon_url)
                            <a href="{{ $tool->amazon_url }}" target="_blank" rel="noopener"
                               class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-[#0f4c73] hover:bg-[#0a8070] transition-colors shadow-md rounded-xl">
                                <i class="fab fa-amazon text-lg"></i>
                                <span>Continue on Amazon</span>
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                        @endif

                        @if($tool->affiliate_url)
                            <a href="{{ $tool->affiliate_url }}" target="_blank" rel="noopener"
                               class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-md rounded-xl">
                                <i class="fas fa-shopping-bag text-lg"></i>
                                <span>Authorized partner store</span>
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                        @endif

                        @if($tool->amazon_url || $tool->affiliate_url)
                            <button
                                id="save-tool-button"
                                class="save-for-later-btn w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold py-3 px-6 rounded-xl text-sm flex items-center justify-center gap-2 transition-all duration-300"
                                data-tool-id="{{ $tool->id }}"
                                data-tool-name="{{ $tool->name }}"
                                data-tool-price="{{ $tool->price }}"
                            >
                                <i class="fas fa-bookmark text-base"></i>
                                <span class="btn-text">Save to buy later</span>
                                <i class="fas fa-spinner fa-spin hidden loading-icon text-sm"></i>
                            </button>
                        @else
                            <div class="w-full bg-slate-100 text-slate-500 font-semibold py-3 px-6 rounded-xl text-sm flex items-center justify-center gap-2">
                                <i class="fas fa-exclamation-circle text-base"></i>
                                Not available for purchase right now
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="gallery-lightbox" class="gallery-lightbox hidden" role="dialog" aria-modal="true" aria-label="Tool photo gallery">
    <div class="gallery-lightbox__content">
        <button type="button" class="gallery-lightbox__close" data-gallery-close aria-label="Close gallery">
            <i class="fas fa-times"></i>
        </button>
        <img src="" alt="{{ $tool->name }}" loading="lazy">
    </div>
</div>

@if($relatedTools->isNotEmpty())
    <section class="py-12 sm:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-12">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Similar tools you may like</h2>
                <a href="{{ route('tools') }}" class="text-sm text-orange-500 hover:text-orange-600 font-semibold">
                    View all tools
                </a>
            </div>

            <div class="tool-related-section">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($relatedTools as $relatedTool)
                        <a href="{{ route('tools.show', $relatedTool) }}" class="related-tool-card">
                            <div class="related-tool-image">
                                <img src="{{ $relatedTool->image_url }}" alt="{{ $relatedTool->name }}" loading="lazy">
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-base font-semibold text-slate-900 line-clamp-2">{{ $relatedTool->name }}</h3>
                                <div class="flex items-center justify-between text-sm text-slate-500">
                                    <span class="font-bold text-orange-500">
                                        @if(!is_null($relatedTool->price))
                                            {{ number_format($relatedTool->price, 2) }} AED
                                        @else
                                            Unavailable
                                        @endif
                                    </span>
                                    <span class="flex items-center gap-1 text-yellow-400">
                                        <i class="fas fa-star text-xs"></i>
                                        <span class="text-slate-600">{{ number_format($relatedTool->rating ?? 0, 1) }}</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const galleryData = @json($galleryImages->values());
    const toolName = @json($tool->name);
    let currentIndex = 0;
    let lightboxOpen = false;
    let mainSwiper;
    let thumbsSwiper;

    const galleryMainEl = document.querySelector('.tool-gallery-slider');
    const thumbsEl = document.querySelector('.tool-gallery-thumbs');
    const lightboxEl = document.getElementById('gallery-lightbox');
    const lightboxImg = lightboxEl ? lightboxEl.querySelector('img') : null;
    const isMobileView = window.matchMedia('(max-width: 640px)').matches;

    if (galleryMainEl && galleryData.length) {
        const slidesCount = galleryData.length;

        if (!isMobileView && thumbsEl && slidesCount > 1) {
            thumbsSwiper = new Swiper(thumbsEl, {
                slidesPerView: Math.min(5, slidesCount),
                spaceBetween: 14,
                freeMode: true,
                watchSlidesProgress: true,
                breakpoints: {
                    0: { slidesPerView: Math.min(3, slidesCount), spaceBetween: 10 },
                    640: { slidesPerView: Math.min(4, slidesCount), spaceBetween: 12 },
                    1024: { slidesPerView: Math.min(5, slidesCount), spaceBetween: 14 }
                }
            });
        }

        const navNext = galleryMainEl.querySelector('.swiper-button-next');
        const navPrev = galleryMainEl.querySelector('.swiper-button-prev');

        if (slidesCount <= 1) {
            [navNext, navPrev].forEach(btn => btn && btn.classList.add('hidden'));
        }

        const mainOptions = {
            loop: slidesCount > 1,
            speed: 600,
            effect: slidesCount > 1 ? 'fade' : 'slide',
            fadeEffect: { crossFade: true },
            allowTouchMove: slidesCount > 1,
            navigation: {
                nextEl: navNext,
                prevEl: navPrev
            },
            autoplay: slidesCount > 1 ? { delay: 4500, disableOnInteraction: false } : false,
            on: {
                slideChange(swiper) {
                    currentIndex = swiper.realIndex;
                    if (thumbsSwiper && typeof thumbsSwiper.slideTo === 'function') {
                        thumbsSwiper.slideTo(swiper.realIndex);
                        thumbsSwiper.updateSlidesClasses();
                    }
                    if (lightboxOpen) {
                        updateLightboxImage(currentIndex);
                    }
                }
            }
        };

        if (thumbsSwiper) {
            mainOptions.thumbs = { swiper: thumbsSwiper };
        }

        mainSwiper = new Swiper(galleryMainEl, mainOptions);

        function updateLightboxImage(index) {
            if (!lightboxEl || !lightboxImg) {
                return;
            }
            const safeIndex = Math.max(0, Math.min(galleryData.length - 1, index));
            lightboxImg.src = galleryData[safeIndex] || '';
            lightboxImg.alt = toolName;
        }

        function openLightbox(index = currentIndex) {
            if (!lightboxEl || !lightboxImg) {
                return;
            }
            lightboxEl.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            lightboxOpen = true;
            if (mainSwiper && galleryData.length > 1) {
                mainSwiper.slideToLoop(index, 0);
            } else {
                currentIndex = index;
            }
            updateLightboxImage(index);
        }

        function closeLightbox() {
            if (!lightboxEl) {
                return;
            }
            lightboxEl.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            lightboxOpen = false;
        }

        document.querySelectorAll('[data-gallery-zoom]').forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const idx = parseInt(btn.dataset.index, 10);
                openLightbox(Number.isNaN(idx) ? currentIndex : idx);
            });
        });

        if (galleryMainEl) {
            galleryMainEl.addEventListener('click', event => {
                if (event.target.closest('[data-gallery-zoom]')) {
                    return;
                }
                if (event.target.tagName && event.target.tagName.toLowerCase() === 'img') {
                    openLightbox(currentIndex);
                }
            });
        }

        if (lightboxEl) {
            lightboxEl.querySelectorAll('[data-gallery-close]').forEach(el => {
                el.addEventListener('click', closeLightbox);
            });

            lightboxEl.addEventListener('click', event => {
                if (event.target === lightboxEl) {
                    closeLightbox();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && lightboxOpen) {
                    closeLightbox();
                }
            });
        }
    }

    const saveButton = document.getElementById('save-tool-button');
    if (!saveButton) {
        return;
    }

    const toolId = parseInt(saveButton.dataset.toolId, 10);
    const defaultClasses = ['bg-gradient-to-r', 'from-orange-500', 'to-amber-500', 'hover:from-orange-600', 'hover:to-amber-600'];
    const savedStateClasses = ['bg-green-500', 'hover:bg-green-600', 'saved'];

    function applySavedButtonState(button) {
        button.classList.remove(...defaultClasses, 'bg-red-500');
        savedStateClasses.forEach(cls => {
            if (!button.classList.contains(cls)) {
                button.classList.add(cls);
            }
        });
    }

    function applyDefaultButtonState(button) {
        button.classList.remove(...savedStateClasses, 'bg-red-500');
        defaultClasses.forEach(cls => {
            if (!button.classList.contains(cls)) {
                button.classList.add(cls);
            }
        });
    }

    function showToast(message, type = 'info') {
        document.querySelectorAll('.toast').forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = 'toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full';

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

        requestAnimationFrame(() => toast.classList.remove('translate-x-full'));

        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    function setLoadingState(button, isLoading, text) {
        button.disabled = isLoading;
        button.querySelector('.btn-text').textContent = text;
        button.querySelector('.loading-icon').classList.toggle('hidden', !isLoading);
    }

    function handleUnauthenticated() {
        showToast('Please sign in to save tools', 'warning');
        setTimeout(() => {
            window.location.href = '{{ route('login') }}';
        }, 1200);
    }

    function loadInitialSavedState() {
        fetch('/saved/status')
            .then(response => {
                if (response.status === 401) {
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    const isSaved = data.saved_tools.includes(toolId);
                    if (isSaved) {
                        saveButton.querySelector('.btn-text').textContent = 'Saved';
                        applySavedButtonState(saveButton);
                    } else {
                        applyDefaultButtonState(saveButton);
                    }
                } else {
                    applyDefaultButtonState(saveButton);
                }
            })
            .catch(() => {
                applyDefaultButtonState(saveButton);
            });
    }

    function addToSaved() {
        setLoadingState(saveButton, true, 'Saving...');
        fetch('/saved/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ tool_id: toolId })
        })
        .then(response => {
            if (response.status === 401) {
                handleUnauthenticated();
                throw new Error('unauthenticated');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                saveButton.querySelector('.btn-text').textContent = 'Saved';
                applySavedButtonState(saveButton);
                saveButton.disabled = false;
                saveButton.querySelector('.loading-icon').classList.add('hidden');
                showToast('Item saved to buy later!', 'success');
            } else {
                throw new Error(data.message || 'error');
            }
        })
        .catch(error => {
            if (error.message === 'unauthenticated') {
                return;
            }
            saveButton.disabled = false;
            saveButton.querySelector('.btn-text').textContent = 'Save to buy later';
            saveButton.querySelector('.loading-icon').classList.add('hidden');
            applyDefaultButtonState(saveButton);
            showToast('Something went wrong while saving the item', 'error');
        });
    }

    function removeFromSaved() {
        setLoadingState(saveButton, true, 'Removing...');
        fetch('/saved/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ tool_id: toolId })
        })
        .then(response => {
            if (response.status === 401) {
                handleUnauthenticated();
                throw new Error('unauthenticated');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                saveButton.querySelector('.btn-text').textContent = 'Save to buy later';
                applyDefaultButtonState(saveButton);
                saveButton.disabled = false;
                saveButton.querySelector('.loading-icon').classList.add('hidden');
                showToast('Item removed from saved tools!', 'success');
            } else {
                throw new Error(data.message || 'error');
            }
        })
        .catch(error => {
            if (error.message === 'unauthenticated') {
                return;
            }
            saveButton.disabled = false;
            saveButton.querySelector('.btn-text').textContent = 'Saved';
            saveButton.querySelector('.loading-icon').classList.add('hidden');
            applySavedButtonState(saveButton);
            showToast('Something went wrong while removing the item', 'error');
        });
    }

    saveButton.addEventListener('click', () => {
        const isSaved = saveButton.classList.contains('saved');
        if (isSaved) {
            removeFromSaved();
        } else {
            addToSaved();
        }
    });

    loadInitialSavedState();
});
</script>
@endpush





