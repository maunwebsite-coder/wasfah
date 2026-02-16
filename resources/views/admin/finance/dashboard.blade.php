@extends('layouts.app')

@section('title', 'Ø§Ù„Ù†Ø¸Ø§Ù… Ø§Ù„Ù…Ø§Ù„ÙŠ - Ù„ÙˆØ­Ø© Ø§Ù„ØªØ­ÙƒÙ…')

@php
    $defaultCurrency = config('finance.default_currency', 'JOD');
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
                        Ø§Ù„Ù†Ø¸Ø§Ù… Ø§Ù„Ù…Ø§Ù„ÙŠ
                    </span>
                    <h1 class="mt-4 text-3xl font-black text-slate-900 md:text-4xl">
                        ÙƒÙ„ Ù…Ø§ ÙŠØ®Øµ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª ÙˆØ§Ù„ÙÙˆØ§ØªÙŠØ± ÙÙŠ Ù…ÙƒØ§Ù† ÙˆØ§Ø­Ø¯
                    </h1>
                    <p class="mt-3 text-slate-600 leading-relaxed max-w-2xl">
                        ØªØ§Ø¨ÙØ¹ Ø§Ù„Ø¹Ù…Ù„Ø§Øª Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…Ø©ØŒ ØªØ£ÙƒØ¯ Ù…Ù† Ø§Ù„ØªÙˆØ²ÙŠØ¹ Ø§Ù„Ø¹Ø§Ø¯Ù„ Ù„Ù„Ø­ØµØµØŒ ÙˆØ±Ø§Ù‚ÙØ¨ Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ø§Ù„Ø¬Ø§Ù‡Ø²Ø© Ø£Ùˆ Ø§Ù„Ù…ØªØ£Ø®Ø±Ø© Ø¨Ø®Ø·ÙˆØ§Øª Ø¨Ø³ÙŠØ·Ø©.
                    </p>
                </div>
                <div class="w-full rounded-2xl border border-white/70 bg-white/80 p-5 text-right shadow-lg md:w-80">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Ø§Ù„Ø¹Ù…Ù„Ø© Ø§Ù„Ù…Ø®ØªØ§Ø±Ø©</div>
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
                                ØªØ­Ø¯ÙŠØ«
                            </button>
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Ø§Ù„ÙØªØ±Ø© Ø§Ù„Ø­Ø§Ù„ÙŠØ©: {{ $periodSummary['range'] }} ({{ $periodDays }} ÙŠÙˆÙ…)
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                <p class="text-xs font-semibold text-slate-500">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª Ø§Ù„Ù…Ø¤ÙƒØ¯Ø©</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($overview['paid_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-slate-500 mt-1">Ø¨Ø¹Ø¯ Ø§Ù„ØªØ­ÙˆÙŠÙ„ Ø¥Ù„Ù‰ Ø§Ù„Ø¹Ù…Ù„Ø© Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-amber-100">
                <p class="text-xs font-semibold text-amber-600">Ù…Ø¯ÙÙˆØ¹Ø§Øª Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„Ù…Ø¹Ø§Ù„Ø¬Ø©</p>
                <p class="mt-2 text-3xl font-black text-amber-600">{{ number_format($overview['pending_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-amber-600 mt-1">ØªØ­ØªØ§Ø¬ Ù‚Ø¨ÙˆÙ„ Ø£Ùˆ ØªÙˆØ«ÙŠÙ‚ Ø¨Ø¹Ø¯ Ø§Ù„Ø¯ÙØ¹</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-rose-100">
                <p class="text-xs font-semibold text-rose-600">Ù…Ø¨Ø§Ù„Øº Ù…Ø³ØªØ±Ø¯Ø©</p>
                <p class="mt-2 text-3xl font-black text-rose-600">{{ number_format($overview['refunded_usd'], 2) }} {{ $defaultCurrency }}</p>
                <p class="text-xs text-rose-600 mt-1">ÙŠÙÙ†ØµØ­ Ø¨Ù…Ø±Ø§Ø¬Ø¹ØªÙ‡Ø§ Ø£Ø³Ø¨ÙˆØ¹ÙŠØ§Ù‹</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ø§Ù„ØªØ¯ÙÙ‚ Ø­Ø³Ø¨ Ø§Ù„Ø¹Ù…Ù„Ø©</h2>
                        <p class="text-sm text-slate-500 mt-1">ÙƒÙŠÙ ØªÙˆØ²Ø¹Øª Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª Ø¹Ø¨Ø± Ø§Ù„Ø¹Ù…Ù„Ø§Øª.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                        <i class="fas fa-globe"></i>
                        {{ $currencyBreakdown->count() }} Ø¹Ù…Ù„Ø§Øª
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
                                    {{ number_format($item['total_bookings']) }} Ø­Ø¬ÙˆØ²Ø§Øª Â· â‰ˆ {{ number_format($item['total_amount_usd'], 2) }} {{ $defaultCurrency }}
                                </p>
                            </div>
                            <p class="text-lg font-bold text-slate-900">
                                {{ number_format($item['total_amount'], 2) }}
                                <span class="text-sm text-slate-500">{{ $meta['symbol'] ?? $item['currency'] }}</span>
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Ù„Ø§ ØªÙˆØ¬Ø¯ Ù…Ø¯ÙÙˆØ¹Ø§Øª Ù…Ø¤ÙƒØ¯Ø© Ø­ØªÙ‰ Ø§Ù„Ø¢Ù†.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ø­Ø§Ù„Ø© Ø§Ù„ÙÙˆØ§ØªÙŠØ±</h2>
                        <p class="text-sm text-slate-500 mt-1">Ø±Ø§Ù‚ÙØ¨ ØªÙ‚Ø¯Ù… Ø¥ØµØ¯Ø§Ø± Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ù„Ù…Ø±Ø§Ø¬Ø¹Ø© Ø£Ø³Ø±Ø¹.</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Ø¹Ø±Ø¶ Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„ÙÙˆØ§ØªÙŠØ±
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @php
                        $invoiceMeta = [
                            'draft' => ['label' => 'Ù…Ø³ÙˆØ¯Ø§Øª', 'color' => 'text-slate-600', 'bg' => 'bg-slate-50'],
                            'issued' => ['label' => 'ØµØ§Ø¯Ø±Ø©', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                            'paid' => ['label' => 'Ù…Ø¯ÙÙˆØ¹Ø©', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                            'void' => ['label' => 'Ù…Ù„ØºØ§Ø©', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
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
                        <p class="text-xs font-semibold text-slate-500">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„ÙÙˆØ§ØªÙŠØ±</p>
                        <p class="text-lg font-black text-slate-900">{{ number_format($invoiceStats['total'] ?? 0) }}</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-700">
                        Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„ÙÙˆØ§ØªÙŠØ±
                        <i class="fas fa-arrow-left text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ø®Ù„Ø§ØµØ© Ø§Ù„ÙØªØ±Ø© ({{ $periodDays }} ÙŠÙˆÙ…)</h2>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $periodSummary['bookings'] }} Ø­Ø¬ÙˆØ²Ø§Øª Ù…Ø¯ÙÙˆØ¹Ø© Â· {{ number_format($periodSummary['amount'], 2) }} {{ $selectedCurrency }} (â‰ˆ {{ number_format($periodSummary['amount_usd'], 2) }} {{ $defaultCurrency }})
                        </p>
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                        <select name="period" class="rounded-lg border border-slate-200 bg-transparent px-3 py-1 text-sm focus:border-indigo-400 focus:outline-none focus:ring-0">
                            @foreach([7 => 'Ø¢Ø®Ø± 7 Ø£ÙŠØ§Ù…', 14 => 'Ø¢Ø®Ø± 14 ÙŠÙˆÙ…', 30 => 'Ø¢Ø®Ø± 30 ÙŠÙˆÙ…', 60 => 'Ø¢Ø®Ø± 60 ÙŠÙˆÙ…'] as $days => $label)
                                <option value="{{ $days }}" {{ $days == $periodDays ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600">
                            ØªØ­Ø¯ÙŠØ«
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
                        <p class="text-sm text-slate-500 col-span-2">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø¨ÙŠØ§Ù†Ø§Øª Ù…ØªØ§Ø­Ø© Ù„Ù„ÙØªØ±Ø© Ø§Ù„Ù…Ø®ØªØ§Ø±Ø©.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Ø£Ø­Ø¯Ø« ØªÙˆØ²ÙŠØ¹Ø§Øª Ø§Ù„Ø£Ø±Ø¨Ø§Ø­</h2>
                <p class="text-sm text-slate-500 mt-1">Ø¹Ø±Ø¶ Ø³Ø±ÙŠØ¹ Ù„Ø£Ø­Ø¯Ø« Ø§Ù„Ù…Ø´Ø§Ø±ÙƒØ§Øª Ø§Ù„Ù…Ø§Ù„ÙŠØ©.</p>
                <div class="mt-4 space-y-3">
                    @forelse($recentShares as $share)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ optional($share->booking->workshop)->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ strtoupper($share->recipient_type) }} Â· {{ number_format($share->amount, 2) }} {{ $share->currency }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ optional($share->distributed_at)->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Ù„Ø§ ØªÙˆØ¬Ø¯ ØªÙˆØ²ÙŠØ¹Ø§Øª Ø­Ø¯ÙŠØ«Ø©.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">ÙÙˆØ§ØªÙŠØ± Ø­Ø¯ÙŠØ«Ø©</h2>
                        <p class="text-sm text-slate-500 mt-1">Ø¢Ø®Ø± Ø§Ù„ÙÙˆØ§ØªÙŠØ± Ø§Ù„ØªÙŠ ØªÙ… ØªØ­Ø¯ÙŠØ«Ù‡Ø§.</p>
                    </div>
                    <a href="{{ route('admin.finance.invoices.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Ø§Ù„ÙƒÙ„
                    </a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($recentInvoices as $invoice)
                        <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="block rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 hover:border-indigo-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ optional($invoice->booking->workshop)->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}
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
                        <p class="text-sm text-slate-500">Ù„Ø§ ØªÙˆØ¬Ø¯ ÙÙˆØ§ØªÙŠØ± Ù…ÙØ³Ø¬Ù„Ø©.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ø­Ø¬ÙˆØ²Ø§Øª ØªØ­ØªØ§Ø¬ ØªÙˆØ²ÙŠØ¹</h2>
                        <p class="text-sm text-slate-500 mt-1">Ù…Ø¯ÙÙˆØ¹Ø© Ù„ÙƒÙ† Ù„Ù… ÙŠØªÙ… ØªÙˆØ²ÙŠØ¹ Ø£Ø±Ø¨Ø§Ø­Ù‡Ø§.</p>
                    </div>
                    <a href="{{ route('admin.bookings.index', ['financial_status' => \App\Models\WorkshopBooking::FINANCIAL_STATUS_PENDING]) }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">
                        Ø¹Ø±Ø¶ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø©
                    </a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($pendingDistributions as $booking)
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ optional($booking->workshop)->title ?? 'ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…Ø­Ø¯Ø¯Ø©' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ optional($booking->user)->name }} Â· {{ number_format($booking->payment_amount, 2) }} {{ $booking->payment_currency }}
                            </p>
                            <p class="text-[11px] text-amber-600 mt-1 flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i>
                                Ø¨Ø§Ù†ØªØ¸Ø§Ø± Ø§Ù„ØªÙˆØ²ÙŠØ¹ Ù…Ù†Ø° {{ optional($booking->updated_at)->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø­Ø¬ÙˆØ²Ø§Øª Ø¨Ø­Ø§Ø¬Ø© Ø¥Ù„Ù‰ ØªÙˆØ²ÙŠØ¹ ÙÙŠ Ø§Ù„ÙˆÙ‚Øª Ø§Ù„Ø­Ø§Ù„ÙŠ.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



