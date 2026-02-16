@extends('thedolci.layouts.admin')

@section('title', 'Order ' . $order->order_number . ' | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Order {{ $order->order_number }}</h1>
            <a href="{{ route('thedolci.admin.orders.index') }}">Back to Orders</a>
        </div>

        <div class="dolci-order-meta-grid">
            <article>
                <h3>Customer</h3>
                <p>{{ $order->customer_name }}</p>
                <p>{{ $order->customer_email }}</p>
                <p>{{ $order->customer_phone }}</p>
            </article>
            <article>
                <h3>Fulfillment</h3>
                <p>{{ ucfirst($order->fulfillment_type) }}</p>
                <p>{{ $order->delivery_address }}</p>
                <p>{{ optional($order->delivery_date)->format('Y-m-d') }} {{ $order->delivery_time }}</p>
            </article>
            <article>
                <h3>Payment</h3>
                <p>Method: {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p>Status: {{ ucfirst($order->payment_status) }}</p>
                <p>Total: JOD {{ number_format((float)$order->total, 2) }}</p>
            </article>
            <article>
                <h3>Order Summary</h3>
                <p>Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                <p>Subtotal: JOD {{ number_format((float)$order->subtotal, 2) }}</p>
                <p>Discount: JOD {{ number_format((float)$order->discount_amount, 2) }}</p>
                <p>Coupon: {{ $order->coupon_code ?: '-' }}</p>
                <p>Loyalty Points: {{ (int) $order->loyalty_points_earned }}</p>
                <p>Created: {{ optional($order->created_at)->format('Y-m-d H:i') }}</p>
            </article>
        </div>

        <div class="dolci-admin-table-wrap">
            <h2>Items</h2>
            <table class="dolci-admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items ?? [] as $item)
                        <tr>
                            <td>
                                <div>{{ $item['name'] ?? '-' }}</div>
                                @if(!empty(data_get($item, 'customizations.add_pepper')))
                                    <small class="dolci-limited">Pepper add-on (+JOD {{ number_format((float) data_get($item, 'customizations.pepper_price', 0), 2) }})</small>
                                @endif
                                @if(!empty(data_get($item, 'customizations.packaging_type')))
                                    <small class="dolci-limited">
                                        Packaging: {{ data_get($item, 'customizations.packaging_type') }}
                                        @if((float) data_get($item, 'customizations.packaging_price', 0) > 0)
                                            (+JOD {{ number_format((float) data_get($item, 'customizations.packaging_price', 0), 2) }})
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>{{ $item['size'] ?? '-' }}</td>
                            <td>{{ $item['quantity'] ?? '-' }}</td>
                            <td>JOD {{ number_format((float)($item['unit_price'] ?? 0), 2) }}</td>
                            <td>JOD {{ number_format((float)($item['line_total'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="dolci-form-card">
            <h3>Admin / Customer Notes</h3>
            <p>{{ $order->notes ?: '-' }}</p>
        </div>

        <form method="POST" action="{{ route('thedolci.admin.orders.update-status', $order) }}" class="dolci-form-card dolci-form-inline">
            @csrf
            @method('PATCH')
            <div>
                <label>Order Status</label>
                <select name="status">
                    @foreach(['new','confirmed','preparing','ready','out_for_delivery','completed','cancelled'] as $status)
                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Payment Status</label>
                <select name="payment_status">
                    @foreach(['pending','paid','failed'] as $payment)
                        <option value="{{ $payment }}" {{ $order->payment_status === $payment ? 'selected' : '' }}>{{ ucfirst($payment) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="dolci-btn dolci-btn-primary">Update Status</button>
        </form>
    </div>
</section>
@endsection

