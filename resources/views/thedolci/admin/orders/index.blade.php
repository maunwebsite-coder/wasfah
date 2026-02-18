@extends('thedolci.layouts.admin')

@section('title', 'Orders | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <h1>Order Management</h1>

        @if(!$dbReady)
            <div class="dolci-alert dolci-alert-error">Database table is not ready. Run migrations first.</div>
        @endif

        <div class="dolci-admin-table-wrap">
            <table class="dolci-admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a href="{{ route('thedolci.admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>JOD {{ number_format((float)$order->total, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td>{{ ucfirst($order->payment_status) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if($dbReady)
                <div class="dolci-pagination-wrap">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

