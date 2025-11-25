@extends('layouts.app')

@section('title', 'إدارة الحجوزات')

@php
    $totalBookings = max($stats['total'], 1);

    $statusCards = [
        [
            'label' => 'إجمالي الحجوزات',
            'value' => $stats['total'],
            'formatted' => number_format($stats['total']),
            'icon' => 'fa-calendar-check',
            'gradient' => 'from-sky-500 via-indigo-500 to-purple-500',
            'description' => 'جميع الحجوزات المسجلة في النظام',
            'percentage' => null,
        ],
        [
            'label' => 'قيد المراجعة',
            'value' => $stats['pending'],
            'formatted' => number_format($stats['pending']),
            'icon' => 'fa-hourglass-half',
            'gradient' => 'from-amber-500 to-orange-500',
            'description' => 'حجوزات تنتظر الإجراء',
            'percentage' => round(($stats['pending'] / $totalBookings) * 100, 1),
        ],
        [
            'label' => 'مؤكدة',
            'value' => $stats['confirmed'],
            'formatted' => number_format($stats['confirmed']),
            'icon' => 'fa-check-circle',
            'gradient' => 'from-emerald-500 to-teal-500',
            'description' => 'تم تأكيدها للمشاركين',
            'percentage' => round(($stats['confirmed'] / $totalBookings) * 100, 1),
        ],
        [
            'label' => 'ملغية',
            'value' => $stats['cancelled'],
            'formatted' => number_format($stats['cancelled']),
            'icon' => 'fa-times-circle',
            'gradient' => 'from-rose-500 to-red-500',
            'description' => 'تحتاج تحليل أسباب الإلغاء',
            'percentage' => round(($stats['cancelled'] / $totalBookings) * 100, 1),
        ],
    ];

    $quickStatusFilters = [
        [
            'label' => 'الكل',
            'value' => null,
            'count' => number_format($stats['total']),
            'icon' => 'fa-layer-group',
            'hint' => 'عرض جميع الحجوزات',
        ],
        [
            'label' => 'قيد المراجعة',
            'value' => 'pending',
            'count' => number_format($stats['pending']),
            'icon' => 'fa-hourglass-half',
            'hint' => 'الحجوزات التي لم يتم اتخاذ إجراء بشأنها',
        ],
        [
            'label' => 'مؤكدة',
            'value' => 'confirmed',
            'count' => number_format($stats['confirmed']),
            'icon' => 'fa-check-circle',
            'hint' => 'الحجوزات الجاهزة للورشة',
        ],
        [
            'label' => 'ملغية',
            'value' => 'cancelled',
            'count' => number_format($stats['cancelled']),
            'icon' => 'fa-ban',
            'hint' => 'إلغاءات تحتاج متابعة',
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
            'label' => 'بانتظار الدفع',
            'class' => 'bg-amber-100 text-amber-700',
        ],
        'paid' => [
            'label' => 'مدفوعة',
            'class' => 'bg-emerald-100 text-emerald-700',
        ],
        'refunded' => [
            'label' => 'مستردة',
            'class' => 'bg-purple-100 text-purple-700',
        ],
    ];

    $financialStatusMeta = [
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING => [
            'label' => 'بانتظار التوزيع',
            'class' => 'bg-slate-100 text-slate-700',
        ],
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED => [
            'label' => 'تم التوزيع',
            'class' => 'bg-emerald-100 text-emerald-700',
        ],
        \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID => [
            'label' => 'معلق أو ملغي',
            'class' => 'bg-rose-100 text-rose-700',
        ],
    ];

    $financialStatusFilters = [
        [
            'label' => 'الكل',
            'value' => null,
            'hint' => 'عرض جميع الحالات المالية',
        ],
        [
            'label' => 'بانتظار التوزيع',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING,
            'hint' => 'الحجوزات التي لم يتم توزيع مبالغها بعد',
        ],
        [
            'label' => 'تم التوزيع',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED,
            'hint' => 'الحجوزات التي تم تقسيم عوائدها تلقائياً',
        ],
        [
            'label' => 'معلق أو ملغي',
            'value' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID,
            'hint' => 'الحجوزات التي تم إيقاف توزيعها',
        ],
    ];

    $defaultCurrency = config('finance.default_currency', 'USD');
    $currencyOptions = $currencyOptions ?? \App\Support\Currency::all();
    $shareTotals = $stats['share_totals'] ?? ['chef' => 0, 'partner' => 0, 'admin' => 0];
    $paidCurrencies = $stats['paid_currencies'] ?? [];

    $statusCards[] = [
        'label' => 'مدفوعات مؤكدة',
        'value' => $stats['paid_amount'] ?? 0,
        'formatted' => number_format($stats['paid_amount'] ?? 0, 2) . ' ' . $defaultCurrency,
        'icon' => 'fa-wallet',
        'gradient' => 'from-emerald-500 to-lime-500',
        'description' => 'إجمالي المبالغ المدفوعة حتى الآن',
        'percentage' => null,
    ];

    $statusCards[] = [
        'label' => 'حجوزات موزعة مالياً',
        'value' => $stats['financial']['distributed'] ?? 0,
        'formatted' => number_format($stats['financial']['distributed'] ?? 0),
        'icon' => 'fa-coins',
        'gradient' => 'from-cyan-500 to-blue-500',
        'description' => 'عدد الحجوزات التي تم تقسيم حصصها',
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
    color: #1e3a8a;
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
    color: #1e40af;
    font-weight: 700;
}

