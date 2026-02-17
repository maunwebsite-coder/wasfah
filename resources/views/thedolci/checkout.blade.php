@extends('thedolci.layouts.store')

@section('title', 'Checkout | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Complete Your Order</h1>
            <p>No online payment. After placing order, send it to WhatsApp for Click payment confirmation.</p>
        </div>

        <div class="dolci-checkout-layout">
            <form method="POST" action="{{ route('thedolci.checkout.place') }}" class="dolci-form-card">
                @csrf

                <h3>Contact Information</h3>
                <div class="dolci-form-grid-2">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    <div>
                        <label>Phone</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required>
                    </div>
                </div>

                <label>Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" required>

                <h3>Fulfillment</h3>
                <div class="dolci-inline-options">
                    <label><input type="radio" name="fulfillment_type" value="delivery" {{ old('fulfillment_type', 'delivery') === 'delivery' ? 'checked' : '' }}> Delivery</label>
                    <label><input type="radio" name="fulfillment_type" value="pickup" {{ old('fulfillment_type') === 'pickup' ? 'checked' : '' }}> Pickup</label>
                </div>

                <label>Delivery Address (required for delivery)</label>
                <textarea name="delivery_address" rows="3" placeholder="Street, building, apartment">{{ old('delivery_address') }}</textarea>

                <div class="dolci-form-grid-2">
                    <div>
                        <label>Date</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date', now()->addDay()->toDateString()) }}" required>
                    </div>
                    <div>
                        <label>Time Slot</label>
                        <select name="delivery_time" required>
                            @foreach($deliverySlots as $slot)
                                <option value="{{ $slot }}" {{ old('delivery_time') === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label>Coupon Code</label>
                <input type="text" name="coupon_code" value="{{ old('coupon_code', $coupon['code'] ?? session('thedolci.preferred_coupon', '')) }}" placeholder="FIRST10">

                <label>Order Notes</label>
                <textarea name="notes" rows="3" placeholder="Optional">{{ old('notes') }}</textarea>

                <button type="submit" class="dolci-btn dolci-btn-primary dolci-btn-block">Confirm Order</button>
            </form>

            <aside class="dolci-order-summary">
                <h3>Order Summary</h3>
                @foreach($items as $item)
                    <div class="dolci-summary-line-item">
                        <span>
                            {{ $item['name'] }} ({{ $item['size'] }}) x {{ $item['quantity'] }}
                            @if(!empty($item['customizations']['add_pepper']))
                                <br><small>+ Pepper</small>
                            @endif
                            @if(!empty($item['customizations']['packaging_type']))
                                <br><small>Packaging: {{ $item['customizations']['packaging_type'] }}</small>
                            @endif
                        </span>
                        <span>JOD {{ number_format((float)$item['line_total'], 2) }}</span>
                    </div>
                @endforeach
                <div class="dolci-summary-row"><span>Subtotal</span><span>JOD {{ number_format((float)$subtotal, 2) }}</span></div>
                <div class="dolci-summary-row"><span>Discount</span><span>- JOD {{ number_format((float)$discount, 2) }}</span></div>
                <div class="dolci-summary-row dolci-summary-total"><span>Total</span><span>JOD {{ number_format((float)$total, 2) }}</span></div>
                <p class="dolci-secure-note">Payment status will stay pending until you confirm via WhatsApp.</p>
            </aside>
        </div>
    </div>
</section>
@endsection

