@extends('thedolci.layouts.store')

@section('title', 'Cart | thedolci')
@section('body_class', 'dolci-page-cart')

@section('content')
@php
    $itemCount = (int) $items->sum('quantity');
    $uniqueItems = (int) $items->count();
    $baseItemsSubtotal = 0.0;
    $pepperAddonsTotal = 0.0;
    $packagingAddonsTotal = 0.0;

    foreach ($items as $summaryItem) {
        $quantity = max(1, (int) ($summaryItem['quantity'] ?? 1));
        $lineTotal = (float) ($summaryItem['line_total'] ?? 0);
        $pepperUnitPrice = !empty(data_get($summaryItem, 'customizations.add_pepper'))
            ? max(0, (float) data_get($summaryItem, 'customizations.pepper_price', 0))
            : 0.0;
        $packagingUnitPrice = !empty(data_get($summaryItem, 'customizations.packaging_type'))
            ? max(0, (float) data_get($summaryItem, 'customizations.packaging_price', 0))
            : 0.0;

        $pepperLineTotal = round($pepperUnitPrice * $quantity, 2);
        $packagingLineTotal = round($packagingUnitPrice * $quantity, 2);
        $addonsLineTotal = round($pepperLineTotal + $packagingLineTotal, 2);
        $baseLineTotal = round(max(0, $lineTotal - $addonsLineTotal), 2);

        $baseItemsSubtotal += $baseLineTotal;
        $pepperAddonsTotal += $pepperLineTotal;
        $packagingAddonsTotal += $packagingLineTotal;
    }

    $baseItemsSubtotal = round($baseItemsSubtotal, 2);
    $pepperAddonsTotal = round($pepperAddonsTotal, 2);
    $packagingAddonsTotal = round($packagingAddonsTotal, 2);
    $addonsTotal = round($pepperAddonsTotal + $packagingAddonsTotal, 2);
