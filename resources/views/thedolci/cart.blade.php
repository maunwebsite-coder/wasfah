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
    $pepperAddonsSelectedCount = 0;
    $packagingAddonsSelectedCount = 0;

    foreach ($items as $summaryItem) {
        $quantity = max(1, (int) ($summaryItem['quantity'] ?? 1));
        $lineTotal = (float) ($summaryItem['line_total'] ?? 0);
        $hasPepperAddon = !empty(data_get($summaryItem, 'customizations.add_pepper'));
        $hasPackagingAddon = trim((string) data_get($summaryItem, 'customizations.packaging_type', '')) !== '';
        $pepperUnitPrice = $hasPepperAddon
            ? max(0, (float) data_get($summaryItem, 'customizations.pepper_price', 0))
            : 0.0;
        $packagingUnitPrice = $hasPackagingAddon
            ? max(0, (float) data_get($summaryItem, 'customizations.packaging_price', 0))
            : 0.0;

        $pepperLineTotal = round($pepperUnitPrice * $quantity, 2);
        $packagingLineTotal = round($packagingUnitPrice * $quantity, 2);
        $addonsLineTotal = round($pepperLineTotal + $packagingLineTotal, 2);
        $baseLineTotal = round(max(0, $lineTotal - $addonsLineTotal), 2);

        $baseItemsSubtotal += $baseLineTotal;
        $pepperAddonsTotal += $pepperLineTotal;
        $packagingAddonsTotal += $packagingLineTotal;

        if ($hasPepperAddon) {
            $pepperAddonsSelectedCount += $quantity;
        }

        if ($hasPackagingAddon) {
            $packagingAddonsSelectedCount += $quantity;
        }
    }

    $baseItemsSubtotal = round($baseItemsSubtotal, 2);
    $pepperAddonsTotal = round($pepperAddonsTotal, 2);
    $packagingAddonsTotal = round($packagingAddonsTotal, 2);
    $addonsTotal = round($pepperAddonsTotal + $packagingAddonsTotal, 2);
    $showPepperAddonsRow = $pepperAddonsSelectedCount > 0;
    $showPackagingAddonsRow = $packagingAddonsSelectedCount > 0;
    $showAddonsTotalRow = $showPepperAddonsRow && $showPackagingAddonsRow;
    $limitedSelectionsCount = (int) $items->filter(function ($item) {
        return (bool) data_get($item, 'inventory.is_limited', false);
    })->count();
    $cartSyncMessages = collect($cartSyncMessages ?? [])->filter()->values();
    $healthScore = max(0, min(100, 100 - ($cartSyncMessages->count() * 15)));
