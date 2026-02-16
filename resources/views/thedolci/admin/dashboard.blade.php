@extends('thedolci.layouts.admin')

@section('title', 'thedolci Admin Dashboard')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <h1>thedolci Admin Dashboard</h1>

        <div class="dolci-admin-metrics">
            <article><h3>Total Orders</h3><p>{{ $ordersCount }}</p></article>
            <article><h3>Total Revenue</h3><p>JOD {{ number_format((float)$revenue, 2) }}</p></article>
            <article><h3>Average Order Value</h3><p>JOD {{ number_format((float)$averageOrderValue, 2) }}</p></article>
            <article><h3>Products</h3><p>{{ $productsCount }}</p></article>
        </div>

        <div class="dolci-admin-table-wrap">
            <h2>Latest Orders</h2>
            <table class="dolci-admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestOrders as $order)
                        <tr>
                            <td><a href="{{ route('thedolci.admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td>JOD {{ number_format((float)$order->total, 2) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