@endphp
<section class="dolci-section dolci-section-tight dolci-cart-section">
    <div class="dolci-container">
        <div class="dolci-cart-hero">
            <p class="dolci-kicker">Ready to checkout</p>
            <h1>Your Cart</h1>
            <p>Adjust quantities and continue to secure checkout.</p>
            <div class="dolci-cart-hero-stats">
                <article>
                    <strong>{{ $itemCount }}</strong>
                    <span>Total items</span>
                </article>
                <article>
                    <strong>{{ $uniqueItems }}</strong>
                    <span>Unique selections</span>
                </article>
                <article>
                    <strong>JOD {{ number_format((float)$subtotal, 2) }}</strong>
                    <span>Subtotal before discounts</span>
                </article>
            </div>
        </div>

        @if($items->isEmpty())
            <div class="dolci-empty-state dolci-empty-cart">
                <h2>Your cart is empty</h2>
                <p>Discover signature tiramisu flavors and seasonal drops freshly made every day.</p>
                <div class="dolci-empty-actions">
                    <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-primary">Shop Tiramisu</a>
                    <a href="{{ route('thedolci.seasonal') }}" class="dolci-btn dolci-btn-secondary">See Seasonal Drops</a>
                </div>
            </div>
        @else
            <div class="dolci-cart-layout">
                <div class="dolci-cart-items">
                    @foreach($items as $item)
                        @php
                            $pepperPrice = (float) ($item['customizations']['pepper_price'] ?? 0);
                            $packagingPrice = (float) ($item['customizations']['packaging_price'] ?? 0);
                            $packagingType = trim((string) ($item['customizations']['packaging_type'] ?? ''));
                            $hasExtras = !empty($item['customizations']['add_pepper']) || !empty($item['customizations']['packaging_type']);
                            $availablePackagingOptions = collect($item['available_packaging_options'] ?? [])
                                ->filter(fn ($option) => is_array($option) && trim((string) ($option['name'] ?? '')) !== '')
                                ->values();
                            $hasSelectedPackagingInOptions = $packagingType !== ''
                                && $availablePackagingOptions->contains(fn ($option) => strtolower((string) ($option['name'] ?? '')) === strtolower($packagingType));
                            $canUpdatePackaging = $availablePackagingOptions->isNotEmpty() || $packagingType !== '';
                        @endphp
                        <article class="dolci-cart-item dolci-cart-item-card">
                            <div class="dolci-cart-media">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                <span class="dolci-cart-qty-badge">x{{ $item['quantity'] }}</span>
                            </div>

                            <div class="dolci-cart-item-body">
                                <div class="dolci-cart-item-head">
                                    <h3>{{ $item['name'] }}</h3>
                                    <span class="dolci-pill">Size: {{ $item['size'] }}</span>
                                </div>
                                <div class="dolci-cart-option-list">
                                    @if(!empty($item['customizations']['add_pepper']))
                                        <span>
                                            Pepper
                                            @if($pepperPrice > 0)
                                                (+JOD {{ number_format($pepperPrice, 2) }})
                                            @endif
                                        </span>
                                    @endif
                                    @if(!empty($item['customizations']['packaging_type']))
                                        <span>
                                            Packaging: {{ $packagingType }}
                                            @if($packagingPrice > 0)
                                                (+JOD {{ number_format($packagingPrice, 2) }})
                                            @endif
                                        </span>
                                    @endif
                                    @if(!$hasExtras)
                                        <span>Classic build</span>
                                    @endif
                                </div>
                            </div>

                            <div class="dolci-cart-actions">
                                <div class="dolci-cart-price-stack">
                                    <span class="dolci-cart-line-label">Line total</span>
                                    <strong class="dolci-cart-line-total">JOD {{ number_format((float)$item['line_total'], 2) }}</strong>
                                </div>

                                <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-qty-form" novalidate data-auto-submit="quantity">
                                    @csrf
                                    <input type="hidden" name="action" value="update">
                                    <div class="dolci-qty-row">
                                        <label for="qty-{{ $item['key'] }}" class="dolci-cart-label">Quantity</label>
                                        <div class="dolci-qty-stepper">
                                            <button
                                                type="button"
                                                class="dolci-qty-btn dolci-qty-btn-minus"
                                                data-qty-step="-1"
                                                data-target="qty-{{ $item['key'] }}"
                                                aria-label="Decrease quantity"
                                            >-</button>
                                            <input
                                                id="qty-{{ $item['key'] }}"
                                                type="number"
                                                name="quantity"
                                                class="dolci-qty-input"
                                                value="{{ $item['quantity'] }}"
                                                inputmode="numeric"
                                                min="1"
                                                max="30"
                                            >
                                            <button
                                                type="button"
                                                class="dolci-qty-btn dolci-qty-btn-plus"
                                                data-qty-step="1"
                                                data-target="qty-{{ $item['key'] }}"
                                                aria-label="Increase quantity"
                                            >+</button>
                                        </div>
                                    </div>
                                </form>

                                @if($canUpdatePackaging)
                                    <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-qty-form" novalidate data-auto-submit="packaging">
                                        @csrf
                                        <input type="hidden" name="action" value="update-packaging">
                                        <div class="dolci-qty-row">
                                            <label for="packaging-{{ $item['key'] }}" class="dolci-cart-label">Packaging</label>
                                            <select id="packaging-{{ $item['key'] }}" name="packaging_type" class="dolci-packaging-select">
                                                <option value="" {{ $packagingType === '' ? 'selected' : '' }}>No packaging</option>
                                                @foreach($availablePackagingOptions as $option)
                                                    @php
                                                        $optionName = trim((string) ($option['name'] ?? ''));
                                                        $optionPrice = round(max(0, (float) ($option['price'] ?? 0)), 2);
                                                    @endphp
                                                    @continue($optionName === '')
                                                    <option value="{{ $optionName }}" {{ strtolower($packagingType) === strtolower($optionName) ? 'selected' : '' }}>
                                                        {{ $optionName }}{{ $optionPrice > 0 ? ' (+' . 'JOD ' . number_format($optionPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endforeach
                                                @if($packagingType !== '' && !$hasSelectedPackagingInOptions)
                                                    <option value="{{ $packagingType }}" selected>
                                                        {{ $packagingType }}{{ $packagingPrice > 0 ? ' (+' . 'JOD ' . number_format($packagingPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-remove-form">
                                    @csrf
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="dolci-btn dolci-btn-link" onclick="return confirm('Remove this item from cart?');">Remove item</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="dolci-order-summary dolci-cart-summary">
                    <h3>Order Summary</h3>
                    <div class="dolci-summary-row"><span>Base items</span><span>JOD {{ number_format($baseItemsSubtotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Pepper add-ons</span><span>JOD {{ number_format($pepperAddonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Packaging add-ons</span><span>JOD {{ number_format($packagingAddonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Total add-ons</span><span>JOD {{ number_format($addonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Subtotal</span><span>JOD {{ number_format((float)$subtotal, 2) }}</span></div>
                    @if($couponsEnabled)
                        <div class="dolci-summary-row"><span>Discount</span><span>- JOD {{ number_format((float)$discount, 2) }}</span></div>
                        @if(!empty($coupon['code']))
                            <div class="dolci-summary-row"><span>Coupon</span><span>{{ $coupon['code'] }}</span></div>
                        @endif
                    @endif
                    <div class="dolci-summary-row dolci-summary-total"><span>Total</span><span>JOD {{ number_format((float)$total, 2) }}</span></div>

                    @if($couponsEnabled)
                        <form method="POST" action="{{ route('thedolci.cart.coupon') }}" class="dolci-coupon-form dolci-coupon-form-compact">
                            @csrf
                            <input
                                type="text"
                                name="coupon_code"
                                placeholder="Coupon code"
                                value="{{ old('coupon_code', $coupon['code'] ?? session('thedolci.preferred_coupon', '')) }}"
                                maxlength="30"
                            >
                            <button type="submit" class="dolci-btn dolci-btn-secondary">Apply Coupon</button>
                        </form>
                    @endif

                    <a href="{{ route('thedolci.checkout') }}" class="dolci-btn dolci-btn-primary dolci-btn-block">Proceed to Checkout</a>
                    <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-secondary dolci-btn-block">Continue Shopping</a>
                </aside>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const submitTimers = new WeakMap();
    const qtySubmitDelayMs = 350;

    const clampQuantity = (input) => {
        const min = Number(input.getAttribute('min') || 1);
        const max = Number(input.getAttribute('max') || 30);
        const current = Number(input.value || min);
        const clamped = Math.min(max, Math.max(min, current));
        input.value = String(clamped);
    };

    const syncPageFromHtml = (html) => {
        const parser = new DOMParser();
        const nextDocument = parser.parseFromString(html, 'text/html');
        const currentMain = document.querySelector('main.dolci-main');
        const nextMain = nextDocument.querySelector('main.dolci-main');

        if (!currentMain || !nextMain) {
            window.location.reload();
            return;
        }

        currentMain.innerHTML = nextMain.innerHTML;

        const nextCartCounts = Array.from(nextDocument.querySelectorAll('.dolci-cart-count'));
        const currentCartCounts = Array.from(document.querySelectorAll('.dolci-cart-count'));

        if (nextCartCounts.length === 0 || currentCartCounts.length === 0) {
            return;
        }

        const fallbackCount = nextCartCounts[0].textContent ?? '0';
        currentCartCounts.forEach((el, index) => {
            const source = nextCartCounts[index] ?? nextCartCounts[0];
            el.textContent = source.textContent ?? fallbackCount;
        });
    };

    const submitForm = async (form) => {
        if (!form || form.dataset.isSubmitting === '1') {
            return;
        }

        form.dataset.isSubmitting = '1';

        try {
            const response = await fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            if (!response.ok) {
                throw new Error('Cart request failed');
            }

            const html = await response.text();
            syncPageFromHtml(html);
        } catch (error) {
            form.submit();
        } finally {
            if (form.isConnected) {
                form.dataset.isSubmitting = '0';
            }
        }
    };

    const scheduleQuantitySubmit = (form) => {
        if (!form) {
            return;
        }

        const previous = submitTimers.get(form);
        if (previous) {
            window.clearTimeout(previous);
        }

        const timer = window.setTimeout(() => {
            submitTimers.delete(form);
            submitForm(form);
        }, qtySubmitDelayMs);

        submitTimers.set(form, timer);
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-qty-step]');
        if (!button) {
            return;
        }

        const targetId = button.getAttribute('data-target');
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        const min = Number(input.getAttribute('min') || 1);
        const max = Number(input.getAttribute('max') || 30);
        const step = Number(button.getAttribute('data-qty-step') || 0);
        const current = Number(input.value || min);
        const next = Math.min(max, Math.max(min, current + step));

        input.value = String(next);
        scheduleQuantitySubmit(input.form);
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('.dolci-qty-form[data-auto-submit="quantity"] .dolci-qty-input')) {
            const input = target;
            clampQuantity(input);
            submitForm(input.form);
            return;
        }

        if (target.matches('.dolci-qty-form[data-auto-submit="packaging"] .dolci-packaging-select')) {
            const select = target;
            submitForm(select.form);
        }
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (!target.matches('.dolci-qty-form[data-auto-submit="quantity"] .dolci-qty-input')) {
            return;
        }

        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const input = target;
        clampQuantity(input);
        submitForm(input.form);
    });
});
</script>
@endpush

