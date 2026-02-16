@extends('thedolci.layouts.store')

@section('title', 'Track Order | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Track Your Order</h1>
            <p>Enter order number and email to view current status.</p>
        </div>

        <form method="POST" action="{{ route('thedolci.track-order') }}" class="dolci-form-card dolci-track-form">
            @csrf
            <div class="dolci-form-grid-2">
                <div>
                    <label>Order Number</label>
                    <input type="text" name="order_number" value="{{ old('order_number') }}" placeholder="TDL-20260215-XXXXX" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" required>
                </div>
            </div>
            <button type="submit" class="dolci-btn dolci-btn-primary">Track</button>
        </form>

        @if($lookupFailed)
            <div class="dolci-alert dolci-alert-error">No order found with the provided details.</div>
        @endif

        @if($order)
            @php
                $orderNumber = is_array($order) ? data_get($order, 'order_number') : $order->order_number;
                $status = is_array($order) ? data_get($order, 'status') : $order->status;
                $paymentStatus = is_array($order) ? data_get($order, 'payment_status') : $order->payment_status;
                $deliveryDateValue = is_array($order) ? data_get($order, 'delivery_date') : $order->delivery_date;
                $deliveryDate = $deliveryDateValue instanceof \Illuminate\Support\Carbon
                    ? $deliveryDateValue->format('Y-m-d')
                    : (is_string($deliveryDateValue) ? $deliveryDateValue : '');
                $deliveryTime = is_array($order) ? data_get($order, 'delivery_time') : $order->delivery_time;
                $total = is_array($order) ? data_get($order, 'total') : $order->total;
            @endphp
            <div class="dolci-order-status-card">
                <h2>Order {{ $orderNumber }}</h2>
                <p>Status: <strong>{{ ucfirst(str_replace('_', ' ', (string) $status)) }}</strong></p>
                <p>Payment: <strong>{{ ucfirst((string) $paymentStatus) }}</strong></p>
                <p>Delivery Date: <strong>{{ $deliveryDate }} {{ $deliveryTime }}</strong></p>
                <p>Total: <strong>JOD {{ number_format((float) $total, 2) }}</strong></p>
            </div>
        @endif
    </div>
</section>
@endsection