@endphp
<section class="dolci-section dolci-section-tight dolci-cart-section">
    <div class="dolci-container">
        <div class="dolci-cart-hero">
            <p class="dolci-kicker">Ready to checkout</p>
            <h1>Your Cart</h1>
            <p>Adjust quantities and continue to secure checkout.</p>
            <div class="dolci-cart-health" role="status" aria-live="polite">
                <div class="dolci-cart-health-head">
                    <span>Cart health</span>
                    <strong>{{ $healthScore }}%</strong>
                </div>
                <div class="dolci-cart-health-track" aria-hidden="true">
                    <span style="width: {{ number_format($healthScore, 2, '.', '') }}%;"></span>
                </div>
                <div class="dolci-cart-health-meta">
                    <span>Limited stock items monitored: {{ $limitedSelectionsCount }}</span>
                    <span>Price and packaging auto-validation enabled</span>
                </div>
                @if($cartSyncMessages->isNotEmpty())
                    <ul class="dolci-cart-health-list">
                        @foreach($cartSyncMessages as $syncMessage)
                            <li>{{ $syncMessage }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="dolci-cart-hero-stats">
                <article>
                    <strong data-hero-stat="total-items">{{ $itemCount }}</strong>
                    <span>Total items</span>
                </article>
                <article>
                    <strong data-hero-stat="unique-items">{{ $uniqueItems }}</strong>
                    <span>Unique selections</span>
                </article>
                <article>
                    <strong data-hero-stat="subtotal">JOD {{ number_format((float)$subtotal, 2) }}</strong>
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
                            $baseUnitPrice = round(max(0, (float) ($item['unit_price'] ?? 0) - $pepperPrice - $packagingPrice), 2);
                            $hasExtras = !empty($item['customizations']['add_pepper']) || !empty($item['customizations']['packaging_type']);
                            $availablePackagingOptions = collect($item['available_packaging_options'] ?? [])
                                ->filter(fn ($option) => is_array($option) && trim((string) ($option['name'] ?? '')) !== '')
                                ->values();
                            $hasSelectedPackagingInOptions = $packagingType !== ''
                                && $availablePackagingOptions->contains(fn ($option) => strtolower((string) ($option['name'] ?? '')) === strtolower($packagingType));
                            $canUpdatePackaging = $availablePackagingOptions->isNotEmpty() || $packagingType !== '';
                        @endphp
                        <article
                            class="dolci-cart-item dolci-cart-item-card"
                            data-cart-item
                            data-base-unit-price="{{ number_format($baseUnitPrice, 2, '.', '') }}"
                            data-pepper-unit-price="{{ number_format(max(0, $pepperPrice), 2, '.', '') }}"
                            data-packaging-unit-price="{{ number_format(max(0, $packagingPrice), 2, '.', '') }}"
                            data-has-pepper="{{ !empty($item['customizations']['add_pepper']) ? '1' : '0' }}"
                            data-has-packaging="{{ $packagingType !== '' ? '1' : '0' }}"
                        >
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
                                @if(data_get($item, 'inventory.is_limited'))
                                    <div class="dolci-cart-stock">
                                        <span class="dolci-cart-stock-pill">
                                            Available now: {{ max(0, (int) data_get($item, 'inventory.available_for_product', 0)) }}
                                        </span>
                                        @if((int) data_get($item, 'inventory.remaining_after_line', 0) <= 0)
                                            <span class="dolci-cart-stock-pill is-critical">All available units are currently in your cart</span>
                                        @else
                                            <span class="dolci-cart-stock-pill is-warning">
                                                Remaining after this line: {{ max(0, (int) data_get($item, 'inventory.remaining_after_line', 0)) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="dolci-cart-actions">
                                <div class="dolci-cart-price-stack">
                                    <span class="dolci-cart-line-label">Line total</span>
                                    <strong class="dolci-cart-line-total" data-line-total>JOD {{ number_format((float)$item['line_total'], 2) }}</strong>
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
                                                <option value="" data-price="0" {{ $packagingType === '' ? 'selected' : '' }}>No packaging</option>
                                                @foreach($availablePackagingOptions as $option)
                                                    @php
                                                        $optionName = trim((string) ($option['name'] ?? ''));
                                                        $optionPrice = round(max(0, (float) ($option['price'] ?? 0)), 2);
                                                    @endphp
                                                    @continue($optionName === '')
                                                    <option value="{{ $optionName }}" data-price="{{ number_format($optionPrice, 2, '.', '') }}" {{ strtolower($packagingType) === strtolower($optionName) ? 'selected' : '' }}>
                                                        {{ $optionName }}{{ $optionPrice > 0 ? ' (+' . 'JOD ' . number_format($optionPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endforeach
                                                @if($packagingType !== '' && !$hasSelectedPackagingInOptions)
                                                    <option value="{{ $packagingType }}" data-price="{{ number_format(max(0, $packagingPrice), 2, '.', '') }}" selected>
                                                        {{ $packagingType }}{{ $packagingPrice > 0 ? ' (+' . 'JOD ' . number_format($packagingPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-remove-form" data-auto-submit="remove">
                                    @csrf
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="dolci-btn dolci-btn-link">Remove item</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="dolci-order-summary dolci-cart-summary">
                    <h3>Order Summary</h3>
                    <div class="dolci-summary-row" data-summary-row="base-items"><span>Base items</span><span data-summary-value="base-items">JOD {{ number_format($baseItemsSubtotal, 2) }}</span></div>
                    <div class="dolci-summary-row" data-summary-row="pepper-addons" @if(!$showPepperAddonsRow) style="display:none;" @endif><span>Pepper add-ons</span><span data-summary-value="pepper-addons">JOD {{ number_format($pepperAddonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row" data-summary-row="packaging-addons" @if(!$showPackagingAddonsRow) style="display:none;" @endif><span>Packaging add-ons</span><span data-summary-value="packaging-addons">JOD {{ number_format($packagingAddonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row" data-summary-row="addons-total" @if(!$showAddonsTotalRow) style="display:none;" @endif><span>Total add-ons</span><span data-summary-value="addons-total">JOD {{ number_format($addonsTotal, 2) }}</span></div>
                    <div class="dolci-summary-row" data-summary-row="subtotal"><span>Subtotal</span><span data-summary-value="subtotal">JOD {{ number_format((float)$subtotal, 2) }}</span></div>
                    @if($couponsEnabled)
                        <div class="dolci-summary-row" data-summary-row="discount"><span>Discount</span><span data-summary-value="discount">- JOD {{ number_format((float)$discount, 2) }}</span></div>
                        @if(!empty($coupon['code']))
                            <div class="dolci-summary-row"><span>Coupon</span><span>{{ $coupon['code'] }}</span></div>
                        @endif
                    @endif
                    <div class="dolci-summary-row dolci-summary-total" data-summary-row="total"><span>Total</span><span data-summary-value="total">JOD {{ number_format((float)$total, 2) }}</span></div>
                    <div class="dolci-cart-insight-grid">
                        <article class="dolci-cart-insight">
                            <span>Inventory Guard</span>
                            <strong>{{ $limitedSelectionsCount }} limited selection{{ $limitedSelectionsCount === 1 ? '' : 's' }} tracked</strong>
                            <small>Quantities are auto-adjusted if stock changes.</small>
                        </article>
                        <article class="dolci-cart-insight">
                            <span>Checkout Safety</span>
                            <strong>{{ $healthScore }}% cart integrity</strong>
                            <small>Current prices, packaging, and availability are validated in real time.</small>
                        </article>
                    </div>

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
                    <form method="POST" action="{{ route('thedolci.cart.clear') }}" class="dolci-clear-form" data-auto-submit="clear">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dolci-btn dolci-btn-link">Clear Cart</button>
                    </form>
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
    const qtySubmitDelayMs = 220;
    const currencyPrefix = 'JOD ';
    let activeController = null;
    const supportsAbortController = typeof AbortController !== 'undefined';
    const cartPageUrl = @json(route('thedolci.cart'));

    const readNumber = (value, fallback = 0) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    };

    const parseMoney = (value) => {
        const normalized = String(value || '')
            .replace(/,/g, '')
            .replace(/[^0-9.-]/g, '');
        return readNumber(normalized, 0);
    };

    const formatMoney = (amount) => `${currencyPrefix}${amount.toFixed(2)}`;
    const formatDiscount = (amount) => `- ${currencyPrefix}${Math.abs(amount).toFixed(2)}`;
    const fallbackToCartPage = () => {
        window.location.href = cartPageUrl;
    };

    const clampQuantity = (input) => {
        const min = readNumber(input.getAttribute('min'), 1);
        const max = readNumber(input.getAttribute('max'), 30);
        const current = readNumber(input.value, min);
        const clamped = Math.min(max, Math.max(min, current));
        input.value = String(clamped);
        return clamped;
    };

    const setText = (selector, value) => {
        const node = document.querySelector(selector);
        if (node) {
            node.textContent = value;
        }
    };

    const updateSummaryRowVisibility = (key, visible) => {
        const row = document.querySelector(`[data-summary-row="${key}"]`);
        if (!row) {
            return;
        }

        row.style.display = visible ? '' : 'none';
    };

    const updateItemPreview = (item) => {
        const quantityInput = item.querySelector('.dolci-qty-input');
        const quantity = quantityInput instanceof HTMLInputElement ? clampQuantity(quantityInput) : 1;

        const baseUnitPrice = readNumber(item.dataset.baseUnitPrice, 0);
        const pepperUnitPrice = readNumber(item.dataset.pepperUnitPrice, 0);
        const hasPepper = item.dataset.hasPepper === '1';

        let packagingUnitPrice = readNumber(item.dataset.packagingUnitPrice, 0);
        let hasPackaging = item.dataset.hasPackaging === '1';

        const packagingSelect = item.querySelector('.dolci-packaging-select');
        if (packagingSelect instanceof HTMLSelectElement) {
            const selectedOption = packagingSelect.options[packagingSelect.selectedIndex];
            packagingUnitPrice = readNumber(selectedOption?.getAttribute('data-price'), 0);
            hasPackaging = packagingSelect.value.trim() !== '';
        }

        item.dataset.packagingUnitPrice = packagingUnitPrice.toFixed(2);
        item.dataset.hasPackaging = hasPackaging ? '1' : '0';

        const baseLineTotal = quantity * baseUnitPrice;
        const pepperLineTotal = hasPepper ? quantity * pepperUnitPrice : 0;
        const packagingLineTotal = hasPackaging ? quantity * packagingUnitPrice : 0;
        const lineTotal = baseLineTotal + pepperLineTotal + packagingLineTotal;

        const lineTotalNode = item.querySelector('[data-line-total]');
        if (lineTotalNode) {
            lineTotalNode.textContent = formatMoney(lineTotal);
        }

        const qtyBadge = item.querySelector('.dolci-cart-qty-badge');
        if (qtyBadge) {
            qtyBadge.textContent = `x${quantity}`;
        }

        return {
            quantity,
            hasPepper,
            hasPackaging,
            baseLineTotal,
            pepperLineTotal,
            packagingLineTotal,
            lineTotal,
        };
    };

    const updateCartPreviewTotals = () => {
        const items = Array.from(document.querySelectorAll('[data-cart-item]'));

        if (!items.length) {
            return;
        }

        let itemCount = 0;
        let baseItemsSubtotal = 0;
        let pepperAddonsTotal = 0;
        let packagingAddonsTotal = 0;
        let pepperSelectedCount = 0;
        let packagingSelectedCount = 0;
        let subtotal = 0;

        items.forEach((item) => {
            const totals = updateItemPreview(item);

            itemCount += totals.quantity;
            baseItemsSubtotal += totals.baseLineTotal;
            pepperAddonsTotal += totals.pepperLineTotal;
            packagingAddonsTotal += totals.packagingLineTotal;
            subtotal += totals.lineTotal;

            if (totals.hasPepper) {
                pepperSelectedCount += totals.quantity;
            }

            if (totals.hasPackaging) {
                packagingSelectedCount += totals.quantity;
            }
        });

        const addonsTotal = pepperAddonsTotal + packagingAddonsTotal;
        const discountNode = document.querySelector('[data-summary-value="discount"]');
        const discount = discountNode ? Math.abs(parseMoney(discountNode.textContent)) : 0;
        const total = Math.max(0, subtotal - discount);

        setText('[data-summary-value="base-items"]', formatMoney(baseItemsSubtotal));
        setText('[data-summary-value="pepper-addons"]', formatMoney(pepperAddonsTotal));
        setText('[data-summary-value="packaging-addons"]', formatMoney(packagingAddonsTotal));
        setText('[data-summary-value="addons-total"]', formatMoney(addonsTotal));
        setText('[data-summary-value="subtotal"]', formatMoney(subtotal));
        setText('[data-summary-value="total"]', formatMoney(total));

        if (discountNode) {
            discountNode.textContent = formatDiscount(discount);
        }

        updateSummaryRowVisibility('pepper-addons', pepperSelectedCount > 0);
        updateSummaryRowVisibility('packaging-addons', packagingSelectedCount > 0);
        updateSummaryRowVisibility('addons-total', pepperSelectedCount > 0 && packagingSelectedCount > 0);

        setText('[data-hero-stat="total-items"]', String(itemCount));
        setText('[data-hero-stat="unique-items"]', String(items.length));
        setText('[data-hero-stat="subtotal"]', formatMoney(subtotal));

        document.querySelectorAll('.dolci-cart-count').forEach((node) => {
            node.textContent = String(itemCount);
        });
    };

    const clearScheduledSubmit = (form) => {
        if (!form) {
            return;
        }

        const previous = submitTimers.get(form);
        if (previous) {
            window.clearTimeout(previous);
            submitTimers.delete(form);
        }
    };

    const syncFlashAlerts = (nextDocument) => {
        const currentMain = document.querySelector('.dolci-main');
        const nextMain = nextDocument.querySelector('.dolci-main');
        if (!currentMain || !nextMain) {
            return;
        }

        currentMain.querySelectorAll(':scope > .dolci-alert').forEach((alert) => alert.remove());

        const nextAlerts = Array.from(nextMain.querySelectorAll(':scope > .dolci-alert'));
        const firstSection = currentMain.querySelector(':scope > .dolci-cart-section');

        nextAlerts.reverse().forEach((alert) => {
            const clone = alert.cloneNode(true);
            if (firstSection) {
                currentMain.insertBefore(clone, firstSection);
            } else {
                currentMain.prepend(clone);
            }
        });
    };

    const syncPageFromHtml = (html) => {
        const parser = new DOMParser();
        const nextDocument = parser.parseFromString(html, 'text/html');
        const currentCartContainer = document.querySelector('.dolci-cart-section .dolci-container');
        const nextCartContainer = nextDocument.querySelector('.dolci-cart-section .dolci-container');

        if (!currentCartContainer || !nextCartContainer) {
            fallbackToCartPage();
            return;
        }

        currentCartContainer.replaceWith(nextCartContainer);
        syncFlashAlerts(nextDocument);

        const nextCartCounts = Array.from(nextDocument.querySelectorAll('.dolci-cart-count'));
        const currentCartCounts = Array.from(document.querySelectorAll('.dolci-cart-count'));

        if (nextCartCounts.length > 0 && currentCartCounts.length > 0) {
            const fallbackCount = nextCartCounts[0].textContent ?? '0';
            currentCartCounts.forEach((el, index) => {
                const source = nextCartCounts[index] ?? nextCartCounts[0];
                el.textContent = source.textContent ?? fallbackCount;
            });
        }

        updateCartPreviewTotals();
    };

    const setFormBusy = (form, isBusy) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.querySelectorAll('button, input, select, textarea').forEach((field) => {
            if (!(field instanceof HTMLElement)) {
                return;
            }

            if (field instanceof HTMLInputElement && field.type === 'hidden') {
                return;
            }

            if ('disabled' in field) {
                field.disabled = isBusy;
            }
        });

        form.classList.toggle('is-busy', isBusy);
    };

    const submitForm = async (form) => {
        if (!form || form.dataset.isSubmitting === '1') {
            return;
        }

        const formData = new FormData(form);
        clearScheduledSubmit(form);
        form.dataset.isSubmitting = '1';
        setFormBusy(form, true);

        let controller = null;
        if (supportsAbortController) {
            if (activeController) {
                activeController.abort();
            }

            controller = new AbortController();
            activeController = controller;
        }

        try {
            const requestInit = {
                method: (form.method || 'POST').toUpperCase(),
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            };

            if (controller) {
                requestInit.signal = controller.signal;
            }

            const response = await fetch(form.action, requestInit);

            if (!response.ok) {
                throw new Error('Cart request failed');
            }

            const html = await response.text();
            syncPageFromHtml(html);
        } catch (error) {
            if (supportsAbortController && error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            console.error('Cart async update failed:', error);
            fallbackToCartPage();
        } finally {
            if (supportsAbortController && activeController === controller) {
                activeController = null;
            }

            if (form.isConnected) {
                setFormBusy(form, false);
                form.dataset.isSubmitting = '0';
            }
        }
    };

    const scheduleQuantitySubmit = (form) => {
        if (!form) {
            return;
        }

        clearScheduledSubmit(form);

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
        updateCartPreviewTotals();
        scheduleQuantitySubmit(input.form);
    });

    document.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (!target.matches('.dolci-qty-form[data-auto-submit="quantity"] .dolci-qty-input')) {
            return;
        }

        const input = target;
        clampQuantity(input);
        updateCartPreviewTotals();
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
            updateCartPreviewTotals();
            submitForm(input.form);
            return;
        }

        if (target.matches('.dolci-qty-form[data-auto-submit="packaging"] .dolci-packaging-select')) {
            const select = target;
            updateCartPreviewTotals();
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
        updateCartPreviewTotals();
        submitForm(input.form);
    });

    document.addEventListener('submit', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        if (!target.matches('.dolci-qty-form[data-auto-submit="quantity"], .dolci-qty-form[data-auto-submit="packaging"], .dolci-remove-form[data-auto-submit="remove"], .dolci-clear-form[data-auto-submit="clear"]')) {
            return;
        }

        event.preventDefault();

        if (target.matches('.dolci-qty-form[data-auto-submit="quantity"]')) {
            const qtyInput = target.querySelector('.dolci-qty-input');
            if (qtyInput instanceof HTMLInputElement) {
                clampQuantity(qtyInput);
            }

            updateCartPreviewTotals();
        }

        if (target.matches('.dolci-remove-form[data-auto-submit="remove"]')) {
            if (!window.confirm('Remove this item from cart?')) {
                return;
            }
        }

        if (target.matches('.dolci-clear-form[data-auto-submit="clear"]')) {
            if (!window.confirm('Clear all items from cart?')) {
                return;
            }
        }

        submitForm(target);
    });

    updateCartPreviewTotals();
});
</script>
@endpush

