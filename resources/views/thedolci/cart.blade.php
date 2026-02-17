@extends('thedolci.layouts.store')

@section('title', 'Cart | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Your Cart</h1>
            <p class="dolci-cart-meta">{{ $items->sum('quantity') }} item(s)</p>
        </div>

        @if($items->isEmpty())
            <div class="dolci-empty-state">
                <p>Your cart is empty.</p>
                <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-primary">Shop Tiramisu</a>
            </div>
        @else
            <div class="dolci-cart-layout">
                <div class="dolci-cart-items">
                    @foreach($items as $item)
                        <article class="dolci-cart-item">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                            <div class="dolci-cart-item-body">
                                <h3>{{ $item['name'] }}</h3>
                                <p>Size: {{ $item['size'] }}</p>
                                @if(!empty($item['customizations']['add_pepper']))
                                    <p>Pepper: Yes
                                        @if(!empty($item['customizations']['pepper_price']))
                                            (+JOD {{ number_format((float)$item['customizations']['pepper_price'], 2) }})
                                        @endif
                                    </p>
                                @endif
                                @if(!empty($item['customizations']['packaging_type']))
                                    <p>Packaging: {{ $item['customizations']['packaging_type'] }}
                                        @if(!empty($item['customizations']['packaging_price']))
                                            (+JOD {{ number_format((float)$item['customizations']['packaging_price'], 2) }})
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <div class="dolci-cart-actions">
                                <strong class="dolci-cart-line-total">JOD {{ number_format((float)$item['line_total'], 2) }}</strong>

                                <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-qty-form" novalidate>
                                    @csrf
                                    <input type="hidden" name="action" value="update">
                                    <label for="qty-{{ $item['key'] }}" class="dolci-cart-label">Quantity</label>
                                    <div class="dolci-qty-stepper">
                                        <button
                                            type="button"
                                            class="dolci-qty-btn"
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
                                            min="1"
                                            max="30"
                                        >
                                        <button
                                            type="button"
                                            class="dolci-qty-btn"
                                            data-qty-step="1"
                                            data-target="qty-{{ $item['key'] }}"
                                            aria-label="Increase quantity"
                                        >+</button>
                                    </div>
                                    <button type="submit" class="dolci-btn dolci-btn-compact">Update</button>
                                </form>

                                <form method="POST" action="{{ route('thedolci.cart.post', $item['key']) }}" class="dolci-remove-form">
                                    @csrf
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="dolci-btn dolci-btn-link" onclick="return confirm('Remove this item from cart?');">Remove</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="dolci-order-summary">
                    <h3>Order Summary</h3>
                    <div class="dolci-summary-row"><span>Subtotal</span><span>JOD {{ number_format((float)$subtotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Discount</span><span>- JOD {{ number_format((float)$discount, 2) }}</span></div>
                    @if(!empty($coupon['code']))
                        <div class="dolci-summary-row"><span>Coupon</span><span>{{ $coupon['code'] }}</span></div>
                    @endif
                    <div class="dolci-summary-row dolci-summary-total"><span>Total</span><span>JOD {{ number_format((float)$total, 2) }}</span></div>

                    <form method="POST" action="{{ route('thedolci.cart.coupon') }}" class="dolci-coupon-form">
                        @csrf
                        <input
                            type="text"
                            name="coupon_code"
                            placeholder="Coupon code"
                            value="{{ old('coupon_code', $coupon['code'] ?? session('thedolci.preferred_coupon', '')) }}"
                            maxlength="30"
                        >
                        <button type="submit" class="dolci-btn dolci-btn-secondary">Apply</button>
                    </form>
                    <p class="dolci-secure-note">Tip: Try coupon <strong>FIRST10</strong> on orders above JOD 20.</p>

                    <a href="{{ route('thedolci.checkout') }}" class="dolci-btn dolci-btn-primary dolci-btn-block">Proceed to Checkout</a>
                </aside>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-qty-step]').forEach((button) => {
        button.addEventListener('click', () => {
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
        });
    });
});
</script>
@endpush

