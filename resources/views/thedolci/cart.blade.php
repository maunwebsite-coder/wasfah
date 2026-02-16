@extends('thedolci.layouts.store')

@section('title', 'Cart | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Your Cart</h1>
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
                            <div>
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
                                <form method="POST" action="{{ route('thedolci.cart.update', $item['key']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="30">
                                    <button type="submit" class="dolci-btn dolci-btn-compact">Update</button>
                                </form>

                                <form method="POST" action="{{ route('thedolci.cart.remove', $item['key']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dolci-btn dolci-btn-link">Remove</button>
                                </form>

                                <strong>JOD {{ number_format((float)$item['line_total'], 2) }}</strong>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="dolci-order-summary">
                    <h3>Order Summary</h3>
                    <div class="dolci-summary-row"><span>Subtotal</span><span>JOD {{ number_format((float)$subtotal, 2) }}</span></div>
                    <div class="dolci-summary-row"><span>Discount</span><span>- JOD {{ number_format((float)$discount, 2) }}</span></div>
                    <div class="dolci-summary-row dolci-summary-total"><span>Total</span><span>JOD {{ number_format((float)$total, 2) }}</span></div>

                    <form method="POST" action="{{ route('thedolci.cart.coupon') }}" class="dolci-coupon-form">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="Coupon code" value="{{ $coupon['code'] ?? '' }}">
                        <button type="submit" class="dolci-btn dolci-btn-secondary">Apply</button>
                    </form>

                    <a href="{{ route('thedolci.checkout') }}" class="dolci-btn dolci-btn-primary dolci-btn-block">Proceed to Checkout</a>
                </aside>
            </div>
        @endif
    </div>
</section>
@endsection

