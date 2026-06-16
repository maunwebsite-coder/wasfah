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
                    <strong data-hero-stat="subtotal">JOD {{ number_format((float) $subtotal, 2) }}</strong>
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
                            $itemKey = (string) ($item['key'] ?? '');
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
                        <article class="dolci-cart-item dolci-cart-item-card" wire:key="thedolci-cart-item-{{ $itemKey }}">
                            <div class="dolci-cart-media">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                <span class="dolci-cart-qty-badge">x{{ (int) ($quantities[$itemKey] ?? $item['quantity']) }}</span>
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
                                    <strong class="dolci-cart-line-total">JOD {{ number_format((float) $item['line_total'], 2) }}</strong>
                                </div>

                                <div class="dolci-qty-form">
                                    <div class="dolci-qty-row">
                                        <label for="qty-{{ $itemKey }}" class="dolci-cart-label">Quantity</label>
                                        <div class="dolci-qty-stepper">
                                            <button
                                                type="button"
                                                class="dolci-qty-btn dolci-qty-btn-minus"
                                                wire:click="decrement('{{ $itemKey }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                                                aria-label="Decrease quantity"
                                            >-</button>
                                            <input
                                                id="qty-{{ $itemKey }}"
                                                type="number"
                                                class="dolci-qty-input"
                                                wire:model.lazy="quantities.{{ $itemKey }}"
                                                wire:change="updateQuantity('{{ $itemKey }}')"
                                                inputmode="numeric"
                                                min="1"
                                                max="30"
                                                wire:loading.attr="disabled"
                                                wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                                            >
                                            <button
                                                type="button"
                                                class="dolci-qty-btn dolci-qty-btn-plus"
                                                wire:click="increment('{{ $itemKey }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                                                aria-label="Increase quantity"
                                            >+</button>
                                        </div>
                                    </div>
                                </div>

                                @if($canUpdatePackaging)
                                    <div class="dolci-qty-form">
                                        <div class="dolci-qty-row">
                                            <label for="packaging-{{ $itemKey }}" class="dolci-cart-label">Packaging</label>
                                            <select
                                                id="packaging-{{ $itemKey }}"
                                                class="dolci-packaging-select"
                                                wire:model.lazy="packagingSelections.{{ $itemKey }}"
                                                wire:change="updatePackaging('{{ $itemKey }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                                            >
                                                <option value="">No packaging</option>
                                                @foreach($availablePackagingOptions as $option)
                                                    @php
                                                        $optionName = trim((string) ($option['name'] ?? ''));
                                                        $optionPrice = round(max(0, (float) ($option['price'] ?? 0)), 2);
                                                    @endphp
                                                    @continue($optionName === '')
                                                    <option value="{{ $optionName }}">
                                                        {{ $optionName }}{{ $optionPrice > 0 ? ' (+' . 'JOD ' . number_format($optionPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endforeach
                                                @if($packagingType !== '' && !$hasSelectedPackagingInOptions)
                                                    <option value="{{ $packagingType }}">
                                                        {{ $packagingType }}{{ $packagingPrice > 0 ? ' (+' . 'JOD ' . number_format($packagingPrice, 2) . ')' : '' }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <button
                                    type="button"
                                    class="dolci-btn dolci-btn-link"
                                    wire:click="removeItem('{{ $itemKey }}')"
                                    wire:confirm="Remove this item from cart?"
                                    wire:loading.attr="disabled"
                                    wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                                >
                                    Remove item
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="dolci-order-summary dolci-cart-summary">
                    <h3>Order Summary</h3>
                    <div class="dolci-summary-row"><span>Base items</span><span>JOD {{ number_format($baseItemsSubtotal, 2) }}</span></div>
                    @if($showPepperAddonsRow)
                        <div class="dolci-summary-row"><span>Pepper add-ons</span><span>JOD {{ number_format($pepperAddonsTotal, 2) }}</span></div>
                    @endif
                    @if($showPackagingAddonsRow)
                        <div class="dolci-summary-row"><span>Packaging add-ons</span><span>JOD {{ number_format($packagingAddonsTotal, 2) }}</span></div>
                    @endif
                    @if($showAddonsTotalRow)
                        <div class="dolci-summary-row"><span>Total add-ons</span><span>JOD {{ number_format($addonsTotal, 2) }}</span></div>
                    @endif
                    <div class="dolci-summary-row"><span>Subtotal</span><span>JOD {{ number_format((float) $subtotal, 2) }}</span></div>
                    @if($couponsEnabled)
                        <div class="dolci-summary-row"><span>Discount</span><span>- JOD {{ number_format((float) $discount, 2) }}</span></div>
                        @if(!empty($coupon['code']))
                            <div class="dolci-summary-row"><span>Coupon</span><span>{{ $coupon['code'] }}</span></div>
                        @endif
                    @endif
                    <div class="dolci-summary-row dolci-summary-total"><span>Total</span><span>JOD {{ number_format((float) $total, 2) }}</span></div>
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
                    <div class="dolci-clear-form">
                        <button
                            type="button"
                            class="dolci-btn dolci-btn-link"
                            wire:click="clearCart"
                            wire:confirm="Clear all items from cart?"
                            wire:loading.attr="disabled"
                            wire:target="increment,decrement,updateQuantity,updatePackaging,removeItem,clearCart"
                        >
                            Clear Cart
                        </button>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</section>
