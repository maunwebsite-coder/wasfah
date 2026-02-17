@extends('thedolci.layouts.store')

@section('title', $product['name'] . ' | thedolci')
@section('body_class', 'dolci-page-product')

@section('content')
<section class="dolci-section dolci-section-tight dolci-product-focus">
    <div class="dolci-container">
        <nav class="dolci-breadcrumbs">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('thedolci.shop') }}">Shop</a>
            <span>/</span>
            <strong>{{ $product['name'] }}</strong>
        </nav>

        @php
            $productImages = collect(array_merge([$product['cover_image'] ?? null], $product['gallery_images'] ?? []))
                ->map(static fn ($image) => trim((string) $image))
                ->filter()
                ->unique()
                ->values();
            $primaryImage = (string) ($productImages->first() ?? '');
        @endphp

        <div class="dolci-product-layout">
            <div class="dolci-product-gallery">
                <div class="dolci-product-main-image">
                    <img id="main-product-image" src="{{ $primaryImage }}" alt="{{ $product['name'] }}">
                </div>
                @if($productImages->count() > 1)
                    <div class="dolci-thumb-grid">
                        @foreach($productImages as $image)
                            <button type="button" class="dolci-thumb" data-product-thumb data-image="{{ $image }}">
                                <img src="{{ $image }}" alt="{{ $product['name'] }} thumbnail">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="dolci-product-panel">
                <h1>{{ $product['name'] }}</h1>
                <p class="dolci-product-sub">{{ $product['headline'] }}</p>
                <p>{{ $product['description'] }}</p>
                <div class="dolci-product-highlights">
                    <span>Freshly made daily</span>
                    <span>Luxury-ready packaging</span>
                    <span>Scheduled delivery or pickup</span>
                </div>

                @if(!empty($product['is_seasonal']) || !empty($product['show_limited_edition']) || !empty($product['preorder_enabled']))
                    <div class="dolci-inline-badges">
                        @if(!empty($product['show_limited_edition']))
                            <span class="dolci-pill">Limited Edition</span>
                        @endif
                        @if(!empty($product['is_seasonal']))
                            <span class="dolci-pill">Seasonal</span>
                        @endif
                        @if(!empty($product['limited_quantity']))
                            <span class="dolci-pill">Limited {{ $product['limited_quantity'] }}</span>
                        @endif
                        @if(!empty($product['preorder_enabled']))
                            <span class="dolci-pill">Pre-order</span>
                        @endif
                    </div>

                    @if(!empty($product['is_seasonal']) && !empty($product['seasonal_ends_at']))
                        <div class="dolci-countdown-inline" data-countdown data-end="{{ $product['seasonal_ends_at'] }}">
                            <p>Offer ends in <strong>--d --h --m --s</strong></p>
                        </div>
                    @endif
                @endif

                @php
                    $sizePrices = $product['size_prices'] ?? [];
                    $firstSize = array_key_first($sizePrices);
                    $firstSizePrice = (float) ($firstSize ? ($sizePrices[$firstSize] ?? 0) : 0);
                    $pepperPrice = round(max(0, (float) ($product['pepper_price'] ?? 0)), 2);
                    $packagingOptions = $product['packaging_options'] ?? [];
                @endphp

                <form method="POST" action="{{ route('thedolci.cart.add') }}" class="dolci-form-card">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                    <input type="hidden" name="redirect_to" value="cart">

                    <label>Choose Size</label>
                    <div class="dolci-size-grid" data-size-selector>
                        @foreach($sizePrices as $size => $price)
                            @php
                                $sizeSelected = old('size', $loop->first ? $size : null) === $size;
                            @endphp
                            <label class="dolci-size-option">
                                <input type="radio" name="size" value="{{ $size }}" data-size-option data-price="{{ $price }}" {{ $sizeSelected ? 'checked' : '' }}>
                                <span>{{ $size }} - JOD {{ number_format((float)$price, 2) }}</span>
                            </label>
                        @endforeach
                    </div>

                    @if($pepperPrice > 0)
                        <label class="dolci-checkbox">
                            <input type="checkbox" name="add_pepper" value="1" data-pepper-option data-price="{{ number_format($pepperPrice, 2, '.', '') }}" {{ old('add_pepper') ? 'checked' : '' }}>
                            <span>Add Pepper (+JOD {{ number_format($pepperPrice, 2) }})</span>
                        </label>
                    @endif

                    @if(!empty($packagingOptions))
                        <label>Packaging Type</label>
                        <select name="packaging_type" data-packaging-select>
                            <option value="" data-price="0" {{ old('packaging_type', '') === '' ? 'selected' : '' }}>No packaging</option>
                            @foreach($packagingOptions as $option)
                                @php
                                    $packagingName = trim((string) ($option['name'] ?? ''));
                                    $packagingPrice = round(max(0, (float) ($option['price'] ?? 0)), 2);
                                @endphp
                                @continue($packagingName === '')
                                <option value="{{ $packagingName }}" data-price="{{ number_format($packagingPrice, 2, '.', '') }}" {{ old('packaging_type') === $packagingName ? 'selected' : '' }}>
                                    {{ $packagingName }}{{ $packagingPrice > 0 ? ' (+' . 'JOD ' . number_format($packagingPrice, 2) . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <label>Quantity</label>
                    <input type="number" name="quantity" min="1" max="30" value="{{ old('quantity', 1) }}" required data-quantity-input>

                    <div class="dolci-price-row">
                        <p>Total: <strong data-dynamic-price>JOD {{ number_format($firstSizePrice, 2) }}</strong></p>
                        <button type="submit" class="dolci-btn dolci-btn-primary">Add to Cart</button>
                    </div>
                </form>

                <div class="dolci-trust-mini">
                    <p>Freshly prepared daily</p>
                    <p>No online payment required</p>
                    <p>Delivery or pickup scheduling</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dolci-section dolci-product-related">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>You May Also Like</h2>
        </div>
        <div class="dolci-product-grid">
            @foreach($relatedProducts as $item)
                <article class="dolci-product-card">
                    <a href="{{ route('thedolci.product', $item['slug']) }}">
                        <img src="{{ $item['cover_image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                    </a>
                    <div class="dolci-product-card-body">
                        <h3>{{ $item['name'] }}</h3>
                        <p>{{ $item['headline'] }}</p>
                        <a href="{{ route('thedolci.product', $item['slug']) }}" class="dolci-btn dolci-btn-compact">View Product</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection

