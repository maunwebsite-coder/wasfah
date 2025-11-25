@extends('layouts.app')

@section('title', 'فاتورة ' . $invoice->invoice_number)

@php
    $defaultCurrency = config('finance.default_currency', 'USD');
    $statusMeta = [
        \App\Models\FinanceInvoice::STATUS_DRAFT => ['label' => 'مسودة', 'class' => 'bg-slate-100 text-slate-700'],
        \App\Models\FinanceInvoice::STATUS_ISSUED => ['label' => 'صادرة', 'class' => 'bg-amber-100 text-amber-700'],
        \App\Models\FinanceInvoice::STATUS_PAID => ['label' => 'مدفوعة', 'class' => 'bg-emerald-100 text-emerald-700'],
        \App\Models\FinanceInvoice::STATUS_VOID => ['label' => 'ملغاة', 'class' => 'bg-rose-100 text-rose-700'],
    ];
@endphp

@section('content')
<div class="bg-slate-50 py-10 md:py-14 min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl space-y-8">
        <div class="rounded-3xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">فاتورة #{{ $invoice->invoice_number }}</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">تفاصيل الفاتورة</h1>
                    <p class="mt-2 text-slate-500">
                        مرتبطة بحجز {{ optional($invoice->booking->workshop)->title ?? 'غير محدد' }} لمستخدم {{ optional($invoice->booking->user)->name ?? 'غير معروف' }}.
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold {{ $statusMeta[$invoice->status]['class'] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $statusMeta[$invoice->status]['label'] ?? $invoice->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-3">بيانات الحجز</h2>
                <p class="text-sm text-slate-600">
                    الورشة: {{ optional($invoice->booking->workshop)->title ?? 'غير محددة' }}
                </p>
                <p class="text-sm text-slate-600 mt-1">
                    العميل: {{ optional($invoice->booking->user)->name ?? 'غير معروف' }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    كود الحجز العام: {{ $invoice->booking->public_code ?? 'غير متوفر' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-3">القيم المالية</h2>
                <ul class="text-sm text-slate-600 space-y-2">
                    <li>المجموع الفرعي: {{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</li>
                    <li>الضرائب: {{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</li>
                    <li class="text-base font-bold text-slate-900">
                        الإجمالي: {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                    </li>
                    <li class="text-xs text-slate-400">
                        ≈ {{ number_format(($invoice->booking->payment_amount_usd ?? 0), 2) }} {{ $defaultCurrency }} (للمقارنة)
                    </li>
                </ul>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-3">عناصر الفاتورة</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-4 py-2 text-right">الوصف</th>
                            <th class="px-4 py-2 text-right">الكمية</th>
                            <th class="px-4 py-2 text-right">سعر الوحدة</th>
                            <th class="px-4 py-2 text-right">المجموع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @foreach(($invoice->line_items ?? []) as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item['description'] ?? 'عنصر' }}</td>
                                <td class="px-4 py-3">{{ $item['quantity'] ?? 1 }}</td>
                                <td class="px-4 py-3">{{ number_format($item['unit_price'] ?? 0, 2) }} {{ $invoice->currency }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($item['total'] ?? 0, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-3">إجراءات سريعة</h2>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.finance.invoices.issue', $invoice) }}" class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-indigo-700">إصدار الفاتورة</p>
                    <p class="text-xs text-indigo-600 mt-1">تنتقل من مسودة إلى صادرة.</p>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                        إصدار الآن
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.finance.invoices.mark-paid', $invoice) }}" class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-emerald-700">تعيين كمدفوعة</p>
                    <p class="text-xs text-emerald-600 mt-1">تأكيد استلام المبلغ.</p>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">
                        تم الدفع
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.finance.invoices.void', $invoice) }}" class="rounded-xl border border-rose-100 bg-rose-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-rose-700">إلغاء الفاتورة</p>
                    <p class="text-xs text-rose-600 mt-1">احتفظ بسبب الإلغاء في السجل.</p>
                    <textarea name="reason" class="mt-2 w-full rounded-lg border border-rose-200 px-3 py-2 text-xs focus:border-rose-400 focus:outline-none focus:ring-0" placeholder="سبب الإلغاء (اختياري)"></textarea>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-rose-700">
                        إلغاء
                    </button>
                </form>
                @if($invoice->booking)
                    <form method="POST" action="{{ route('admin.finance.bookings.invoice.regenerate', $invoice->booking) }}" class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-4">
                        @csrf
                        <p class="text-sm font-semibold text-slate-700">مزامنة البيانات</p>
                        <p class="text-xs text-slate-500 mt-1">تحديث الفاتورة بناءً على بيانات الحجز.</p>
                        <button type="submit" class="mt-3 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600">
                            إعادة التوليد
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


