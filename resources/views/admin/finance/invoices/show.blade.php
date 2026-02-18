@extends('layouts.app')

@section('title', 'ÙØ§ØªÙˆØ±Ø© ' . $invoice->invoice_number)

@php
    $defaultCurrency = config('finance.default_currency', 'JOD');
    $statusMeta = [
        \App\Models\FinanceInvoice::STATUS_DRAFT => ['label' => 'Ù…Ø³ÙˆØ¯Ø©', 'class' => 'bg-slate-100 text-slate-700'],
        \App\Models\FinanceInvoice::STATUS_ISSUED => ['label' => 'ØµØ§Ø¯Ø±Ø©', 'class' => 'bg-amber-100 text-amber-700'],
        \App\Models\FinanceInvoice::STATUS_PAID => ['label' => 'Ù…Ø¯ÙÙˆØ¹Ø©', 'class' => 'bg-emerald-100 text-emerald-700'],
        \App\Models\FinanceInvoice::STATUS_VOID => ['label' => 'Ù…Ù„ØºØ§Ø©', 'class' => 'bg-rose-100 text-rose-700'],
    ];
@endphp

@section('content')
<div class="bg-slate-50 py-10 md:py-14 min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl space-y-8">
        <div class="rounded-3xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">ÙØ§ØªÙˆØ±Ø© #{{ $invoice->invoice_number }}</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">ØªÙØ§ØµÙŠÙ„ Ø§Ù„ÙØ§ØªÙˆØ±Ø©</h1>
                    <p class="mt-2 text-slate-500">
                        Ù…Ø±ØªØ¨Ø·Ø© Ø¨Ø­Ø¬Ø² {{ optional($invoice->booking->workshop)->title ?? 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯' }} Ù„Ù…Ø³ØªØ®Ø¯Ù… {{ optional($invoice->booking->user)->name ?? 'ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ' }}.
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold {{ $statusMeta[$invoice->status]['class'] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $statusMeta[$invoice->status]['label'] ?? $invoice->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-3">Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø­Ø¬Ø²</h2>
                <p class="text-sm text-slate-600">
                    Ø§Ù„ÙˆØ±Ø´Ø©: {{ optional($invoice->booking->workshop)->title ?? 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}
                </p>
                <p class="text-sm text-slate-600 mt-1">
                    Ø§Ù„Ø¹Ù…ÙŠÙ„: {{ optional($invoice->booking->user)->name ?? 'ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ' }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    ÙƒÙˆØ¯ Ø§Ù„Ø­Ø¬Ø² Ø§Ù„Ø¹Ø§Ù…: {{ $invoice->booking->public_code ?? 'ØºÙŠØ± Ù…ØªÙˆÙØ±' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-3">Ø§Ù„Ù‚ÙŠÙ… Ø§Ù„Ù…Ø§Ù„ÙŠØ©</h2>
                <ul class="text-sm text-slate-600 space-y-2">
                    <li>Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹ Ø§Ù„ÙØ±Ø¹ÙŠ: {{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</li>
                    <li>Ø§Ù„Ø¶Ø±Ø§Ø¦Ø¨: {{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</li>
                    <li class="text-base font-bold text-slate-900">
                        Ø§Ù„Ø¥Ø¬Ù…Ø§Ù„ÙŠ: {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                    </li>
                    <li class="text-xs text-slate-400">
                        â‰ˆ {{ number_format(($invoice->booking->payment_amount_usd ?? 0), 2) }} {{ $defaultCurrency }} (Ù„Ù„Ù…Ù‚Ø§Ø±Ù†Ø©)
                    </li>
                </ul>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Ø¹Ù†Ø§ØµØ± Ø§Ù„ÙØ§ØªÙˆØ±Ø©</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-4 py-2 text-right">Ø§Ù„ÙˆØµÙ</th>
                            <th class="px-4 py-2 text-right">Ø§Ù„ÙƒÙ…ÙŠØ©</th>
                            <th class="px-4 py-2 text-right">Ø³Ø¹Ø± Ø§Ù„ÙˆØ­Ø¯Ø©</th>
                            <th class="px-4 py-2 text-right">Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @foreach(($invoice->line_items ?? []) as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item['description'] ?? 'Ø¹Ù†ØµØ±' }}</td>
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
            <h2 class="text-lg font-bold text-slate-900 mb-3">Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª Ø³Ø±ÙŠØ¹Ø©</h2>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.finance.invoices.issue', $invoice) }}" class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-indigo-700">Ø¥ØµØ¯Ø§Ø± Ø§Ù„ÙØ§ØªÙˆØ±Ø©</p>
                    <p class="text-xs text-indigo-600 mt-1">ØªÙ†ØªÙ‚Ù„ Ù…Ù† Ù…Ø³ÙˆØ¯Ø© Ø¥Ù„Ù‰ ØµØ§Ø¯Ø±Ø©.</p>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                        Ø¥ØµØ¯Ø§Ø± Ø§Ù„Ø¢Ù†
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.finance.invoices.mark-paid', $invoice) }}" class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-emerald-700">ØªØ¹ÙŠÙŠÙ† ÙƒÙ…Ø¯ÙÙˆØ¹Ø©</p>
                    <p class="text-xs text-emerald-600 mt-1">ØªØ£ÙƒÙŠØ¯ Ø§Ø³ØªÙ„Ø§Ù… Ø§Ù„Ù…Ø¨Ù„Øº.</p>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">
                        ØªÙ… Ø§Ù„Ø¯ÙØ¹
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.finance.invoices.void', $invoice) }}" class="rounded-xl border border-rose-100 bg-rose-50/60 px-4 py-4">
                    @csrf
                    <p class="text-sm font-semibold text-rose-700">Ø¥Ù„ØºØ§Ø¡ Ø§Ù„ÙØ§ØªÙˆØ±Ø©</p>
                    <p class="text-xs text-rose-600 mt-1">Ø§Ø­ØªÙØ¸ Ø¨Ø³Ø¨Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡ ÙÙŠ Ø§Ù„Ø³Ø¬Ù„.</p>
                    <textarea name="reason" class="mt-2 w-full rounded-lg border border-rose-200 px-3 py-2 text-xs focus:border-rose-400 focus:outline-none focus:ring-0" placeholder="Ø³Ø¨Ø¨ Ø§Ù„Ø¥Ù„ØºØ§Ø¡ (Ø§Ø®ØªÙŠØ§Ø±ÙŠ)"></textarea>
                    <button type="submit" class="mt-3 inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-rose-700">
                        Ø¥Ù„ØºØ§Ø¡
                    </button>
                </form>
                @if($invoice->booking)
                    <form method="POST" action="{{ route('admin.finance.bookings.invoice.regenerate', $invoice->booking) }}" class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-4">
                        @csrf
                        <p class="text-sm font-semibold text-slate-700">Ù…Ø²Ø§Ù…Ù†Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª</p>
                        <p class="text-xs text-slate-500 mt-1">ØªØ­Ø¯ÙŠØ« Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø­Ø¬Ø².</p>
                        <button type="submit" class="mt-3 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600">
                            Ø¥Ø¹Ø§Ø¯Ø© Ø§Ù„ØªÙˆÙ„ÙŠØ¯
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection



