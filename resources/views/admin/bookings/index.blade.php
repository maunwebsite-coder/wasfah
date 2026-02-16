@extends('layouts.app')

@section('title', 'Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª')

@php
    $totalBookings = max($stats['total'], 1);

    $statusCards = [
        [
            'label' => 'Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª',
            'value' => $stats['total'],
            'formatted' => number_format($stats['total']),
            'icon' => 'fa-calendar-check',
            'gradient' => 'from-sky-500 via-indigo-500 to-purple-500',
            'description' => 'Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„Ù…Ø³Ø¬Ù„Ø© ÙÙŠ Ø§Ù„Ù†Ø¸Ø§Ù…',
            'percentage' => null,
        ],
        [
            'label' => 'Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø©',
            'value' => $stats['pending'],
            'formatted' => number_format($stats['pending']),
            'icon' => 'fa-hourglass-half',
            'gradient' => 'from-amber-500 to-orange-500',
            'description' => 'Ø­Ø¬ÙˆØ²Ø§Øª ØªÙ†ØªØ¸Ø± Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡',
            'percentage' => round(($stats['pending'] / $totalBookings) * 100, 1),
        ],
        [
            'label' => 'Ù…Ø¤ÙƒØ¯Ø©',
            'value' => $stats['confirmed'],
            'formatted' => number_format($stats['confirmed']),
            'icon' => 'fa-check-circle',
            'gradient' => 'from-emerald-500 to-teal-500',
            'description' => 'ØªÙ… ØªØ£ÙƒÙŠØ¯Ù‡Ø§ Ù„Ù„Ù…Ø´Ø§Ø±ÙƒÙŠÙ†',
            'percentage' => round(($stats['confirmed'] / $totalBookings) * 100, 1),
        ],
        [
            'label' => 'Ù…Ù„ØºÙŠØ©',
            'value' => $stats['cancelled'],
            'formatted' => number_format($stats['cancelled']),
            'icon' => 'fa-times-circle',
            'gradient' => 'from-rose-500 to-red-500',
            'description' => 'ØªØ­ØªØ§Ø¬ ØªØ­Ù„ÙŠÙ„ Ø£Ø³Ø¨Ø§Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡',
            'percentage' => round(($stats['cancelled'] / $totalBookings) * 100, 1),
        ],
    ];

    $quickStatusFilters = [
        [
            'label' => 'Ø§Ù„ÙƒÙ„',
            'value' => null,
            'count' => number_format($stats['total']),
            'icon' => 'fa-layer-group',
            'hint' => 'Ø¹Ø±Ø¶ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª',
        ],
        [
            'label' => 'Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø©',
            'value' => 'pending',
            'count' => number_format($stats['pending']),
            'icon' => 'fa-hourglass-half',
            'hint' => 'Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„ØªÙŠ Ù„Ù… ÙŠØªÙ… Ø§ØªØ®Ø§Ø° Ø¥Ø¬Ø±Ø§Ø¡ Ø¨Ø´Ø£Ù†Ù‡Ø§',
        ],
        [
            'label' => 'Ù…Ø¤ÙƒØ¯Ø©',
            'value' => 'confirmed',
            'count' => number_format($stats['confirmed']),
            'icon' => 'fa-check-circle',
            'hint' => 'Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„Ø¬Ø§Ù‡Ø²Ø© Ù„Ù„ÙˆØ±Ø´Ø©',
        ],
        [
            'label' => 'Ù…Ù„ØºÙŠØ©',
            'value' => 'cancelled',
            'count' => number_format($stats['cancelled']),
            'icon' => 'fa-ban',
            'hint' => 'Ø¥Ù„ØºØ§Ø¡Ø§Øª ØªØ­ØªØ§Ø¬ Ù…ØªØ§Ø¨Ø¹Ø©',
        ],
    ];

    $advancedFiltersActive = request()->hasAny([
        'workshop_type',
        'price_range',
        'payment_method',
        'payment_currency',
        'booking_count',
        'workshop_date_from',
        'workshop_date_to',
        'financial_status',
    ]);

    $paymentMeta = [
        'pending' => [
            'label' => 'Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„Ø¯ÙØ¹',
            'class' => 'bg-amber-100 text-amber-700',
        ],
        'paid' => [
            'label' => 'Ù…Ø¯ÙÙˆØ¹Ø©',
            'class' => 'bg-emerald-100 text-emerald-700',
        ],
        'refunded' => [
            'label' => 'Ù…Ø³ØªØ±Ø¯Ø©',
            'class' => 'bg-purple-100 text-purple-700',
        ],
    ];

    $financialStatusMeta = [
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING => [
            'label' => 'Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„ØªÙˆØ²ÙŠØ¹',
            'class' => 'bg-slate-100 text-slate-700',
        ],
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED => [
            'label' => 'ØªÙ… Ø§Ù„ØªÙˆØ²ÙŠØ¹',
            'class' => 'bg-emerald-100 text-emerald-700',
        ],
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID => [
            'label' => 'Ù…Ø¹Ù„Ù‚ Ø£Ùˆ Ù…Ù„ØºÙŠ',
            'class' => 'bg-rose-100 text-rose-700',
        ],
    ];

    $financialStatusFilters = [
        [
            'label' => 'Ø§Ù„ÙƒÙ„',
            'value' => null,
            'hint' => 'Ø¹Ø±Ø¶ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø§Ù„Ø§Øª Ø§Ù„Ù…Ø§Ù„ÙŠØ©',
        ],
        [
            'label' => 'Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„ØªÙˆØ²ÙŠØ¹',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING,
            'hint' => 'Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„ØªÙŠ Ù„Ù… ÙŠØªÙ… ØªÙˆØ²ÙŠØ¹ Ù…Ø¨Ø§Ù„ØºÙ‡Ø§ Ø¨Ø¹Ø¯',
        ],
        [
            'label' => 'ØªÙ… Ø§Ù„ØªÙˆØ²ÙŠØ¹',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED,
            'hint' => 'Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„ØªÙŠ ØªÙ… ØªÙ‚Ø³ÙŠÙ… Ø¹ÙˆØ§Ø¦Ø¯Ù‡Ø§ ØªÙ„Ù‚Ø§Ø¦ÙŠØ§Ù‹',
        ],
        [
            'label' => 'Ù…Ø¹Ù„Ù‚ Ø£Ùˆ Ù…Ù„ØºÙŠ',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID,
            'hint' => 'Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„ØªÙŠ ØªÙ… Ø¥ÙŠÙ‚Ø§Ù ØªÙˆØ²ÙŠØ¹Ù‡Ø§',
        ],
    ];

    $defaultCurrency = config('finance.default_currency', 'JOD');
    $currencyOptions = $currencyOptions ?? \App\Support\Currency::all();
    $shareTotals = $stats['share_totals'] ?? ['chef' => 0, 'partner' => 0, 'admin' => 0];
    $paidCurrencies = $stats['paid_currencies'] ?? [];

    $statusCards[] = [
        'label' => 'Ù…Ø¯ÙÙˆØ¹Ø§Øª Ù…Ø¤ÙƒØ¯Ø©',
        'value' => $stats['paid_amount'] ?? 0,
        'formatted' => number_format($stats['paid_amount'] ?? 0, 2) . ' ' . $defaultCurrency,
        'icon' => 'fa-wallet',
        'gradient' => 'from-emerald-500 to-lime-500',
        'description' => 'Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¨Ø§Ù„Øº Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø© Ø­ØªÙ‰ Ø§Ù„Ø¢Ù†',
        'percentage' => null,
    ];

    $statusCards[] = [
        'label' => 'Ø­Ø¬ÙˆØ²Ø§Øª Ù…ÙˆØ²Ø¹Ø© Ù…Ø§Ù„ÙŠØ§Ù‹',
        'value' => $stats['financial']['distributed'] ?? 0,
        'formatted' => number_format($stats['financial']['distributed'] ?? 0),
        'icon' => 'fa-coins',
        'gradient' => 'from-cyan-500 to-blue-500',
        'description' => 'Ø¹Ø¯Ø¯ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„ØªÙŠ ØªÙ… ØªÙ‚Ø³ÙŠÙ… Ø­ØµØµÙ‡Ø§',
        'percentage' => null,
    ];
@endphp

@push('styles')
<style>
#confirmationModal,
#alertModal,
#adminNoteModal {
    transition: opacity 0.2s ease-in-out;
}

#confirmationModal.show,
#alertModal.show,
#adminNoteModal.show {
    opacity: 1;
}

.modal-backdrop {
    backdrop-filter: blur(2px);
}

.modal-content {
    direction: rtl;
    text-align: right;
    animation: modalSlideIn 0.25s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-button {
    transition: all 0.2s ease;
}

.modal-button:hover {
    transform: translateY(-1px);
}

.modal-button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.35);
}

