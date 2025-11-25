@extends('layouts.app')

@section('title', 'النظام المالي - لوحة التحكم')

@php
    $defaultCurrency = config('finance.default_currency', 'USD');
    $currencyMeta = $currencyOptions[$selectedCurrency] ?? ['label' => $selectedCurrency];
@endphp

@section('content')
<div class="bg-slate-50 py-10 md:py-14 min-h-screen">
    <div class="container mx-auto px-4 max-w-6xl space-y-8">
        <div class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-white via-indigo-50 to-purple-50 p-8 shadow-xl">
            <div class="absolute -top-16 -right-16 h-40 w-40 rounded-full bg-indigo-200 opacity-40 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-purple-200 opacity-30 blur-3xl"></div>
            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-indigo-700">
                        <i class="fas fa-file-invoice-dollar"></i>
                        النظام المالي
                    </span>
                    <h1 class="mt-4 text-3xl font-black text-slate-900 md:text-4xl">
                        كل ما يخص المدفوعات والفواتير في مكان واحد
                    </h1>
                    <p class="mt-3 text-slate-600 leading-relaxed max-w-2xl">
                        تابِع العملات المستخدمة، تأكد من التوزيع العادل للحصص، وراقِب الفواتير الجاهزة أو المتأخرة بخطوات بسيطة.
                    </p>
                </div>
                <div class="w-full rounded-2xl border border-white/70 bg-white/80 p-5 text-right shadow-lg md:w-80">
                    <div class="text-xs font-semibold text-slate-400 uppercase">العملة المختارة</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-black text-slate-900">{{ $selectedCurrency }}</p>
                            <p class="text-sm text-slate-500">{{ $currencyMeta['label'] ?? $selectedCurrency }}</p>
                        </div>
                        <form method="GET" class="flex items-center gap-2">
                            <select name="currency" class="rounded-lg border border-slate-200 bg-transparent px-3 py-1 text-sm focus:border-indigo-400 focus:outline-none focus:ring-0">
                                @foreach($currencyOptions as $code => $meta)
                                    <option value="{{ $code }}" {{ $code === $selectedCurrency ? 'selected' : '' }}>
                                        {{ $code }} - {{ $meta['label'] ?? $code }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                                تحديث
                            </button>
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        الفترة الحالية: {{ $periodSummary['range'] }} ({{ $periodDays }} يوم)
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                <p class="text-xs font-semibold text-slate-500">إجمالي المدفوعات المؤكدة</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($overview['paid_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-slate-500 mt-1">بعد التحويل إلى العملة الأساسية</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-amber-100">
                <p class="text-xs font-semibold text-amber-600">مدفوعات بانتظار المعالجة</p>
                <p class="mt-2 text-3xl font-black text-amber-600">{{ number_format($overview['pending_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-amber-600 mt-1">تحتاج قبول أو توثيق بعد الدفع</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-rose-100">
                <p class="text-xs font-semibold text-rose-600">مبالغ مستردة</p>
                <p class="mt-2 text-3xl font-black text-rose-600">{{ number_format($overview['refunded_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-rose-600 mt-1">يُنصح بمراجعتها أسبوعياً</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">التدفق حسب العملة</h2>
                        <p class="text-sm text-slate-500 mt-1">كيف توزعت المدفوعات عبر العملات.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                        <i class="fas fa-globe"></i>
                        {{ $currencyBreakdown->count() }} عملات
                    </span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($currencyBreakdown as $item)
                        @php
                            $meta = $currencyOptions[$item['currency']] ?? ['label' => $item['currency'], 'symbol' => $item['currency']];
                        @endphp
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $meta['label'] ?? $item['currency'] }}
                                    <span class="text-xs text-slate-500">({{ $item['currency'] }})</span>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ number_format($item['total_bookings']) }} حجوزات · ≈ {{ number_format($item['total_amount_usd'], 2) }} {{ $defaultCurrency }}
                                </p>
                            </div>
                            <p class="text-lg font-bold text-slate-900">
                                {{ number_format($item['total_amount'], 2) }}
                                <span class="text-sm text-slate-500">{{ $meta['symbol'] ?? $item['currency'] }}</span>
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد مدفوعات مؤكدة حتى الآن.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">حالة الفواتير</h2>
                        <p class="text-sm text-slate-500 mt-1">راقِب تقدم إصدار الفواتير لمراجعة أسرع.</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        عرض قائمة الفواتير
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @php
                        $invoiceMeta = [
                            'draft' => ['label' => 'مسودات', 'color' => 'text-slate-600', 'bg' => 'bg-slate-50'],
                            'issued' => ['label' => 'صادرة', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                            'paid' => ['label' => 'مدفوعة', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                            'void' => ['label' => 'ملغاة', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
                        ];
                    @endphp
                    @foreach($invoiceMeta as $status => $meta)
                        <div class="rounded-xl border border-slate-100 px-4 py-3 {{ $meta['bg'] }}">
                            <p class="text-xs font-semibold {{ $meta['color'] }}">{{ $meta['label'] }}</p>
                            <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($invoiceStats[$status] ?? 0) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">إجمالي الفواتير</p>
                        <p class="text-lg font-black text-slate-900">{{ number_format($invoiceStats['total'] ?? 0) }}</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                        إدارة الفواتير
                        <i class="fas fa-arrow-left text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">خلاصة الفترة ({{ $periodDays }} يوم)</h2>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $periodSummary['bookings'] }} حجوزات مدفوعة · {{ number_format($periodSummary['amount'], 2) }} {{ $selectedCurrency }} (≈ {{ number_format($periodSummary['amount_usd'], 2) }} {{ $defaultCurrency }})
                        </p>
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                        <select name="period" class="rounded-lg border border-slate-200 bg-transparent px-3 py-1 text-sm focus:border-indigo-400 focus:outline-none focus:ring-0">
                            @foreach([7 => 'آخر 7 أيام', 14 => 'آخر 14 يوم', 30 => 'آخر 30 يوم', 60 => 'آخر 60 يوم'] as $days => $label)
                                <option value="{{ $days }}" {{ $days == $periodDays ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600">
                            تحديث
                        </button>
                    </form>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2">
                    @forelse($periodSeries as $point)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-600">{{ $point['day'] }}</p>
                            <p class="text-base font-bold text-slate-900">
                                {{ number_format($point['amount'], 2) }}
                                <span class="text-xs text-slate-500">{{ $selectedCurrency }}</span>
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 col-span-2">لا توجد بيانات متاحة للفترة المختارة.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">أحدث توزيعات الأرباح</h2>
                <p class="text-sm text-slate-500 mt-1">عرض سريع لأحدث المشاركات المالية.</p>
                <div class="mt-4 space-y-3">
                    @forelse($recentShares as $share)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ optional($share->booking->workshop)->title ?? 'ورشة غير محددة' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ strtoupper($share->recipient_type) }} · {{ number_format($share->amount, 2) }} {{ $share->currency }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ optional($share->distributed_at)->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد توزيعات حديثة.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">فواتير حديثة</h2>
                        <p class="text-sm text-slate-500 mt-1">آخر الفواتير التي تم تحديثها.</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        الكل
                    </a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($recentInvoices as $invoice)
                        <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="block rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 hover:border-indigo-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ optional($invoice->booking->workshop)->title ?? 'ورشة غير محددة' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                                </p>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ optional($invoice->updated_at)->diffForHumans() }}
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد فواتير مُسجلة.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">حجوزات تحتاج توزيع</h2>
                        <p class="text-sm text-slate-500 mt-1">مدفوعة لكن لم يتم توزيع أرباحها.</p>
                    </div>
                    <a href="{{ route('admin.bookings.index', ['financial_status' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING]) }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">
                        عرض القائمة
                    </a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($pendingDistributions as $booking)
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ optional($booking->workshop)->title ?? 'ورشة غير محددة' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ optional($booking->user)->name }} · {{ number_format($booking->payment_amount, 2) }} {{ $booking->payment_currency }}
                            </p>
                            <p class="text-[11px] text-amber-600 mt-1 flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i>
                                بانتظار التوزيع منذ {{ optional($booking->updated_at)->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد حجوزات بحاجة إلى توزيع في الوقت الحالي.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


