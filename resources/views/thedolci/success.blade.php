@extends('thedolci.layouts.store')

@section('title', 'Order Confirmed | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-success-box">
            <p class="dolci-kicker">Order Confirmed</p>
            <h1>Thank you for ordering with thedolci</h1>
            <p>Your order is saved. Final step: send it to WhatsApp to complete Click payment confirmation.</p>

            <div class="dolci-success-grid">
                <div>
                    <h3>Order Number</h3>
                    <p>{{ is_array($order) ? $order['order_number'] : $order->order_number }}</p>
                </div>
                <div>
                    <h3>Total</h3>
                    <p>JOD {{ number_format((float)(is_array($order) ? $order['total'] : $order->total), 2) }}</p>
                </div>
                <div>
                    <h3>Fulfillment</h3>
                    <p>{{ ucfirst(str_replace('_', ' ', is_array($order) ? $order['fulfillment_type'] : $order->fulfillment_type)) }}</p>
                </div>
                <div>
                    <h3>Payment Status</h3>
                    <p>{{ ucfirst(is_array($order) ? ($order['payment_status'] ?? 'pending') : $order->payment_status) }}</p>
                </div>
            </div>

            <div class="dolci-hero-actions">
                @if(!empty($whatsappUrl))
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="dolci-btn dolci-btn-primary">Ø§Ù„Ø§Ø³ØªÙ…Ø±Ø§Ø± ÙÙŠ Ø§Ù„Ø·Ù„Ø¨ Ø¹Ø¨Ø± Ø§Ù„ÙˆØ§ØªØ³Ø§Ø¨</a>
                @endif
                <a href="{{ route('thedolci.track-order') }}" class="dolci-btn dolci-btn-secondary">Track Order</a>
                <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-secondary">Order Again</a>
            </div>

            @if(empty($whatsappUrl))
                <p class="dolci-secure-note">WhatsApp link is not configured. Set <code>WHATSAPP_BOOKING_NUMBER</code> in environment.</p>
            @elseif(!empty($whatsappNumber))
                <p class="dolci-secure-note">If WhatsApp did not open automatically, message this number manually: +{{ $whatsappNumber }}</p>
            @endif
        </div>
    </div>
</section>
@endsection