.quick-filter-scroll {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(59, 130, 246, 0.25) transparent;
}

.quick-filter-scroll::-webkit-scrollbar {
    height: 6px;
}

.quick-filter-scroll::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.25);
    border-radius: 9999px;
}

.quick-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    border: 1px solid rgba(59, 130, 246, 0.18);
    background: rgba(59, 130, 246, 0.08);
    color: #6b2e30;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
    white-space: nowrap;
}

.quick-filter-chip:hover {
    border-color: rgba(59, 130, 246, 0.35);
    background: rgba(59, 130, 246, 0.12);
}

.quick-filter-chip .chip-icon,
.quick-filter-chip .chip-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 9999px;
    font-size: 0.7rem;
}

.quick-filter-chip .chip-icon {
    background: rgba(255, 255, 255, 0.25);
}

.quick-filter-chip .chip-count {
    background: rgba(255, 255, 255, 0.9);
    color: #7f3a3d;
    font-weight: 700;
}

.quick-filter-chip.is-active {
    color: #fff;
    border-color: transparent;
    background: linear-gradient(135deg, #9f5e63, #7c3aed);
    box-shadow: 0 16px 32px -24px rgba(79, 70, 229, 0.65);
}

.quick-filter-chip.is-active .chip-count {
    background: rgba(255, 255, 255, 0.22);
    color: inherit;
}

.status-summary-card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.25rem;
    border-radius: 1rem;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.06);
    box-shadow: 0 10px 30px -25px rgba(15, 23, 42, 0.45);
}

.status-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-summary-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    background: rgba(59, 130, 246, 0.12);
    color: #8f4a50;
    font-size: 1.25rem;
}

.status-summary-trend {
    font-size: 0.75rem;
    font-weight: 600;
    color: #22c55e;
}

.note-indicator {
    border-radius: 0.75rem;
    background: rgba(79, 70, 229, 0.12);
    color: #4338ca;
    padding: 0.25rem 0.6rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
}

.activity-item {
    transition: background 0.2s ease, box-shadow 0.2s ease;
}

.activity-item:hover {
    background: rgba(59, 130, 246, 0.06);
    box-shadow: 0 12px 20px -18px rgba(37, 99, 235, 0.5);
}

