@extends('thedolci.layouts.admin')

@section('title', 'Thedolci Admin Area')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <div>
                <p class="dolci-kicker">Admin Workspace</p>
                <h1>Thedolci Control Center</h1>
                <p>Only thedolci modules are shown here: products, orders, reviews, and storefront content.</p>
            </div>
            <div class="dolci-admin-head-actions">
                <a href="{{ route('thedolci.admin.dashboard') }}" class="dolci-btn dolci-btn-secondary">Dashboard</a>
                <a href="{{ route('thedolci.shop') }}" class="dolci-btn dolci-btn-primary" target="_blank" rel="noopener">Open Store</a>
            </div>
        </div>

        @if(count($schemaWarnings))
            <div class="dolci-alert dolci-alert-error">
                <ul>
                    @foreach($schemaWarnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dolci-section-head">
            <h2>Needs Attention</h2>
        </div>

        <div class="dolci-admin-metrics">
            @foreach($attentionItems as $item)
                @php
                    $format = $item['format'] ?? 'number';
                    $rawValue = $item['value'] ?? 0;
                    $isEmpty = (float) $rawValue === 0.0;
                    $displayValue = $format === 'currency'
                        ? number_format((float) $rawValue, 2) . ' JOD'
                        : number_format((int) $rawValue);
                @endphp
                <article>
                    <h3>{{ $item['label'] }}</h3>
                    <p>{{ $displayValue }}</p>
                    <p>{{ $isEmpty ? $item['empty_state'] : $item['cta'] }}</p>
                    <a href="{{ route($item['route']) }}" class="dolci-btn dolci-btn-compact">Open</a>
                </article>
            @endforeach
        </div>

        <div class="dolci-section-head">
            <h2>Quick Glance</h2>
        </div>

        <div class="dolci-admin-metrics">
            @foreach($metrics as $metric)
                @php
                    $format = $metric['format'] ?? 'number';
                    $value = $metric['value'] ?? 0;
                    $displayValue = $format === 'currency'
                        ? number_format((float) $value, 2) . ' JOD'
                        : number_format((int) $value);
                @endphp
                <article>
                    <h3>{{ $metric['label'] }}</h3>
                    <p>{{ $displayValue }}</p>
                    @if(!empty($metric['hint']))
                        <p>{{ $metric['hint'] }}</p>
                    @endif
                    <a href="{{ route($metric['route']) }}" class="dolci-btn dolci-btn-compact">View</a>
                </article>
            @endforeach
        </div>

        <div class="dolci-split">
            <div class="dolci-admin-table-wrap">
                <div class="dolci-section-head">
                    <h2>Latest Orders</h2>
                    <a href="{{ route('thedolci.admin.orders.index') }}" class="dolci-btn dolci-btn-secondary">All Orders</a>
                </div>
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
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }} / {{ ucfirst($order->payment_status) }}</td>
                            <td>{{ number_format((float) $order->total, 2) }} JOD</td>
                            <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No orders found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="dolci-admin-table-wrap">
                <div class="dolci-section-head">
                    <h2>Latest Reviews</h2>
                    <a href="{{ route('thedolci.admin.reviews.index') }}" class="dolci-btn dolci-btn-secondary">All Reviews</a>
                </div>
                <table class="dolci-admin-table">
                    <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Flags</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($latestReviews as $review)
                        <tr>
                            <td>
                                <a href="{{ route('thedolci.admin.reviews.edit', $review) }}">{{ $review->customer_name }}</a>
                                <div>{{ \Illuminate\Support\Str::limit($review->review_text, 80) }}</div>
                            </td>
                            <td>{{ $review->rating }}/5</td>
                            <td>
                                {{ $review->is_active ? 'Visible' : 'Hidden' }}
                                {{ $review->is_featured ? ' - Featured' : '' }}
                            </td>
                            <td>{{ $review->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No reviews found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dolci-section-head">
            <h2>Store Management</h2>
        </div>

        <div class="dolci-three-cols">
            @foreach($managementSections as $section)
                <article class="dolci-form-card">
                    <h3>{{ $section['title'] }}</h3>
                    <p class="dolci-limited">{{ $section['description'] }}</p>
                    <div class="dolci-check-stack">
                        @foreach($section['items'] as $item)
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="dolci-btn dolci-btn-secondary dolci-btn-block">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        <div class="dolci-section-head">
            <h2>Quick Actions</h2>
        </div>

        <div class="dolci-admin-head-actions">
            @foreach($quickActions as $action)
                <a href="{{ route($action['route'], $action['params'] ?? []) }}" class="dolci-btn dolci-btn-compact">{{ $action['label'] }}</a>
            @endforeach
        </div>
    </div>
</section>
@endsection