.quick-filter-chip.is-active {
    color: #fff;
    border-color: transparent;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
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
    color: #1d4ed8;
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
                        إدارة الحجوزات
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        متابعة سريعة للحجوزات، المدفوعات، والإجراءات اليومية.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">
                    <a href="{{ route('admin.bookings.manual') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
                        <i class="fas fa-plus"></i>
                        إضافة حجز يدوي
                    </a>
                    <a href="{{ route('admin.bookings.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-blue-200 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                        <i class="fas fa-file-export"></i>
                        تصدير النتائج
                    </a>
                    <button type="button" onclick="refreshBookings()" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300">
                        <i class="fas fa-sync-alt"></i>
                        تحديث
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-right"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
            <div class="flex items-center text-sm text-gray-500 gap-2">
                <i class="fas fa-clock text-gray-400"></i>
                <span>آخر تحديث: {{ now()->format('Y-m-d H:i') }}</span>
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
                                حجوزات جديدة بانتظار الإجراء
                            </h2>
                            <p class="text-sm text-gray-500">
                                راجع أحدث الحجوزات وقرر تأكيدها أو رفضها مباشرة بدون الحاجة للتمرير إلى الأسفل.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('admin.bookings.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
                                class="inline-flex items-center px-4 py-2 rounded-md border border-blue-200 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors duration-200"
                            >
                                <i class="fas fa-filter ml-2"></i>
                                عرض كل الحجوزات المعلقة
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
                                            المستخدم
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-graduation-cap ml-2"></i>
                                            الورشة
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-credit-card ml-2"></i>
                                            الدفع
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-calendar ml-2"></i>
                                            تاريخ الحجز
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <i class="fas fa-cogs ml-2"></i>
                                            الإجراءات السريعة
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
                                                        {{ $pendingUser?->name ?? 'مستخدم بدون اسم' }}
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
                                                    {{ $pendingWorkshop?->title ?? 'ورشة غير محددة' }}
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <i class="fas fa-clock"></i>
                                                    {{ optional($pendingWorkshop?->start_date)->format('Y-m-d H:i') ?? 'غير مجدول' }}
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
                                                        عرض
                                                    </a>
                                                    <button
                                                        type="button"
                                                        onclick="confirmBooking({{ $pendingBooking->id }})"
                                                        class="inline-flex items-center px-3 py-1 rounded-md border border-transparent text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200"
                                                    >
                                                        <i class="fas fa-check ml-1"></i>
                                                        تأكيد
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onclick="cancelBooking({{ $pendingBooking->id }})"
                                                        class="inline-flex items-center px-3 py-1 rounded-md border border-transparent text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200"
                                                    >
                                                        <i class="fas fa-times ml-1"></i>
                                                        إلغاء
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
                            لا توجد حجوزات جديدة بانتظار الموافقة حالياً.
                        </div>
                    @endif
                </div>
            </div>
        @endisset

        <!-- نظرة عامة سريعة -->
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
                    <p class="text-xs font-semibold text-gray-500">حصة المنصة</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['admin'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">تشمل الرسوم التشغيلية والتقنية</p>
                </div>
                <span class="h-12 w-12 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-xl"></i>
                </span>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500">صافي الشيفات</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['chef'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">يتم تحويله بعد انتهاء الورشات</p>
                </div>
                <span class="h-12 w-12 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center">
                    <i class="fas fa-utensils text-xl"></i>
                </span>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500">عمولات الشركاء</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($shareTotals['partner'] ?? 0, 2) }} {{ $defaultCurrency }}</p>
                    <p class="mt-1 text-xs text-gray-500">يتم تتبعها عبر لوحة برنامج الإحالة</p>
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
                            توزيع المدفوعات حسب العملة
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            راقب العملات المستخدمة في الحجوزات المدفوعة لضمان جاهزية التحويلات المالية وإصدار الفواتير بالعملة الصحيحة.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm font-semibold">
                        <i class="fas fa-money-bill-wave"></i>
                        {{ count($paidCurrencies) }} عملة نشطة
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
                                    {{ number_format($currencyStat['total_bookings']) }} حجوزات
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-exchange-alt text-gray-400"></i>
                                    ≈ {{ number_format($currencyStat['total_amount_usd'] ?? 0, 2) }} {{ $defaultCurrency }}
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
                            حجوزات تحتاج متابعة
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            الطلبات التي تجاوزت 48 ساعة بدون إجراء. تعامل معها أولاً لضمان تجربة أفضل للمستخدمين.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-sm font-semibold">
                        <i class="fas fa-clock"></i>
                        {{ $pendingFollowUpCount ?? $followUpBookings->count() }} حجوزات متأخرة
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($followUpBookings->take(6) as $pending)
                        @php
                            $userName = optional($pending->user)->name ?? 'مستخدم';
                            $workshopTitle = optional($pending->workshop)->title ?? 'ورشة غير محددة';
                        @endphp
                        <div class="border border-amber-100 rounded-lg p-4 flex flex-col gap-3 bg-amber-50">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $userName }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-calendar text-[11px] ml-1"></i>
                                    {{ optional($pending->created_at)->format('Y-m-d H:i') ?? 'غير معروف' }}
                                </p>
                            </div>
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-graduation-cap text-[11px] ml-1 text-amber-500"></i>
                                {{ $workshopTitle }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-phone ml-1"></i>
                                {{ optional($pending->user)->phone ?? 'لا يوجد رقم' }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.bookings.show', $pending) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-md bg-white text-sm font-semibold text-blue-700 hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-eye ml-1"></i>
                                    مراجعة
                                </a>
                                <button type="button" onclick="confirmBooking({{ $pending->id }})" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-green-500 text-white text-sm font-semibold hover:bg-green-600 transition-colors">
                                    <i class="fas fa-check ml-1"></i>
                                    تأكيد
                                </button>
                                <button type="button" onclick="cancelBooking({{ $pending->id }})" class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-red-200 bg-white text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-times ml-1"></i>
                                    إلغاء
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($followUpBookings->count() > 6)
                    <div class="mt-4 text-sm text-gray-500">
                        عرضنا أهم {{ min(6, $followUpBookings->count()) }} حجوزات تحتاج متابعة. يمكنك الوصول للباقي من خلال الفلاتر أو الجدول أدناه.
                    </div>
                @endif
            </div>
        @endif
        <!-- الفلاتر -->
        <div class="bg-white shadow-xl rounded-2xl mb-8 border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter text-blue-500 ml-2"></i>
                        فلترة الحجوزات
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">اختصر الوقت باستعمال الفلاتر الذكية أدناه</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="toggleAdvancedFilters" class="inline-flex items-center px-4 py-2 rounded-md border border-blue-200 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors duration-200" aria-expanded="{{ $advancedFiltersActive ? 'true' : 'false' }}">
                        <i class="fas fa-sliders-h ml-2"></i>
                        <span id="advancedFiltersToggleLabel">{{ $advancedFiltersActive ? 'إخفاء الفلاتر المتقدمة' : 'عرض الفلاتر المتقدمة' }}</span>
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-redo ml-2"></i>
                        إعادة التعيين
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                        <select id="statusSelect" name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">جميع الحالات</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكدة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حالة الدفع</label>
                        <select name="payment_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">جميع حالات الدفع</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مستردة</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">التوزيع المالي</label>
                        <select name="financial_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">جميع الحالات المالية</option>
                            @foreach($financialStatusFilters as $filter)
                                @continue(is_null($filter['value']))
                                <option value="{{ $filter['value'] }}" {{ request('financial_status') == $filter['value'] ? 'selected' : '' }}>
                                    {{ $filter['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الورشة</label>
                        <select name="workshop_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">جميع الورشات</option>
                            @foreach($workshops as $workshop)
                                <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                    {{ $workshop->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" max="{{ date('Y-m-d') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" max="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div id="advancedFilters" class="border-t border-gray-200 pt-4 {{ $advancedFiltersActive ? '' : 'hidden' }}">
                    <h4 class="text-sm font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-cogs ml-2"></i>
                        فلاتر متقدمة
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نوع الورشة</label>
                            <select name="workshop_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">جميع الأنواع</option>
                                <option value="online" {{ request('workshop_type') == 'online' ? 'selected' : '' }}>أونلاين</option>
                                <option value="offline" {{ request('workshop_type') == 'offline' ? 'selected' : '' }}>أوفلاين</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نطاق السعر</label>
                            <select name="price_range" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">جميع الأسعار</option>
                                <option value="0-50" {{ request('price_range') == '0-50' ? 'selected' : '' }}>0 - 50 دولار أمريكي</option>
                                <option value="50-100" {{ request('price_range') == '50-100' ? 'selected' : '' }}>50 - 100 دولار أمريكي</option>
                                <option value="100-200" {{ request('price_range') == '100-200' ? 'selected' : '' }}>100 - 200 دولار أمريكي</option>
                                <option value="200-500" {{ request('price_range') == '200-500' ? 'selected' : '' }}>200 - 500 دولار أمريكي</option>
                                <option value="500+" {{ request('price_range') == '500+' ? 'selected' : '' }}>أكثر من 500 دولار أمريكي</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع</label>
                            <select name="payment_method" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">جميع الطرق</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">عملة الدفع</label>
                            <select name="payment_currency" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">جميع العملات</option>
                                @foreach($currencyOptions as $code => $currency)
                                    <option value="{{ $code }}" {{ strtoupper(request('payment_currency')) == $code ? 'selected' : '' }}>
                                        {{ $currency['label'] ?? $code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">عدد الحجوزات</label>
                            <select name="booking_count" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">جميع المستخدمين</option>
                                <option value="single" {{ request('booking_count') == 'single' ? 'selected' : '' }}>حجز واحد</option>
                                <option value="multiple" {{ request('booking_count') == 'multiple' ? 'selected' : '' }}>عدة حجوزات</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الورشة (من)</label>
                            <input type="date" name="workshop_date_from" value="{{ request('workshop_date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الورشة (إلى)</label>
                            <input type="date" name="workshop_date_to" value="{{ request('workshop_date_to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6">
                        <div class="w-full xl:max-w-md">
                            <label for="bookingSearchInput" class="block text-sm font-medium text-gray-700 mb-2">
                                البحث السريع
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
                                    placeholder="اسم المستخدم، البريد الإلكتروني، أو عنوان الورشة"
                                    class="w-full pr-4 pl-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                تظهر النتائج بمجرد الكتابة، أو اضغط إدخال للتأكيد.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 xl:gap-6">
                            <div class="flex gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-2">ترتيب حسب</label>
                                    <select name="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الحجز</option>
                                        <option value="payment_amount" {{ request('sort_by') == 'payment_amount' ? 'selected' : '' }}>المبلغ</option>
                                        <option value="workshop_start_date" {{ request('sort_by') == 'workshop_start_date' ? 'selected' : '' }}>تاريخ الورشة</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-2">اتجاه الترتيب</label>
                                    <select name="sort_direction" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 sm:self-end">
                                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-filter ml-2"></i>
                                    تطبيق البحث
                                </button>
                                <button type="button" id="clearFiltersButton" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <i class="fas fa-undo ml-2"></i>
                                    إعادة الضبط
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- مؤشرات الفلاتر النشطة -->
        @if(request()->hasAny(['status', 'payment_status', 'financial_status', 'workshop_id', 'date_from', 'date_to', 'search', 'workshop_type', 'price_range', 'payment_method', 'payment_currency', 'booking_count', 'workshop_date_from', 'workshop_date_to', 'sort_by', 'sort_direction']))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-filter text-blue-600 ml-2"></i>
                    <span class="text-sm font-medium text-blue-900">الفلاتر النشطة:</span>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times ml-1"></i>
                    مسح جميع الفلاتر
                </a>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                @if(request('status'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        الحالة: {{ request('status') == 'pending' ? 'في الانتظار' : (request('status') == 'confirmed' ? 'مؤكدة' : 'ملغية') }}
                    </span>
                @endif
                @if(request('payment_status'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        الدفع: {{ request('payment_status') == 'pending' ? 'في الانتظار' : (request('payment_status') == 'paid' ? 'مدفوعة' : 'مستردة') }}
                    </span>
                @endif
                @if(request('payment_currency'))
                    @php
                        $selectedCurrency = strtoupper(request('payment_currency'));
                        $currencyLabel = $currencyOptions[$selectedCurrency]['label'] ?? $selectedCurrency;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        العملة: {{ $currencyLabel }}
                    </span>
                @endif
                @if(request('financial_status'))
                    @php
                        $financialFilterLabel = match (request('financial_status')) {
                            \App\Models\WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED => 'تم التوزيع',
                            \App\Models\WorkshopBooking::FINANCIAL_STATUS_VOID => 'معلق أو ملغي',
                            default => 'بانتظار التوزيع',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        التوزيع: {{ $financialFilterLabel }}
                    </span>
                @endif
                @if(request('workshop_id'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        الورشة: {{ $workshops->where('id', request('workshop_id'))->first()->title ?? 'غير محدد' }}
                    </span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        التاريخ: {{ request('date_from') ?: 'بداية' }} - {{ request('date_to') ?: 'نهاية' }}
                    </span>
                @endif
                @if(request('search'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        البحث: "{{ request('search') }}"
                    </span>
                @endif
                @if(request('workshop_type'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        النوع: {{ request('workshop_type') == 'online' ? 'أونلاين' : 'أوفلاين' }}
                    </span>
                @endif
                @if(request('price_range'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                        السعر: {{ request('price_range') == '0-50' ? '0-50 دولار أمريكي' : (request('price_range') == '50-100' ? '50-100 دولار أمريكي' : (request('price_range') == '100-200' ? '100-200 دولار أمريكي' : (request('price_range') == '200-500' ? '200-500 دولار أمريكي' : 'أكثر من 500 دولار أمريكي'))) }}
                    </span>
                @endif
                @if(request('payment_method'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                        الدفع: {{ request('payment_method') }}
                    </span>
                @endif
                @if(request('booking_count'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        الحجوزات: {{ request('booking_count') == 'single' ? 'واحد فقط' : 'متعددة' }}
                    </span>
                @endif
                @if(request('workshop_date_from') || request('workshop_date_to'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                        تاريخ الورشة: {{ request('workshop_date_from') ?: 'بداية' }} - {{ request('workshop_date_to') ?: 'نهاية' }}
                    </span>
                @endif
                @if(request('sort_by') && request('sort_by') != 'created_at')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                        الترتيب: {{ request('sort_by') == 'payment_amount' ? 'المبلغ' : 'تاريخ الورشة' }} 
                        ({{ request('sort_direction') == 'desc' ? 'تنازلي' : 'تصاعدي' }})
                    </span>
                @endif
            </div>
        </div>
        @endif

        <!-- جدول الحجوزات -->
        <div class="bg-white shadow-lg overflow-hidden rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-green-500 ml-2"></i>
                        قائمة الحجوزات
                    </h3>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="text-sm text-gray-500">
                            عرض {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} من أصل {{ $bookings->total() }} حجز
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <button onclick="exportBookings()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download ml-2"></i>
                                تصدير
                            </button>
                            <button onclick="printBookings()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-print ml-2"></i>
                                طباعة
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
                                        المستخدم
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-graduation-cap ml-2"></i>
                                        الورشة
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-info-circle ml-2"></i>
                                        الحالة
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-credit-card ml-2"></i>
                                        حالة الدفع
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-coins ml-2"></i>
                                        التوزيع المالي
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-money-bill-wave ml-2"></i>
                                        المبلغ
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-calendar ml-2"></i>
                                        تاريخ الحجز
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cogs ml-2"></i>
                                        الإجراءات
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
                                            'draft' => ['label' => 'فاتورة مسودة', 'class' => 'bg-slate-100 text-slate-700'],
                                            'issued' => ['label' => 'فاتورة صادرة', 'class' => 'bg-amber-100 text-amber-700'],
                                            'paid' => ['label' => 'فاتورة مدفوعة', 'class' => 'bg-emerald-100 text-emerald-700'],
                                            'void' => ['label' => 'فاتورة ملغاة', 'class' => 'bg-rose-100 text-rose-700'],
                                        ];
                                        $invoiceBadge = $invoice ? ($invoiceStatusMeta[$invoice->status] ?? $invoiceStatusMeta['draft']) : null;
                                    @endphp
                                    <tr id="booking-row-{{ $booking->id }}" class="activity-item hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200" data-admin-note="{{ base64_encode($booking->admin_notes ?? '') }}" data-user-name="{{ e(optional($booking->user)->name ?? 'مستخدم') }}" data-workshop-title="{{ e(optional($booking->workshop)->title ?? 'غير محددة') }}">
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
                                                        توجد ملاحظة داخلية
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
                                                    في الانتظار
                                                </span>
                                            @elseif($booking->status === 'confirmed')
                                                <span class="status-badge status-confirmed inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-check-circle ml-1"></i>
                                                    مؤكدة
                                                </span>
                                            @else
                                                <span class="status-badge status-cancelled inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-times-circle ml-1"></i>
                                                    ملغية
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($booking->payment_status === 'paid')
                                                <span class="status-badge status-confirmed inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-check ml-1"></i>
                                                    مدفوعة
                                                </span>
                                            @elseif($booking->payment_status === 'refunded')
                                                <span class="status-badge status-cancelled inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-undo ml-1"></i>
                                                    مستردة
                                                </span>
                                            @else
                                                <span class="status-badge status-pending inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-clock ml-1"></i>
                                                    في الانتظار
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
                                                    <span>الشيف: {{ number_format($chefShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
                                                </div>
                                                <div class="flex items-center justify-end gap-1 text-blue-600">
                                                    <i class="fas fa-handshake text-[11px]"></i>
                                                    <span>الشريك: {{ number_format($partnerShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
                                                </div>
                                                <div class="flex items-center justify-end gap-1 text-slate-600">
                                                    <i class="fas fa-shield-alt text-[11px]"></i>
                                                    <span>الإدارة: {{ number_format($adminShare->amount ?? 0, 2) }} {{ $shareCurrency }}</span>
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
                                                ≈ {{ number_format($booking->payment_amount_usd ?? 0, 2) }} {{ $defaultCurrency }}
                                                <span class="text-gray-400">/ سعر الصرف {{ number_format($booking->payment_exchange_rate ?? 1, 6) }}</span>
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
                                                    عرض
                                                </a>
                                                @if($invoice)
                                                    <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors duration-200">
                                                        <i class="fas fa-file-invoice ml-1"></i>
                                                        الفاتورة
                                                    </a>
                                                @endif
                                                <button onclick="openAdminNoteModal({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 transition-colors duration-200">
                                                    <i class="fas fa-sticky-note ml-1"></i>
                                                    ملاحظة
                                                </button>
                                                @if($booking->status === 'pending')
                                                    <button onclick="confirmBooking({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200">
                                                        <i class="fas fa-check ml-1"></i>
                                                        تأكيد
                                                    </button>
                                                    <button onclick="cancelBooking({{ $booking->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200">
                                                        <i class="fas fa-times ml-1"></i>
                                                        إلغاء
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
                            عرض {{ $bookings->firstItem() ?? 0 }} إلى {{ $bookings->lastItem() ?? 0 }} من أصل {{ $bookings->total() }} نتيجة
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
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">لا توجد نتائج</h3>
                            <p class="text-gray-500 mb-6 max-w-md mx-auto">لم يتم العثور على حجوزات تطابق المعايير المحددة. جرب تعديل الفلاتر أو البحث بكلمات مختلفة.</p>
                            <div class="flex justify-center space-x-4 space-x-reverse">
                                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-times ml-2"></i>
                                    مسح الفلاتر
                                </a>
                                <button onclick="document.getElementById('bookingFiltersForm').reset(); document.getElementById('bookingFiltersForm').submit();" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-redo ml-2"></i>
                                    إعادة تعيين
                                </button>
                            </div>
                        @else
                            <div class="mx-auto w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">لا توجد حجوزات</h3>
                            <p class="text-gray-500 mb-6">لم يتم إنشاء أي حجوزات بعد. ابدأ بإضافة حجز جديد أو انتظر حتى يقوم المستخدمون بالحجز.</p>
                            <a href="{{ route('admin.bookings.manual') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-plus ml-2"></i>
                                إضافة حجز جديد
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
                    ملاحظة الإدارة
                </h3>
                <p class="text-sm text-gray-500 mt-1">تُحفظ هذه الملاحظة للاستخدام الداخلي ولا تظهر للمستخدم.</p>
            </div>
            <button id="adminNoteClose" type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-5 space-y-5">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-700">
                <div class="flex flex-col gap-1">
                    <span><i class="fas fa-user text-blue-500 ml-1"></i>المستخدم: <span id="adminNoteUser" class="font-semibold text-gray-900"></span></span>
                    <span><i class="fas fa-graduation-cap text-emerald-500 ml-1"></i>الورشة: <span id="adminNoteWorkshop" class="font-semibold text-gray-900"></span></span>
                </div>
            </div>
            <div>
                <label for="adminNoteTextarea" class="block text-sm font-medium text-gray-700 mb-2">اكتب الملاحظات الداخلية</label>
                <textarea id="adminNoteTextarea" rows="5" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-purple-500 focus:border-purple-500" maxlength="2000" placeholder="مثال: تم التواصل مع العميل بخصوص الدفع..."></textarea>
                <div class="mt-2 flex items-center justify-between">
                    <span id="adminNoteCharCounter" class="text-xs text-gray-400">0/2000</span>
                    <div id="adminNoteError" data-default-text="تعذر حفظ الملاحظة، حاول مرة أخرى." class="hidden text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>تعذر حفظ الملاحظة، حاول مرة أخرى.</span>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="adminNoteBookingId">
        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" id="adminNoteCancel" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-times ml-1"></i>
                إغلاق
            </button>
            <button type="button" id="adminNoteSaveButton" class="inline-flex items-center px-5 py-2 rounded-md border border-transparent text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <i class="fas fa-save ml-1"></i>
                حفظ الملاحظة
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
                تأكيد العملية
            </h3>
            
            <!-- Message -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">
                    هل أنت متأكد من تأكيد هذا الحجز؟
                </p>
            </div>
            
            <!-- Buttons -->
            <div class="items-center px-4 py-3">
                <div class="flex justify-center space-x-4 space-x-reverse">
                    <button id="modalCancel" class="modal-button px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                        <i class="fas fa-times ml-2"></i>
                        إلغاء
                    </button>
                    <button id="modalConfirm" class="modal-button px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                        <i class="fas fa-check ml-2"></i>
                        تأكيد
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
                تنبيه
            </h3>
            
            <!-- Message -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="alertMessage">
                    حدث خطأ أثناء تنفيذ العملية
                </p>
            </div>
            
            <!-- Button -->
            <div class="items-center px-4 py-3">
                <div class="flex justify-center">
                    <button id="alertOk" class="modal-button px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-check ml-2"></i>
                        موافق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// وظائف إضافية
function refreshBookings() {
    location.reload();
}

function exportBookings() {
    // إضافة معاملات التصدير
    const url = new URL(window.location);
    url.searchParams.set('export', 'excel');
    window.open(url.toString(), '_blank');
}

function printBookings() {
    window.print();
}

// تحسين وظائف الحجز
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
        userSpan.textContent = row.dataset.userName || 'مستخدم';
    }

    if (workshopSpan) {
        workshopSpan.textContent = row.dataset.workshopTitle || 'ورشة';
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
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i>جاري الحفظ...';

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

            const message = data?.message || 'تم حفظ ملاحظة الإدارة بنجاح';
            showAlertModal(message);
            closeAdminNoteModal();
        })
        .catch((error) => {
            if (errorEl) {
                const defaultMessage = errorEl.getAttribute('data-default-text') || 'تعذر حفظ الملاحظة، حاول مرة أخرى.';
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
        'تأكيد الحجز',
        'هل أنت متأكد من تأكيد هذا الحجز؟',
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
                showAlertModal('حدث خطأ أثناء تأكيد الحجز');
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
                    إلغاء الحجز
                </h3>
                
                <!-- Message -->
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        يرجى إدخال سبب الإلغاء:
                    </p>
                    <textarea id="cancellationReason" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                        rows="3" 
                        placeholder="أدخل سبب الإلغاء هنا..."
                        required></textarea>
                    <div id="cancellationError" class="hidden mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle ml-1"></i>
                        يرجى إدخال سبب الإلغاء
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="items-center px-4 py-3">
                    <div class="flex justify-center space-x-4 space-x-reverse">
                        <button id="cancelCancel" class="modal-button px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                            <i class="fas fa-times ml-2"></i>
                            إلغاء
                        </button>
                        <button id="cancelConfirm" class="modal-button px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-check ml-2"></i>
                            تأكيد الإلغاء
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
            showAlertModal('حدث خطأ أثناء إلغاء الحجز');
            button.innerHTML = originalContent;
            button.disabled = false;
        });
        
        document.body.removeChild(modal);
    });
}

// تحسين وظائف الفلترة
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


    // التحقق من صحة التواريخ الأساسية
    function validateDates() {
        if (!dateFromInput || !dateToInput) {
            return true;
        }

        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;

        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            showAlertModal('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
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
                advancedFiltersLabel.textContent = isHidden ? 'عرض الفلاتر المتقدمة' : 'إخفاء الفلاتر المتقدمة';
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

    // التحقق من صحة تواريخ الورشة
    function validateWorkshopDates() {
        if (!workshopDateFromInput || !workshopDateToInput) {
            return true;
        }

        const dateFrom = workshopDateFromInput.value;
        const dateTo = workshopDateToInput.value;

        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            showAlertModal('تاريخ بداية الورشة يجب أن يكون قبل تاريخ نهاية الورشة');
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

    // البحث مع تأخير
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

    // الفلاتر السريعة للحالة
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