@media (max-width: 640px) {
    .quick-filter-chip {
        padding-inline: 0.75rem;
        font-size: 0.78rem;
    }
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 space-y-4">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Ù…ØªØ§Ø¨Ø¹Ø© Ø³Ø±ÙŠØ¹Ø© Ù„Ù„Ø­Ø¬ÙˆØ²Ø§ØªØŒ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§ØªØŒ ÙˆØ§Ù„Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª Ø§Ù„ÙŠÙˆÙ…ÙŠØ©.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">
                    <a href="{{ route('admin.bookings.manual') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
                        <i class="fas fa-plus"></i>
                        Ø¥Ø¶Ø§ÙØ© Ø­Ø¬Ø² ÙŠØ¯ÙˆÙŠ
                    </a>
                    <a href="{{ route('admin.bookings.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-blue-200 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                        <i class="fas fa-file-export"></i>
                        ØªØµØ¯ÙŠØ± Ø§Ù„Ù†ØªØ§Ø¦Ø¬
                    </a>
                    <button type="button" onclick="refreshBookings()" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300">
                        <i class="fas fa-sync-alt"></i>
                        ØªØ­Ø¯ÙŠØ«
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-right"></i>
                        Ø§Ù„Ø¹ÙˆØ¯Ø© Ù„Ù„ÙˆØ­Ø© Ø§Ù„ØªØ­ÙƒÙ…
                    </a>
                </div>
            </div>
            <div class="flex items-center text-sm text-gray-500 gap-2">
                <i class="fas fa-clock text-gray-400"></i>
                <span>Ø¢Ø®Ø± ØªØ­Ø¯ÙŠØ«: {{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        @isset($pendingApprovalBookings)
            <div class="mb-8">
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100">
                    <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <i class="fas fa-inbox"></i>
                                </span>
                                Ø­Ø¬ÙˆØ²Ø§Øª Ø¬Ø¯ÙŠØ¯Ø© Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡
                            </h2>
                            <p class="text-sm text-gray-500">
                                Ø±Ø§Ø¬Ø¹ Ø£Ø­Ø¯Ø« Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª ÙˆÙ‚Ø±Ø± ØªØ£ÙƒÙŠØ¯Ù‡Ø§ Ø£Ùˆ Ø±ÙØ¶Ù‡Ø§ Ù…Ø¨Ø§Ø´Ø±Ø© Ø¨Ø¯ÙˆÙ† Ø§Ù„Ø­Ø§Ø¬Ø© Ù„Ù„ØªÙ…Ø±ÙŠØ± Ø¥Ù„Ù‰ Ø§Ù„Ø£Ø³ÙÙ„.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('admin.bookings.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
                                class="inline-flex items-center px-4 py-2 rounded-md border border-blue-200 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors duration-200"
                            >
                                <i class="fas fa-filter ml-2"></i>
                                Ø¹Ø±Ø¶ ÙƒÙ„ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„Ù…Ø¹Ù„Ù‚Ø©
                            </a>
                        </div>
                    </div>
                    @if($pendingApprovalBookings->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="dashboard-table min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-user ml-2"></i>
                                            Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-graduation-cap ml-2"></i>
                                            Ø§Ù„ÙˆØ±Ø´Ø©
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-credit-card ml-2"></i>
                                            Ø§Ù„Ø¯ÙØ¹
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-calendar ml-2"></i>
                                            ØªØ§Ø±ÙŠØ® Ø§Ù„Ø­Ø¬Ø²
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-cogs ml-2"></i>
                                            Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª Ø§Ù„Ø³Ø±ÙŠØ¹Ø©
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pendingApprovalBookings as $pendingBooking)
                                        @php
                                            $pendingUser = $pendingBooking->user;
                                            $pendingWorkshop = $pendingBooking->workshop;
                                            $pendingPaymentMeta = $paymentMeta[$pendingBooking->payment_status] ?? null;
                                        @endphp
                                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-900">
                                                        {{ $pendingUser?->name ?? 'Ù…Ø³ØªØ®Ø¯Ù… Ø¨Ø¯ÙˆÙ† Ø§Ø³Ù…' }}
                                                    </span>
                                                    @if($pendingUser?->email)
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-envelope ml-1"></i>
                                                            {{ $pendingUser->email }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $pendingWorkshop?->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <i class="fas fa-clock"></i>
                                                    {{ optional($pendingWorkshop?->start_date)->format('Y-m-d H:i') ?? 'ØºÙŠØ± Ù…Ø¬Ø¯ÙˆÙ„' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ number_format($pendingBooking->payment_amount, 2) }} {{ $pendingWorkshop?->currency }}
                                                </div>
                                                <div class="mt-1 flex items-center gap-2">
                                                    @if($pendingPaymentMeta)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $pendingPaymentMeta['class'] }}">
                                                            {{ $pendingPaymentMeta['label'] }}
                                                        </span>
                                                    @endif
                                                    @if($pendingBooking->payment_method)
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-receipt ml-1"></i>
                                                            {{ $pendingBooking->payment_method }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <div class="font-medium text-gray-900">
                                                    {{ $pendingBooking->created_at->format('Y-m-d') }}
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <i class="fas fa-clock"></i>
                                                    {{ $pendingBooking->created_at->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <a
                                                        href="{{ route('admin.bookings.show', $pendingBooking) }}"
                                                        class="inline-flex items-center px-3 py-1 rounded-md border border-transparent text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200"
                                                    >
                                                        <i class="fas fa-eye ml-1"></i>
                                                        Ø¹Ø±Ø¶
                                                    </a>
                                                    <button
                                                        type="button"
                                                        onclick="confirmBooking({{ $pendingBooking->id }})"
                                                        class="inline-flex items-center px-3 py-1 rounded-md border border-transparent text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200"
                                                    >
                                                        <i class="fas fa-check ml-1"></i>
                                                        ØªØ£ÙƒÙŠØ¯
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onclick="cancelBooking({{ $pendingBooking->id }})"
                                                        class="inline-flex items-center px-3 py-1 rounded-md border border-transparent text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200"
                                                    >
                                                        <i class="fas fa-times ml-1"></i>
                                                        Ø¥Ù„ØºØ§Ø¡
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-8 text-center text-sm text-gray-500">
                            Ù„Ø§ ØªÙˆØ¬Ø¯ Ø­Ø¬ÙˆØ²Ø§Øª Ø¬Ø¯ÙŠØ¯Ø© Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„Ù…ÙˆØ§ÙÙ‚Ø© Ø­Ø§Ù„ÙŠØ§Ù‹.
                        </div>
                    @endif
                </div>
            </div>
        @endisset

        <!-- Ù†Ø¸Ø±Ø© Ø¹Ø§Ù…Ø© Ø³Ø±ÙŠØ¹Ø© -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            @foreach($statusCards as $card)
                <div class="status-summary-card">
                    <div class="status-summary-header">
                        <div>
                            <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $card['formatted'] }}</p>
                        </div>
                        <span class="status-summary-icon">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $card['description'] }}
                        @if(!is_null($card['percentage']))
                            <span class="status-summary-trend ml-2">{{ number_format($card['percentage'], 1) }}%</span>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500">Ø­ØµØ© Ø§Ù„Ù…Ù†ØµØ©</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['admin'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">ØªØ´Ù…Ù„ Ø§Ù„Ø±Ø³ÙˆÙ… Ø§Ù„ØªØ´ØºÙŠÙ„ÙŠØ© ÙˆØ§Ù„ØªÙ‚Ù†ÙŠØ©</p>
                </div>
                <span class="h-12 w-12 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-xl"></i>
                </span>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500">ØµØ§ÙÙŠ Ø§Ù„Ø´ÙŠÙØ§Øª</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['chef'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">ÙŠØªÙ… ØªØ­ÙˆÙŠÙ„Ù‡ Ø¨Ø¹Ø¯ Ø§Ù†ØªÙ‡Ø§Ø¡ Ø§Ù„ÙˆØ±Ø´Ø§Øª</p>
                </div>
                <span class="h-12 w-12 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center">
                    <i class="fas fa-utensils text-xl"></i>
                </span>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500">Ø¹Ù…ÙˆÙ„Ø§Øª Ø§Ù„Ø´Ø±ÙƒØ§Ø¡</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['partner'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">ÙŠØªÙ… ØªØªØ¨Ø¹Ù‡Ø§ Ø¹Ø¨Ø± Ù„ÙˆØ­Ø© Ø¨Ø±Ù†Ø§Ù…Ø¬ Ø§Ù„Ø¥Ø­Ø§Ù„Ø©</p>
                </div>
                <span class="h-12 w-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                    <i class="fas fa-handshake text-xl"></i>
                </span>
            </div>
        </div>

        @if(!empty($paidCurrencies))
            <div class="bg-white border border-indigo-100 rounded-2xl p-6 mb-8 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-globe text-indigo-500 ml-2"></i>
                            ØªÙˆØ²ÙŠØ¹ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª Ø­Ø³Ø¨ Ø§Ù„Ø¹Ù…Ù„Ø©
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Ø±Ø§Ù‚Ø¨ Ø§Ù„Ø¹Ù…Ù„Ø§Øª Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…Ø© ÙÙŠ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø© Ù„Ø¶Ù…Ø§Ù† Ø¬Ø§Ù‡Ø²ÙŠØ© Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª Ø§Ù„Ù…Ø§Ù„ÙŠØ© ÙˆØ¥ØµØ¯Ø§Ø± Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ø¨Ø§Ù„Ø¹Ù…Ù„Ø© Ø§Ù„ØµØ­ÙŠØ­Ø©.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm font-semibold">
                        <i class="fas fa-money-bill-wave"></i>
                        {{ count($paidCurrencies) }} Ø¹Ù…Ù„Ø© Ù†Ø´Ø·Ø©
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($paidCurrencies as $currencyStat)
                        @php
                            $currencyCode = strtoupper($currencyStat['currency']);
                            $meta = $currencyOptions[$currencyCode] ?? ['label' => $currencyCode, 'symbol' => $currencyCode];
                        @endphp
                        <div class="border border-gray-100 rounded-xl p-4 bg-gradient-to-br from-white to-indigo-50/40">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500">{{ $meta['label'] ?? $currencyCode }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ number_format($currencyStat['total_amount'], 2) }}
                                        <span class="text-base text-gray-500">{{ $meta['symbol'] ?? $currencyCode }}</span>
                                    </p>
                                </div>
                                <span class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-coins"></i>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-receipt text-indigo-500"></i>
                                    {{ number_format($currencyStat['total_bookings']) }} Ø­Ø¬ÙˆØ²Ø§Øª
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-exchange-alt text-gray-400"></i>
                                    â‰ˆ {{ number_format($currencyStat['total_amount_usd'] ?? 0, 2) }} {{ $defaultCurrency }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($followUpBookings) && $followUpBookings->isNotEmpty())
            <div class="bg-white border border-amber-100 rounded-xl p-6 mb-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exclamation-circle text-amber-500 ml-2"></i>
                            Ø­Ø¬ÙˆØ²Ø§Øª ØªØ­ØªØ§Ø¬ Ù…ØªØ§Ø¨Ø¹Ø©
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Ø§Ù„Ø·Ù„Ø¨Ø§Øª Ø§Ù„ØªÙŠ ØªØ¬Ø§ÙˆØ²Øª 48 Ø³Ø§Ø¹Ø© Ø¨Ø¯ÙˆÙ† Ø¥Ø¬Ø±Ø§Ø¡. ØªØ¹Ø§Ù…Ù„ Ù…Ø¹Ù‡Ø§ Ø£ÙˆÙ„Ø§Ù‹ Ù„Ø¶Ù…Ø§Ù† ØªØ¬Ø±Ø¨Ø© Ø£ÙØ¶Ù„ Ù„Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙŠÙ†.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-sm font-semibold">
                        <i class="fas fa-clock"></i>
                        {{ $pendingFollowUpCount ?? $followUpBookings->count() }} Ø­Ø¬ÙˆØ²Ø§Øª Ù…ØªØ£Ø®Ø±Ø©
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($followUpBookings->take(6) as $pending)
                        @php
                            $userName = optional($pending->user)->name ?? 'Ù…Ø³ØªØ®Ø¯Ù…';
                            $workshopTitle = optional($pending->workshop)->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©';
                        @endphp
                        <div class="border border-amber-100 rounded-lg p-4 flex flex-col gap-3 bg-amber-50">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $userName }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-calendar text-[11px] ml-1"></i>
                                    {{ optional($pending->created_at)->format('Y-m-d H:i') ?? 'ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ' }}
                                </p>
                            </div>
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-graduation-cap text-[11px] ml-1 text-amber-500"></i>
                                {{ $workshopTitle }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-phone ml-1"></i>
                                {{ optional($pending->user)->phone ?? 'Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø±Ù‚Ù…' }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.bookings.show', $pending) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-md bg-white text-sm font-semibold text-blue-700 hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-eye ml-1"></i>
                                    Ù…Ø±Ø§Ø¬Ø¹Ø©
                                </a>
                                <button type="button" onclick="confirmBooking({{ $pending->id }})" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-green-500 text-white text-sm font-semibold hover:bg-green-600 transition-colors">
                                    <i class="fas fa-check ml-1"></i>
                                    ØªØ£ÙƒÙŠØ¯
                                </button>
                                <button type="button" onclick="cancelBooking({{ $pending->id }})" class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-red-200 bg-white text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-times ml-1"></i>
                                    Ø¥Ù„ØºØ§Ø¡
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($followUpBookings->count() > 6)
                    <div class="mt-4 text-sm text-gray-500">
                        Ø¹Ø±Ø¶Ù†Ø§ Ø£Ù‡Ù… {{ min(6, $followUpBookings->count()) }} Ø­Ø¬ÙˆØ²Ø§Øª ØªØ­ØªØ§Ø¬ Ù…ØªØ§Ø¨Ø¹Ø©. ÙŠÙ…ÙƒÙ†Ùƒ Ø§Ù„ÙˆØµÙˆÙ„ Ù„Ù„Ø¨Ø§Ù‚ÙŠ Ù…Ù† Ø®Ù„Ø§Ù„ Ø§Ù„ÙÙ„Ø§ØªØ± Ø£Ùˆ Ø§Ù„Ø¬Ø¯ÙˆÙ„ Ø£Ø¯Ù†Ø§Ù‡.
                    </div>
                @endif
            </div>
        @endif
        <!-- Ø§Ù„ÙÙ„Ø§ØªØ± -->
        <div class="bg-white shadow-xl rounded-2xl mb-8 border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter text-blue-500 ml-2"></i>
                        ÙÙ„ØªØ±Ø© Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Ø§Ø®ØªØµØ± Ø§Ù„ÙˆÙ‚Øª Ø¨Ø§Ø³ØªØ¹Ù…Ø§Ù„ Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ø°ÙƒÙŠØ© Ø£Ø¯Ù†Ø§Ù‡</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="toggleAdvancedFilters" class="inline-flex items-center px-4 py-2 rounded-md border border-blue-200 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors duration-200" aria-expanded="{{ $advancedFiltersActive ? 'true' : 'false' }}">
                        <i class="fas fa-sliders-h ml-2"></i>
                        <span id="advancedFiltersToggleLabel">{{ $advancedFiltersActive ? 'Ø¥Ø®ÙØ§Ø¡ Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©' : 'Ø¹Ø±Ø¶ Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©' }}</span>
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-redo ml-2"></i>
                        Ø¥Ø¹Ø§Ø¯Ø© Ø§Ù„ØªØ¹ÙŠÙŠÙ†
                    </a>
                </div>
            </div>
            <form method="GET" id="bookingFiltersForm" class="px-6 py-6 space-y-6">
                <div class="quick-filter-scroll">
                    @foreach($quickStatusFilters as $filter)
                        @php
                            $isActiveStatus = request()->filled('status') ? request('status') === $filter['value'] : is_null($filter['value']);
                        @endphp
                        <button type="button" class="quick-filter-chip {{ $isActiveStatus ? 'is-active' : '' }}" data-status-value="{{ $filter['value'] ?? '' }}">
                            <span class="chip-icon">
                                <i class="fas {{ $filter['icon'] }} text-sm"></i>
                            </span>
                            <div class="flex flex-col text-right leading-tight">
                                <span class="chip-label">{{ $filter['label'] }}</span>
                                <span class="text-[11px] text-blue-900/60">{{ $filter['hint'] }}</span>
                            </div>
                            <span class="chip-count">{{ $filter['count'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ø§Ù„Ø­Ø§Ù„Ø©</label>
                        <select id="statusSelect" name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø§Ù„Ø§Øª</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Ù…Ø¤ÙƒØ¯Ø©</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Ù…Ù„ØºÙŠØ©</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ø­Ø§Ù„Ø© Ø§Ù„Ø¯ÙØ¹</label>
                        <select name="payment_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Ø¬Ù…ÙŠØ¹ Ø­Ø§Ù„Ø§Øª Ø§Ù„Ø¯ÙØ¹</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Ù…Ø¯ÙÙˆØ¹Ø©</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Ù…Ø³ØªØ±Ø¯Ø©</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ø§Ù„ØªÙˆØ²ÙŠØ¹ Ø§Ù„Ù…Ø§Ù„ÙŠ</label>
                        <select name="financial_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø§Ù„Ø§Øª Ø§Ù„Ù…Ø§Ù„ÙŠØ©</option>
                            @foreach($financialStatusFilters as $filter)
                                @continue(is_null($filter['value']))
                                <option value="{{ $filter['value'] }}" {{ request('financial_status') == $filter['value'] ? 'selected' : '' }}>
                                    {{ $filter['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ø§Ù„ÙˆØ±Ø´Ø©</label>
                        <select name="workshop_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„ÙˆØ±Ø´Ø§Øª</option>
                            @foreach($workshops as $workshop)
                                <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                    {{ $workshop->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ù…Ù† ØªØ§Ø±ÙŠØ®</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" max="{{ date('Y-m-d') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ø¥Ù„Ù‰ ØªØ§Ø±ÙŠØ®</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" max="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div id="advancedFilters" class="border-t border-gray-200 pt-4 {{ $advancedFiltersActive ? '' : 'hidden' }}">
                    <h4 class="text-sm font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-cogs ml-2"></i>
                        ÙÙ„Ø§ØªØ± Ù…ØªÙ‚Ø¯Ù…Ø©
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ù†ÙˆØ¹ Ø§Ù„ÙˆØ±Ø´Ø©</label>
                            <select name="workshop_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø£Ù†ÙˆØ§Ø¹</option>
                                <option value="online" {{ request('workshop_type') == 'online' ? 'selected' : '' }}>Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†</option>
                                <option value="offline" {{ request('workshop_type') == 'offline' ? 'selected' : '' }}>Ø£ÙˆÙÙ„Ø§ÙŠÙ†</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ù†Ø·Ø§Ù‚ Ø§Ù„Ø³Ø¹Ø±</label>
                            <select name="price_range" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø£Ø³Ø¹Ø§Ø±</option>
                                <option value="0-50" {{ request('price_range') == '0-50' ? 'selected' : '' }}>0 - 50 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ</option>
                                <option value="50-100" {{ request('price_range') == '50-100' ? 'selected' : '' }}>50 - 100 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ</option>
                                <option value="100-200" {{ request('price_range') == '100-200' ? 'selected' : '' }}>100 - 200 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ</option>
                                <option value="200-500" {{ request('price_range') == '200-500' ? 'selected' : '' }}>200 - 500 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ</option>
                                <option value="500+" {{ request('price_range') == '500+' ? 'selected' : '' }}>Ø£ÙƒØ«Ø± Ù…Ù† 500 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ø·Ø±ÙŠÙ‚Ø© Ø§Ù„Ø¯ÙØ¹</label>
                            <select name="payment_method" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø·Ø±Ù‚</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ø¹Ù…Ù„Ø© Ø§Ù„Ø¯ÙØ¹</label>
                            <select name="payment_currency" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø¹Ù…Ù„Ø§Øª</option>
                                @foreach($currencyOptions as $code => $currency)
                                    <option value="{{ $code }}" {{ strtoupper(request('payment_currency')) == $code ? 'selected' : '' }}>
                                        {{ $currency['label'] ?? $code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ø¹Ø¯Ø¯ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª</label>
                            <select name="booking_count" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙŠÙ†</option>
                                <option value="single" {{ request('booking_count') == 'single' ? 'selected' : '' }}>Ø­Ø¬Ø² ÙˆØ§Ø­Ø¯</option>
                                <option value="multiple" {{ request('booking_count') == 'multiple' ? 'selected' : '' }}>Ø¹Ø¯Ø© Ø­Ø¬ÙˆØ²Ø§Øª</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ØªØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø© (Ù…Ù†)</label>
                            <input type="date" name="workshop_date_from" value="{{ request('workshop_date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ØªØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø© (Ø¥Ù„Ù‰)</label>
                            <input type="date" name="workshop_date_to" value="{{ request('workshop_date_to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6">
                        <div class="w-full xl:max-w-md">
                            <label for="bookingSearchInput" class="block text-sm font-medium text-gray-700 mb-2">
                                Ø§Ù„Ø¨Ø­Ø« Ø§Ù„Ø³Ø±ÙŠØ¹
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    id="bookingSearchInput"
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Ø§Ø³Ù… Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ØŒ Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠØŒ Ø£Ùˆ Ø¹Ù†ÙˆØ§Ù† Ø§Ù„ÙˆØ±Ø´Ø©"
                                    class="w-full pr-4 pl-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                ØªØ¸Ù‡Ø± Ø§Ù„Ù†ØªØ§Ø¦Ø¬ Ø¨Ù…Ø¬Ø±Ø¯ Ø§Ù„ÙƒØªØ§Ø¨Ø©ØŒ Ø£Ùˆ Ø§Ø¶ØºØ· Ø¥Ø¯Ø®Ø§Ù„ Ù„Ù„ØªØ£ÙƒÙŠØ¯.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 xl:gap-6">
                            <div class="flex gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-2">ØªØ±ØªÙŠØ¨ Ø­Ø³Ø¨</label>
                                    <select name="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø­Ø¬Ø²</option>
                                        <option value="payment_amount" {{ request('sort_by') == 'payment_amount' ? 'selected' : '' }}>Ø§Ù„Ù…Ø¨Ù„Øº</option>
                                        <option value="workshop_start_date" {{ request('sort_by') == 'workshop_start_date' ? 'selected' : '' }}>ØªØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø©</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-2">Ø§ØªØ¬Ø§Ù‡ Ø§Ù„ØªØ±ØªÙŠØ¨</label>
                                    <select name="sort_direction" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>ØªÙ†Ø§Ø²Ù„ÙŠ</option>
                                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>ØªØµØ§Ø¹Ø¯ÙŠ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 sm:self-end">
                                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-filter ml-2"></i>
                                    ØªØ·Ø¨ÙŠÙ‚ Ø§Ù„Ø¨Ø­Ø«
                                </button>
                                <button type="button" id="clearFiltersButton" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <i class="fas fa-undo ml-2"></i>
                                    Ø¥Ø¹Ø§Ø¯Ø© Ø§Ù„Ø¶Ø¨Ø·
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Ù…Ø¤Ø´Ø±Ø§Øª Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù†Ø´Ø·Ø© -->
        @if(request()->hasAny(['status', 'payment_status', 'financial_status', 'workshop_id', 'date_from', 'date_to', 'search', 'workshop_type', 'price_range', 'payment_method', 'payment_currency', 'booking_count', 'workshop_date_from', 'workshop_date_to', 'sort_by', 'sort_direction']))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-filter text-blue-600 ml-2"></i>
                    <span class="text-sm font-medium text-blue-900">Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù†Ø´Ø·Ø©:</span>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times ml-1"></i>
                    Ù…Ø³Ø­ Ø¬Ù…ÙŠØ¹ Ø§Ù„ÙÙ„Ø§ØªØ±
                </a>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                @if(request('status'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Ø§Ù„Ø­Ø§Ù„Ø©: {{ request('status') == 'pending' ? 'ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±' : (request('status') == 'confirmed' ? 'Ù…Ø¤ÙƒØ¯Ø©' : 'Ù…Ù„ØºÙŠØ©') }}
                    </span>
                @endif
                @if(request('payment_status'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Ø§Ù„Ø¯ÙØ¹: {{ request('payment_status') == 'pending' ? 'ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±' : (request('payment_status') == 'paid' ? 'Ù…Ø¯ÙÙˆØ¹Ø©' : 'Ù…Ø³ØªØ±Ø¯Ø©') }}
                    </span>
                @endif
                @if(request('payment_currency'))
                    @php
                        $selectedCurrency = strtoupper(request('payment_currency'));
                        $currencyLabel = $currencyOptions[$selectedCurrency]['label'] ?? $selectedCurrency;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Ø§Ù„Ø¹Ù…Ù„Ø©: {{ $currencyLabel }}
                    </span>
                @endif
                @if(request('financial_status'))
                    @php
                        $financialFilterLabel = match (request('financial_status')) {
                            \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED => 'ØªÙ… Ø§Ù„ØªÙˆØ²ÙŠØ¹',
                            \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID => 'Ù…Ø¹Ù„Ù‚ Ø£Ùˆ Ù…Ù„ØºÙŠ',
                            default => 'Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„ØªÙˆØ²ÙŠØ¹',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Ø§Ù„ØªÙˆØ²ÙŠØ¹: {{ $financialFilterLabel }}
                    </span>
                @endif
                @if(request('workshop_id'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        Ø§Ù„ÙˆØ±Ø´Ø©: {{ $workshops->where('id', request('workshop_id'))->first()->title ?? 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯' }}
                    </span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Ø§Ù„ØªØ§Ø±ÙŠØ®: {{ request('date_from') ?: 'Ø¨Ø¯Ø§ÙŠØ©' }} - {{ request('date_to') ?: 'Ù†Ù‡Ø§ÙŠØ©' }}
                    </span>
                @endif
                @if(request('search'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Ø§Ù„Ø¨Ø­Ø«: "{{ request('search') }}"
                    </span>
                @endif
                @if(request('workshop_type'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Ø§Ù„Ù†ÙˆØ¹: {{ request('workshop_type') == 'online' ? 'Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†' : 'Ø£ÙˆÙÙ„Ø§ÙŠÙ†' }}
                    </span>
                @endif
                @if(request('price_range'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                        Ø§Ù„Ø³Ø¹Ø±: {{ request('price_range') == '0-50' ? '0-50 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ' : (request('price_range') == '50-100' ? '50-100 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ' : (request('price_range') == '100-200' ? '100-200 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ' : (request('price_range') == '200-500' ? '200-500 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ' : 'Ø£ÙƒØ«Ø± Ù…Ù† 500 Ø¯ÙˆÙ„Ø§Ø± Ø£Ù…Ø±ÙŠÙƒÙŠ'))) }}
                    </span>
                @endif
                @if(request('payment_method'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                        Ø§Ù„Ø¯ÙØ¹: {{ request('payment_method') }}
                    </span>
                @endif
                @if(request('booking_count'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª: {{ request('booking_count') == 'single' ? 'ÙˆØ§Ø­Ø¯ ÙÙ‚Ø·' : 'Ù…ØªØ¹Ø¯Ø¯Ø©' }}
                    </span>
                @endif
                @if(request('workshop_date_from') || request('workshop_date_to'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                        ØªØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø©: {{ request('workshop_date_from') ?: 'Ø¨Ø¯Ø§ÙŠØ©' }} - {{ request('workshop_date_to') ?: 'Ù†Ù‡Ø§ÙŠØ©' }}
                    </span>
                @endif
                @if(request('sort_by') && request('sort_by') != 'created_at')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                        Ø§Ù„ØªØ±ØªÙŠØ¨: {{ request('sort_by') == 'payment_amount' ? 'Ø§Ù„Ù…Ø¨Ù„Øº' : 'ØªØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø©' }} 
                        ({{ request('sort_direction') == 'desc' ? 'ØªÙ†Ø§Ø²Ù„ÙŠ' : 'ØªØµØ§Ø¹Ø¯ÙŠ' }})
                    </span>
                @endif
            </div>
        </div>
        @endif

        <!-- Ø¬Ø¯ÙˆÙ„ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª -->
        <div class="bg-white shadow-lg overflow-hidden rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-green-500 ml-2"></i>
                        Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª
                    </h3>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="text-sm text-gray-500">
                            Ø¹Ø±Ø¶ {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} Ù…Ù† Ø£ØµÙ„ {{ $bookings->total() }} Ø­Ø¬Ø²
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <button onclick="exportBookings()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download ml-2"></i>
                                ØªØµØ¯ÙŠØ±
                            </button>
                            <button onclick="printBookings()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-print ml-2"></i>
                                Ø·Ø¨Ø§Ø¹Ø©
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4">
                
                @if($bookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="dashboard-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-user ml-2"></i>
                                        Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-graduation-cap ml-2"></i>
                                        Ø§Ù„ÙˆØ±Ø´Ø©
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-info-circle ml-2"></i>
                                        Ø§Ù„Ø­Ø§Ù„Ø©
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-credit-card ml-2"></i>
                                        Ø­Ø§Ù„Ø© Ø§Ù„Ø¯ÙØ¹
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-coins ml-2"></i>
                                        Ø§Ù„ØªÙˆØ²ÙŠØ¹ Ø§Ù„Ù…Ø§Ù„ÙŠ
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-money-bill-wave ml-2"></i>
                                        Ø§Ù„Ù…Ø¨Ù„Øº
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-calendar ml-2"></i>
                                        ØªØ§Ø±ÙŠØ® Ø§Ù„Ø­Ø¬Ø²
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cogs ml-2"></i>
                                        Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($bookings as $booking)
                                    @php
                                        $financialBadge = $financialStatusMeta[$booking->financial_status] ?? null;
                                        $chefShare = optional($booking->revenueShares->firstWhere('recipient_type', 'chef'));
                                        $partnerShare = optional($booking->revenueShares->firstWhere('recipient_type', 'partner'));
                                        $adminShare = optional($booking->revenueShares->firstWhere('recipient_type', 'admin'));
                                        $shareCurrency = $chefShare->currency
                                            ?? $partnerShare->currency
                                            ?? $adminShare->currency
                                            ?? ($booking->payment_currency ?? $defaultCurrency);
                                        $invoice = $booking->invoice;
                                        $invoiceStatusMeta = [
                                            'draft' => ['label' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ø³ÙˆØ¯Ø©', 'class' => 'bg-slate-100 text-slate-700'],
                                            'issued' => ['label' => 'ÙØ§ØªÙˆØ±Ø© ØµØ§Ø¯Ø±Ø©', 'class' => 'bg-amber-100 text-amber-700'],
                                            'paid' => ['label' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ø¯ÙÙˆØ¹Ø©', 'class' => 'bg-emerald-100 text-emerald-700'],
                                            'void' => ['label' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ù„ØºØ§Ø©', 'class' => 'bg-rose-100 text-rose-700'],
                                        ];
                                        $invoiceBadge = $invoice ? ($invoiceStatusMeta[$invoice->status] ?? $invoiceStatusMeta['draft']) : null;
                                    @endphp
                                    <tr id="booking-row-{{ $booking->id }}" class="activity-item hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200" data-admin-note="{{ base64_encode($booking->admin_notes ?? '') }}" data-user-name="{{ e(optional($booking->user)->name ?? 'Ù…Ø³ØªØ®Ø¯Ù…') }}" data-workshop-title="{{ e(optional($booking->workshop)->title ?? 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©') }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                        <i class="fas fa-user text-white text-lg"></i>
                                                    </div>
                                                </div>
                                                <div class="mr-4">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $booking->user->name }}</div>
                                                    <div class="text-sm text-gray-500 flex items-center">
                                                        <i class="fas fa-envelope text-xs ml-1"></i>
                                                        {{ $booking->user->email }}
                                                    </div>
                                                    <div id="booking-note-indicator-{{ $booking->id }}" class="note-indicator mt-2 {{ $booking->admin_notes ? '' : 'hidden' }}">
                                                        <i class="fas fa-sticky-note text-xs"></i>
                                                        ØªÙˆØ¬Ø¯ Ù…Ù„Ø§Ø­Ø¸Ø© Ø¯Ø§Ø®Ù„ÙŠØ©
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 mr-3">
                                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-green-500 to-teal-600 flex items-center justify-center">
                                                        <i class="fas fa-graduation-cap text-white text-sm"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">{{ $booking->workshop->title }}</div>
                                                    <div class="text-sm text-gray-500 flex items-center">
                                                        <i class="fas fa-user-tie text-xs ml-1"></i>
                                                        {{ $booking->workshop->instructor }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($booking->status === 'pending')
                                                <span class="status-badge status-pending inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-clock ml-1"></i>
                                                    ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±
                                                </span>
                                            @elseif($booking->status === 'confirmed')
                                                <span class="status-badge status-confirmed inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-check-circle ml-1"></i>
                                                    Ù…Ø¤ÙƒØ¯Ø©
                                                </span>
                                            @else
                                                <span class="status-badge status-cancelled inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-times-circle ml-1"></i>
                                                    Ù…Ù„ØºÙŠØ©
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($booking->payment_status === 'paid')
                                                <span class="status-badge status-confirmed inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-check ml-1"></i>
                                                    Ù…Ø¯ÙÙˆØ¹Ø©
                                                </span>
                                            @elseif($booking->payment_status === 'refunded')
                                                <span class="status-badge status-cancelled inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-undo ml-1"></i>
                                                    Ù…Ø³ØªØ±Ø¯Ø©
                                                </span>
                                            @else
                                                <span class="status-badge status-pending inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-clock ml-1"></i>
                                                    ÙÙŠ Ø§Ù„Ø§Ù†ØªØ¸Ø§Ø±
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @if($financialBadge)
                                                <span class="{{ $financialBadge['class'] }} inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                    <i class="fas fa-balance-scale ml-1 text-[11px]"></i>
                                                    {{ $financialBadge['label'] }}
                                                </span>
                                            @endif
                                            <div class="mt-2 text-xs text-gray-600 space-y-1">
                                                <div class="flex items-center justify-end gap-1 text-orange-600">
                                                    <i class="fas fa-utensils text-[11px]"></i>
                                                    <span>Ø§Ù„Ø´ÙŠÙ: {{ number_format($chefShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
                                                </div>
                                                <div class="flex items-center justify-end gap-1 text-blue-600">
                                                    <i class="fas fa-handshake text-[11px]"></i>
                                                    <span>Ø§Ù„Ø´Ø±ÙŠÙƒ: {{ number_format($partnerShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
                                                </div>
                                                <div class="flex items-center justify-end gap-1 text-slate-600">
                                                    <i class="fas fa-shield-alt text-[11px]"></i>
                                                    <span>Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©: {{ number_format($adminShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span>
                                {{ number_format($booking->payment_amount, 2) }}
                                {{ strtoupper($booking->payment_currency ?? $defaultCurrency) }}
                            </span>
                                                @if($invoiceBadge)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $invoiceBadge['class'] }}">
                                                        <i class="fas fa-file-invoice ml-1 text-[10px]"></i>
                                                        {{ $invoiceBadge['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                <i class="fas fa-exchange-alt text-[10px]"></i>
                                                â‰ˆ {{ number_format($booking->payment_amount_usd ?? 0, 2) }} {{ $defaultCurrency }}
                                                <span class="text-gray-400">/ Ø³Ø¹Ø± Ø§Ù„ØµØ±Ù {{ number_format($booking->payment_exchange_rate ?? 1, 6) }}</span>
                                            </div>
                                            @if($booking->payment_method)
                                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                    <i class="fas fa-credit-card"></i>
                                                    {{ $booking->payment_method }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $booking->created_at->format('Y-m-d') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-clock ml-1"></i>
                                                {{ $booking->created_at->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2 space-x-reverse">
                                                <a href="{{ route('admin.bookings.show', $booking) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200">
                                                    <i class="fas fa-eye ml-1"></i>
                                                    Ø¹Ø±Ø¶
                                                </a>
                                                @if($invoice)
                                                    <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors duration-200">
                                                        <i class="fas fa-file-invoice ml-1"></i>
                                                        Ø§Ù„ÙØ§ØªÙˆØ±Ø©
                                                    </a>
                                                @endif
                                                <button onclick="openAdminNoteModal({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 transition-colors duration-200">
                                                    <i class="fas fa-sticky-note ml-1"></i>
                                                    Ù…Ù„Ø§Ø­Ø¸Ø©
                                                </button>
                                                @if($booking->status === 'pending')
                                                    <button onclick="confirmBooking({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200">
                                                        <i class="fas fa-check ml-1"></i>
                                                        ØªØ£ÙƒÙŠØ¯
                                                    </button>
                                                    <button onclick="cancelBooking({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200">
                                                        <i class="fas fa-times ml-1"></i>
                                                        Ø¥Ù„ØºØ§Ø¡
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Ø¹Ø±Ø¶ {{ $bookings->firstItem() ?? 0 }} Ø¥Ù„Ù‰ {{ $bookings->lastItem() ?? 0 }} Ù…Ù† Ø£ØµÙ„ {{ $bookings->total() }} Ù†ØªÙŠØ¬Ø©
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            {{ $bookings->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-16">
                        @if(request()->hasAny(['status', 'payment_status', 'financial_status', 'workshop_id', 'date_from', 'date_to', 'search', 'workshop_type', 'price_range', 'payment_method', 'booking_count', 'workshop_date_from', 'workshop_date_to', 'sort_by', 'sort_direction']))
                            <div class="mx-auto w-24 h-24 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-search text-blue-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Ù„Ø§ ØªÙˆØ¬Ø¯ Ù†ØªØ§Ø¦Ø¬</h3>
                            <p class="text-gray-500 mb-6 max-w-md mx-auto">Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø­Ø¬ÙˆØ²Ø§Øª ØªØ·Ø§Ø¨Ù‚ Ø§Ù„Ù…Ø¹Ø§ÙŠÙŠØ± Ø§Ù„Ù…Ø­Ø¯Ø¯Ø©. Ø¬Ø±Ø¨ ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„ÙÙ„Ø§ØªØ± Ø£Ùˆ Ø§Ù„Ø¨Ø­Ø« Ø¨ÙƒÙ„Ù…Ø§Øª Ù…Ø®ØªÙ„ÙØ©.</p>
                            <div class="flex justify-center space-x-4 space-x-reverse">
                                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-times ml-2"></i>
                                    Ù…Ø³Ø­ Ø§Ù„ÙÙ„Ø§ØªØ±
                                </a>
                                <button onclick="document.getElementById('bookingFiltersForm').reset(); document.getElementById('bookingFiltersForm').submit();" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-redo ml-2"></i>
                                    Ø¥Ø¹Ø§Ø¯Ø© ØªØ¹ÙŠÙŠÙ†
                                </button>
                            </div>
                        @else
                            <div class="mx-auto w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø­Ø¬ÙˆØ²Ø§Øª</h3>
                            <p class="text-gray-500 mb-6">Ù„Ù… ÙŠØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø£ÙŠ Ø­Ø¬ÙˆØ²Ø§Øª Ø¨Ø¹Ø¯. Ø§Ø¨Ø¯Ø£ Ø¨Ø¥Ø¶Ø§ÙØ© Ø­Ø¬Ø² Ø¬Ø¯ÙŠØ¯ Ø£Ùˆ Ø§Ù†ØªØ¸Ø± Ø­ØªÙ‰ ÙŠÙ‚ÙˆÙ… Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙˆÙ† Ø¨Ø§Ù„Ø­Ø¬Ø².</p>
                            <a href="{{ route('admin.bookings.manual') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-plus ml-2"></i>
                                Ø¥Ø¶Ø§ÙØ© Ø­Ø¬Ø² Ø¬Ø¯ÙŠØ¯
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Admin Note Modal -->
<div id="adminNoteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden modal-backdrop">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-lg shadow-lg rounded-2xl bg-white modal-content">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-sticky-note text-purple-500 ml-2"></i>
                    Ù…Ù„Ø§Ø­Ø¸Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©
                </h3>
                <p class="text-sm text-gray-500 mt-1">ØªÙØ­ÙØ¸ Ù‡Ø°Ù‡ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø© Ù„Ù„Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„Ø¯Ø§Ø®Ù„ÙŠ ÙˆÙ„Ø§ ØªØ¸Ù‡Ø± Ù„Ù„Ù…Ø³ØªØ®Ø¯Ù….</p>
            </div>
            <button id="adminNoteClose" type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-5 space-y-5">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-700">
                <div class="flex flex-col gap-1">
                    <span><i class="fas fa-user text-blue-500 ml-1"></i>Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…: <span id="adminNoteUser" class="font-semibold text-gray-900"></span></span>
                    <span><i class="fas fa-graduation-cap text-emerald-500 ml-1"></i>Ø§Ù„ÙˆØ±Ø´Ø©: <span id="adminNoteWorkshop" class="font-semibold text-gray-900"></span></span>
                </div>
            </div>
            <div>
                <label for="adminNoteTextarea" class="block text-sm font-medium text-gray-700 mb-2">Ø§ÙƒØªØ¨ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø§Øª Ø§Ù„Ø¯Ø§Ø®Ù„ÙŠØ©</label>
                <textarea id="adminNoteTextarea" rows="5" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-purple-500 focus:border-purple-500" maxlength="2000" placeholder="Ù…Ø«Ø§Ù„: ØªÙ… Ø§Ù„ØªÙˆØ§ØµÙ„ Ù…Ø¹ Ø§Ù„Ø¹Ù…ÙŠÙ„ Ø¨Ø®ØµÙˆØµ Ø§Ù„Ø¯ÙØ¹..."></textarea>
                <div class="mt-2 flex items-center justify-between">
                    <span id="adminNoteCharCounter" class="text-xs text-gray-400">0/2000</span>
                    <div id="adminNoteError" data-default-text="ØªØ¹Ø°Ø± Ø­ÙØ¸ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø©ØŒ Ø­Ø§ÙˆÙ„ Ù…Ø±Ø© Ø£Ø®Ø±Ù‰." class="hidden text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>ØªØ¹Ø°Ø± Ø­ÙØ¸ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø©ØŒ Ø­Ø§ÙˆÙ„ Ù…Ø±Ø© Ø£Ø®Ø±Ù‰.</span>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="adminNoteBookingId">
        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" id="adminNoteCancel" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-times ml-1"></i>
                Ø¥ØºÙ„Ø§Ù‚
            </button>
            <button type="button" id="adminNoteSaveButton" class="inline-flex items-center px-5 py-2 rounded-md border border-transparent text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <i class="fas fa-save ml-1"></i>
                Ø­ÙØ¸ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø©
            </button>
        </div>
    </div>
</div>
<!-- Custom Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden modal-backdrop">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
        <div class="mt-3 text-center">
            <!-- Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-question-circle text-blue-600 text-xl"></i>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-2" id="modalTitle">
                ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©
            </h3>
            
            <!-- Message -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">
                    Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ù…Ù† ØªØ£ÙƒÙŠØ¯ Ù‡Ø°Ø§ Ø§Ù„Ø­Ø¬Ø²ØŸ
                </p>
            </div>
            
            <!-- Buttons -->
            <div class="items-center px-4 py-3">
                <div class="flex justify-center space-x-4 space-x-reverse">
                    <button id="modalCancel" class="modal-button px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                        <i class="fas fa-times ml-2"></i>
                        Ø¥Ù„ØºØ§Ø¡
                    </button>
                    <button id="modalConfirm" class="modal-button px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                        <i class="fas fa-check ml-2"></i>
                        ØªØ£ÙƒÙŠØ¯
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden modal-backdrop">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
        <div class="mt-3 text-center">
            <!-- Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                ØªÙ†Ø¨ÙŠÙ‡
            </h3>
            
            <!-- Message -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="alertMessage">
                    Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªÙ†ÙÙŠØ° Ø§Ù„Ø¹Ù…Ù„ÙŠØ©
                </p>
            </div>
            
            <!-- Button -->
            <div class="items-center px-4 py-3">
                <div class="flex justify-center">
                    <button id="alertOk" class="modal-button px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-check ml-2"></i>
                        Ù…ÙˆØ§ÙÙ‚
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ÙˆØ¸Ø§Ø¦Ù Ø¥Ø¶Ø§ÙÙŠØ©
function refreshBookings() {
    location.reload();
}

function exportBookings() {
    // Ø¥Ø¶Ø§ÙØ© Ù…Ø¹Ø§Ù…Ù„Ø§Øª Ø§Ù„ØªØµØ¯ÙŠØ±
    const url = new URL(window.location);
    url.searchParams.set('export', 'excel');
    window.open(url.toString(), '_blank');
}

function printBookings() {
    window.print();
}

// ØªØ­Ø³ÙŠÙ† ÙˆØ¸Ø§Ø¦Ù Ø§Ù„Ø­Ø¬Ø²
function encodeBase64Unicode(str) {
    try {
        return window.btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(_, p1) {
            return String.fromCharCode(parseInt(p1, 16));
        }));
    } catch (error) {
        return '';
    }
}

function decodeBase64Unicode(str) {
    if (!str) {
        return '';
    }

    try {
        return decodeURIComponent(Array.prototype.map.call(window.atob(str), function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    } catch (error) {
        return '';
    }
}

function updateAdminNoteCounter() {
    const textarea = document.getElementById('adminNoteTextarea');
    const counter = document.getElementById('adminNoteCharCounter');
    if (!textarea || !counter) {
        return;
    }
    counter.textContent = `${textarea.value.length}/2000`;
}

function openAdminNoteModal(bookingId) {
    const modal = document.getElementById('adminNoteModal');
    const textarea = document.getElementById('adminNoteTextarea');
    const bookingIdInput = document.getElementById('adminNoteBookingId');
    const userSpan = document.getElementById('adminNoteUser');
    const workshopSpan = document.getElementById('adminNoteWorkshop');
    const errorEl = document.getElementById('adminNoteError');

    if (!modal || !textarea || !bookingIdInput) {
        return;
    }

    const row = document.getElementById(`booking-row-${bookingId}`);
    if (!row) {
        return;
    }

    const noteValue = decodeBase64Unicode(row.dataset.adminNote || '');
    textarea.value = noteValue;
    bookingIdInput.value = bookingId;

    if (userSpan) {
        userSpan.textContent = row.dataset.userName || 'Ù…Ø³ØªØ®Ø¯Ù…';
    }

    if (workshopSpan) {
        workshopSpan.textContent = row.dataset.workshopTitle || 'ÙˆØ±Ø´Ø©';
    }

    if (errorEl) {
        errorEl.classList.add('hidden');
        const defaultMessage = errorEl.getAttribute('data-default-text');
        if (defaultMessage) {
            errorEl.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${defaultMessage}</span>`;
        }
    }

    updateAdminNoteCounter();

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });

    textarea.focus({ preventScroll: true });
}

function closeAdminNoteModal() {
    const modal = document.getElementById('adminNoteModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

function saveAdminNote() {
    const bookingIdInput = document.getElementById('adminNoteBookingId');
    const textarea = document.getElementById('adminNoteTextarea');
    const errorEl = document.getElementById('adminNoteError');
    const saveButton = document.getElementById('adminNoteSaveButton');

    if (!bookingIdInput || !textarea || !saveButton) {
        return;
    }

    const bookingId = bookingIdInput.value;
    const noteValue = textarea.value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    saveButton.disabled = true;
    const originalContent = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i>Ø¬Ø§Ø±ÙŠ Ø§Ù„Ø­ÙØ¸...';

    fetch(`/admin/bookings/${bookingId}/admin-note`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ admin_note: noteValue }),
    })
        .then(async (response) => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw payload;
            }
            return payload;
        })
        .then((data) => {
            if (errorEl) {
                errorEl.classList.add('hidden');
            }

            const row = document.getElementById(`booking-row-${bookingId}`);
            if (row) {
                row.dataset.adminNote = noteValue ? encodeBase64Unicode(noteValue) : '';
                const indicator = document.getElementById(`booking-note-indicator-${bookingId}`);
                if (indicator) {
                    if (noteValue.trim().length > 0) {
                        indicator.classList.remove('hidden');
                    } else {
                        indicator.classList.add('hidden');
                    }
                }
            }

            const message = data?.message || 'ØªÙ… Ø­ÙØ¸ Ù…Ù„Ø§Ø­Ø¸Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø© Ø¨Ù†Ø¬Ø§Ø­';
            showAlertModal(message);
            closeAdminNoteModal();
        })
        .catch((error) => {
            if (errorEl) {
                const defaultMessage = errorEl.getAttribute('data-default-text') || 'ØªØ¹Ø°Ø± Ø­ÙØ¸ Ø§Ù„Ù…Ù„Ø§Ø­Ø¸Ø©ØŒ Ø­Ø§ÙˆÙ„ Ù…Ø±Ø© Ø£Ø®Ø±Ù‰.';
                const finalMessage = typeof error?.message === 'string' ? error.message : defaultMessage;
                errorEl.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${finalMessage}</span>`;
                errorEl.classList.remove('hidden');
            } else {
                console.error('Failed to save admin note', error);
            }
        })
        .finally(() => {
            saveButton.disabled = false;
            saveButton.innerHTML = originalContent;
        });
}
function confirmBooking(bookingId) {
    showConfirmationModal(
        'ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø­Ø¬Ø²',
        'Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ù…Ù† ØªØ£ÙƒÙŠØ¯ Ù‡Ø°Ø§ Ø§Ù„Ø­Ø¬Ø²ØŸ',
        () => {
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            fetch(`/admin/bookings/${bookingId}/confirm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showAlertModal(data.message);
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø­Ø¬Ø²');
                button.innerHTML = originalContent;
                button.disabled = false;
            });
        }
    );
}

function cancelBooking(bookingId) {
    showCancellationModal(bookingId);
}

// Modal Functions
function showConfirmationModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    
    // Clear previous event listeners
    const confirmBtn = document.getElementById('modalConfirm');
    const cancelBtn = document.getElementById('modalCancel');
    
    // Remove existing listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    
    // Add new listeners
    document.getElementById('modalConfirm').addEventListener('click', () => {
        hideConfirmationModal();
        onConfirm();
    });
    
    document.getElementById('modalCancel').addEventListener('click', hideConfirmationModal);
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideConfirmationModal();
        }
    });
}

function hideConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function showAlertModal(message) {
    document.getElementById('alertMessage').textContent = message;
    const modal = document.getElementById('alertModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    
    // Clear previous event listeners
    const okBtn = document.getElementById('alertOk');
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    
    // Add new listener
    document.getElementById('alertOk').addEventListener('click', hideAlertModal);
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideAlertModal();
        }
    });
}

function hideAlertModal() {
    const modal = document.getElementById('alertModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function showCancellationModal(bookingId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-backdrop';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
            <div class="mt-3 text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ø­Ø¬Ø²
                </h3>
                
                <!-- Message -->
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡:
                    </p>
                    <textarea id="cancellationReason" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                        rows="3" 
                        placeholder="Ø£Ø¯Ø®Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡ Ù‡Ù†Ø§..."
                        required></textarea>
                    <div id="cancellationError" class="hidden mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle ml-1"></i>
                        ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø³Ø¨Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="items-center px-4 py-3">
                    <div class="flex justify-center space-x-4 space-x-reverse">
                        <button id="cancelCancel" class="modal-button px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                            <i class="fas fa-times ml-2"></i>
                            Ø¥Ù„ØºØ§Ø¡
                        </button>
                        <button id="cancelConfirm" class="modal-button px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-check ml-2"></i>
                            ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø¥Ù„ØºØ§Ø¡
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Event listeners
    document.getElementById('cancelCancel').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    // Hide error when user starts typing
    document.getElementById('cancellationReason').addEventListener('input', () => {
        document.getElementById('cancellationError').classList.add('hidden');
    });
    
    document.getElementById('cancelConfirm').addEventListener('click', () => {
        const reason = document.getElementById('cancellationReason').value.trim();
        const errorDiv = document.getElementById('cancellationError');
        
        if (reason === '') {
            errorDiv.classList.remove('hidden');
            return;
        } else {
            errorDiv.classList.add('hidden');
        }
        
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        fetch(`/admin/bookings/${bookingId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cancellation_reason: reason
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlertModal(data.message);
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlertModal('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ø­Ø¬Ø²');
            button.innerHTML = originalContent;
            button.disabled = false;
        });
        
        document.body.removeChild(modal);
    });
}

// ØªØ­Ø³ÙŠÙ† ÙˆØ¸Ø§Ø¦Ù Ø§Ù„ÙÙ„ØªØ±Ø©
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bookingFiltersForm');
    if (!form) {
        return;
    }

    const dateFromInput = form.querySelector('input[name="date_from"]');
    const dateToInput = form.querySelector('input[name="date_to"]');
    const workshopDateFromInput = form.querySelector('input[name="workshop_date_from"]');
    const workshopDateToInput = form.querySelector('input[name="workshop_date_to"]');
    const statusSelect = document.getElementById('statusSelect');
    const quickFilterChips = form.querySelectorAll('.quick-filter-chip');
    const clearFiltersButton = document.getElementById('clearFiltersButton');
    const toggleAdvancedFiltersButton = document.getElementById('toggleAdvancedFilters');
    const advancedFiltersSection = document.getElementById('advancedFilters');
    const advancedFiltersLabel = document.getElementById('advancedFiltersToggleLabel');
    const adminNoteModal = document.getElementById('adminNoteModal');
    const adminNoteTextarea = document.getElementById('adminNoteTextarea');
    const adminNoteSaveButton = document.getElementById('adminNoteSaveButton');
    const adminNoteCancelButton = document.getElementById('adminNoteCancel');
    const adminNoteCloseButton = document.getElementById('adminNoteClose');

    if (adminNoteTextarea) {
        adminNoteTextarea.addEventListener('input', updateAdminNoteCounter);
        updateAdminNoteCounter();
    }

    if (adminNoteSaveButton) {
        adminNoteSaveButton.addEventListener('click', saveAdminNote);
    }

    if (adminNoteCancelButton) {
        adminNoteCancelButton.addEventListener('click', closeAdminNoteModal);
    }

    if (adminNoteCloseButton) {
        adminNoteCloseButton.addEventListener('click', closeAdminNoteModal);
    }

    if (adminNoteModal) {
        adminNoteModal.addEventListener('click', function(event) {
            if (event.target === adminNoteModal) {
                closeAdminNoteModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('adminNoteModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAdminNoteModal();
            }
        }
    });


    // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø§Ù„ØªÙˆØ§Ø±ÙŠØ® Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©
    function validateDates() {
        if (!dateFromInput || !dateToInput) {
            return true;
        }

        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;

        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            showAlertModal('ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¨Ø¯Ø§ÙŠØ© ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† Ù‚Ø¨Ù„ ØªØ§Ø±ÙŠØ® Ø§Ù„Ù†Ù‡Ø§ÙŠØ©');
            dateToInput.value = '';
            return false;
        }
        return true;
    }

    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            if (this.value && dateToInput.value) {
                validateDates();
            }
        });

        dateToInput.addEventListener('change', function() {
            if (this.value && dateFromInput.value) {
                validateDates();
            }
        });
    }

    if (toggleAdvancedFiltersButton && advancedFiltersSection) {
        toggleAdvancedFiltersButton.addEventListener('click', () => {
            const isHidden = advancedFiltersSection.classList.toggle('hidden');
            toggleAdvancedFiltersButton.setAttribute('aria-expanded', (!isHidden).toString());
            if (advancedFiltersLabel) {
                advancedFiltersLabel.textContent = isHidden ? 'Ø¹Ø±Ø¶ Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©' : 'Ø¥Ø®ÙØ§Ø¡ Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©';
            }
        });
    }

    const filterInputs = form.querySelectorAll('select, input[type="date"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (validateDates()) {
                form.submit();
            }
        });
    });

    // Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© ØªÙˆØ§Ø±ÙŠØ® Ø§Ù„ÙˆØ±Ø´Ø©
    function validateWorkshopDates() {
        if (!workshopDateFromInput || !workshopDateToInput) {
            return true;
        }

        const dateFrom = workshopDateFromInput.value;
        const dateTo = workshopDateToInput.value;

        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            showAlertModal('ØªØ§Ø±ÙŠØ® Ø¨Ø¯Ø§ÙŠØ© Ø§Ù„ÙˆØ±Ø´Ø© ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† Ù‚Ø¨Ù„ ØªØ§Ø±ÙŠØ® Ù†Ù‡Ø§ÙŠØ© Ø§Ù„ÙˆØ±Ø´Ø©');
            workshopDateToInput.value = '';
            return false;
        }
        return true;
    }

    if (workshopDateFromInput && workshopDateToInput) {
        workshopDateFromInput.addEventListener('change', function() {
            if (this.value && workshopDateToInput.value) {
                validateWorkshopDates();
            }
        });

        workshopDateToInput.addEventListener('change', function() {
            if (this.value && workshopDateFromInput.value) {
                validateWorkshopDates();
            }
        });
    }

    // Ø§Ù„Ø¨Ø­Ø« Ù…Ø¹ ØªØ£Ø®ÙŠØ±
    const searchInput = form.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    form.submit();
                }
            }, 500);
        });
    }

    // Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ø³Ø±ÙŠØ¹Ø© Ù„Ù„Ø­Ø§Ù„Ø©
    quickFilterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            quickFilterChips.forEach(btn => btn.classList.remove('is-active'));
            chip.classList.add('is-active');

            if (statusSelect) {
                statusSelect.value = chip.getAttribute('data-status-value') || '';
                statusSelect.dispatchEvent(new Event('change'));
            } else {
                form.submit();
            }
        });
    });

    if (clearFiltersButton) {
        clearFiltersButton.addEventListener('click', () => {
            form.reset();
            if (statusSelect) {
                statusSelect.value = '';
            }

            quickFilterChips.forEach(btn => btn.classList.remove('is-active'));
            const defaultChip = Array.from(quickFilterChips).find(btn => (btn.getAttribute('data-status-value') || '') === '');
            if (defaultChip) {
                defaultChip.classList.add('is-active');
            }

            if (validateDates()) {
                form.submit();
            }
        });
    }
});
</script>
@endsection










