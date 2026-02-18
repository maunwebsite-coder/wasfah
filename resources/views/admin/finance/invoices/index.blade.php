@extends('layouts.app')

@section('title', 'Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ø§Ù„Ù…Ø§Ù„ÙŠØ©')

@php
    $defaultCurrency = config('finance.default_currency', 'JOD');
    $statusFilters = [
        '' => 'ÙƒÙ„ Ø§Ù„Ø­Ø§Ù„Ø§Øª',
        \App\Models\FinanceInvoice::STATUS_DRAFT => 'Ù…Ø³ÙˆØ¯Ø©',
        \App\Models\FinanceInvoice::STATUS_ISSUED => 'ØµØ§Ø¯Ø±Ø©',
        \App\Models\FinanceInvoice::STATUS_PAID => 'Ù…Ø¯ÙÙˆØ¹Ø©',
        \App\Models\FinanceInvoice::STATUS_VOID => 'Ù…Ù„ØºØ§Ø©',
    ];
@endphp

@section('content')
<div class="bg-slate-50 py-10 md:py-14 min-h-screen">
    <div class="container mx-auto px-4 max-w-6xl space-y-8">
        <div class="rounded-3xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">Ø¯ÙØªØ± Ø§Ù„ÙÙˆØ§ØªÙŠØ±</p>
                    <h1 class="mt-1 text-3xl font-black text-slate-900">Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ø§Ù„Ù…Ø§Ù„ÙŠØ©</h1>
                    <p class="mt-2 text-slate-500">
                        Ø§Ø³ØªØ®Ø¯Ù… Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø© Ù„Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø£ÙŠ ÙØ§ØªÙˆØ±Ø© Ø¨Ø³Ø±Ø¹Ø© ÙˆØ¥Ø¬Ø±Ø§Ø¡ Ø§Ù„ØªØ­Ø¯ÙŠØ« Ø§Ù„Ù…Ù†Ø§Ø³Ø¨ Ø¹Ù„ÙŠÙ‡Ø§.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3 text-right">
                    <p class="text-xs font-semibold text-slate-500">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„ÙÙˆØ§ØªÙŠØ±</p>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Ù…Ø³ÙˆØ¯Ø§Øª</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($stats['draft'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-amber-600">ØµØ§Ø¯Ø±Ø©</p>
                <p class="mt-1 text-2xl font-black text-amber-600">{{ number_format($stats['issued'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-emerald-600">Ù…Ø¯ÙÙˆØ¹Ø©</p>
                <p class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($stats['paid'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-rose-600">Ù…Ù„ØºØ§Ø©</p>
                <p class="mt-1 text-2xl font-black text-rose-600">{{ number_format($stats['void'] ?? 0) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-semibold text-slate-600">Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„ÙØ§ØªÙˆØ±Ø©</label>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-0" placeholder="Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø£Ùˆ ÙƒÙˆØ¯ Ø§Ù„Ø­Ø¬Ø²">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-600">Ø§Ù„Ø­Ø§Ù„Ø©</label>
                    <select name="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-0">
                        @foreach($statusFilters as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-600">Ø§Ù„Ø¹Ù…Ù„Ø©</label>
                    <select name="currency" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-0">
                        <option value="">ÙƒÙ„ Ø§Ù„Ø¹Ù…Ù„Ø§Øª</option>
                        @foreach($currencyOptions as $code => $meta)
                            <option value="{{ $code }}" {{ ($filters['currency'] ?? '') === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $meta['label'] ?? $code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end justify-end gap-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                        ØªØ·Ø¨ÙŠÙ‚
                    </button>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:text-indigo-600">
                        Ù…Ø³Ø­
                    </a>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-600">Ù…Ù† ØªØ§Ø±ÙŠØ®</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-0">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-600">Ø¥Ù„Ù‰ ØªØ§Ø±ÙŠØ®</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-0">
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-3 text-right">Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©</th>
                            <th class="px-6 py-3 text-right">Ø§Ù„Ø­Ø¬Ø²</th>
                            <th class="px-6 py-3 text-right">Ø§Ù„Ø­Ø§Ù„Ø©</th>
                            <th class="px-6 py-3 text-right">Ø§Ù„Ù‚ÙŠÙ…Ø©</th>
                            <th class="px-6 py-3 text-right">Ø¢Ø®Ø± ØªØ­Ø¯ÙŠØ«</th>
                            <th class="px-6 py-3 text-right">Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold">{{ optional($invoice->booking->workshop)->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}</p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ optional($invoice->booking->user)->name ?? 'Ù…Ø³ØªØ®Ø¯Ù…' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badgeClasses = [
                                            \App\Models\FinanceInvoice::STATUS_DRAFT => 'bg-slate-100 text-slate-700',
                                            \App\Models\FinanceInvoice::STATUS_ISSUED => 'bg-amber-100 text-amber-700',
                                            \App\Models\FinanceInvoice::STATUS_PAID => 'bg-emerald-100 text-emerald-700',
                                            \App\Models\FinanceInvoice::STATUS_VOID => 'bg-rose-100 text-rose-700',
                                        ];
                                        $badgeLabels = [
                                            \App\Models\FinanceInvoice::STATUS_DRAFT => 'Ù…Ø³ÙˆØ¯Ø©',
                                            \App\Models\FinanceInvoice::STATUS_ISSUED => 'ØµØ§Ø¯Ø±Ø©',
                                            \App\Models\FinanceInvoice::STATUS_PAID => 'Ù…Ø¯ÙÙˆØ¹Ø©',
                                            \App\Models\FinanceInvoice::STATUS_VOID => 'Ù…Ù„ØºØ§Ø©',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses[$invoice->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $badgeLabels[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ optional($invoice->updated_at)->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                        <i class="fas fa-eye ml-1"></i>
                                        Ø¹Ø±Ø¶
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Ù„Ø§ ØªÙˆØ¬Ø¯ ÙÙˆØ§ØªÙŠØ± Ù…Ø·Ø§Ø¨Ù‚Ø© Ù„Ø®ÙŠØ§Ø±Ø§Øª Ø§Ù„Ø¨Ø­Ø« Ø§Ù„Ø­Ø§Ù„ÙŠØ©.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection



