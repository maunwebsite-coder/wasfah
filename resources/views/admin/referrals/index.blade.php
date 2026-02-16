@extends('layouts.app')

@section('title', 'Ø¨Ø±Ù†Ø§Ù…Ø¬ Ø§Ù„Ø¥Ø­Ø§Ù„Ø§Øª - Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©')

@section('content')
    @php
        $defaultReferralCurrency = config('referrals.default_currency', 'JOD');
        $defaultReferralSymbol = data_get(
            config('referrals.currencies', []),
            "{$defaultReferralCurrency}.symbol",
            $defaultReferralCurrency
        );
    @endphp
    <div class="bg-slate-50 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white rounded-3xl border border-orange-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide mb-2">Ø§Ù„Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ù…Ø§Ù„ÙŠØ©</p>
                        <h1 class="text-3xl font-black text-slate-900 mb-3">Ø¨Ø±Ù†Ø§Ù…Ø¬ Ø§Ù„Ø¥Ø­Ø§Ù„Ø§Øª</h1>
                        <p class="text-slate-600 leading-relaxed max-w-2xl">
                            ØªØªØ¨Ø¹ Ø£Ø¯Ø§Ø¡ Ø´Ø±ÙƒØ§Ø¡ Ø§Ù„Ù†Ù…Ùˆ ÙˆØ­Ø¯Ø¯ Ù†Ø³Ø¨ Ø§Ù„Ø¹Ù…ÙˆÙ„Ø§Øª Ø§Ù„Ù…Ù†Ø§Ø³Ø¨Ø©ØŒ Ù…Ø¹ Ù†Ø¸Ø±Ø© ÙƒØ§Ù…Ù„Ø© Ø¹Ù„Ù‰ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø© Ø§Ù„ØªÙŠ ØªØ³ØªØ­Ù‚ Ø§Ù„Ø¯ÙØ¹.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-emerald-600 uppercase">Ø¹Ù…ÙˆÙ„Ø§Øª Ø¬Ø§Ù‡Ø²Ø©</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format($stats['ready_amount'], 2) }} {{ $defaultReferralSymbol }}</p>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-blue-600 uppercase">Ù…Ø¯ÙÙˆØ¹ Ù‡Ø°Ø§ Ø§Ù„Ø´Ù‡Ø±</p>
                            <p class="text-2xl font-black text-blue-700">{{ number_format($stats['paid_amount'], 2) }} {{ $defaultReferralSymbol }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-slate-600 uppercase">Ø¹Ø¯Ø¯ Ø§Ù„Ø´Ø±ÙƒØ§Ø¡</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($stats['partners_count']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Ø´Ø±ÙƒØ§Ø¡ Ø¬Ø¯Ø¯ Ù‡Ø°Ø§ Ø§Ù„Ø´Ù‡Ø±</p>
                            <p class="text-2xl font-black text-orange-600">{{ number_format($stats['new_partners_this_month']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <form method="GET" action="{{ route('admin.referrals.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="flex-1">
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Ø§Ù„Ø¨Ø­Ø« Ø¹Ù† Ø´Ø±ÙŠÙƒ</label>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Ø§Ù„Ø§Ø³Ù…ØŒ Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø£Ùˆ ÙƒÙˆØ¯ Ø§Ù„Ø¥Ø­Ø§Ù„Ø©"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            >
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            <i class="fas fa-search ml-2"></i>
                            Ø¨Ø­Ø«
                        </button>
                    </form>

                    <div class="mt-6 hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ø§Ù„Ø´Ø±ÙŠÙƒ</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ø§Ù„Ø±Ù…Ø²</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ù…Ø³ØªØ®Ø¯Ù…ÙˆÙ†</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ø¬Ø§Ù‡Ø²</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ù…Ø¯ÙÙˆØ¹</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($partners as $partner)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-slate-900">{{ $partner->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $partner->email }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-mono text-slate-700">{{ $partner->referral_code }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ number_format($partner->referred_users_count) }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-emerald-600">
                                            {{ number_format($partner->pending_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-blue-600">
                                            {{ number_format($partner->paid_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                        </td>
                                        <td class="px-4 py-3 text-left">
                                            <a href="{{ route('admin.referrals.show', $partner) }}" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-orange-300 hover:text-orange-600">
                                                Ø¥Ø¯Ø§Ø±Ø©
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                            Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø´Ø±ÙƒØ§Ø¡ Ù…Ø·Ø§Ø¨Ù‚ÙˆÙ† Ù„Ù†ØªÙŠØ¬Ø© Ø§Ù„Ø¨Ø­Ø« Ø§Ù„Ø­Ø§Ù„ÙŠØ©.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 space-y-4 md:hidden">
                        @if ($partners->count())
                            @foreach ($partners as $partner)
                                <div class="rounded-2xl border border-slate-100 bg-white/90 p-4 shadow-sm">
                                    <div class="flex flex-wrap items-start gap-3">
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900">{{ $partner->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $partner->email }}</p>
                                        </div>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-mono text-slate-700">
                                            {{ $partner->referral_code }}
                                        </span>
                                    </div>
                                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600 sm:grid-cols-3">
                                        <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Ù…Ø³ØªØ®Ø¯Ù…ÙˆÙ†</dt>
                                            <dd class="font-semibold text-slate-700">
                                                {{ number_format($partner->referred_users_count) }}
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl bg-emerald-50 px-3 py-2">
                                            <dt class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide">Ø¬Ø§Ù‡Ø²</dt>
                                            <dd class="font-semibold text-emerald-700">
                                                {{ number_format($partner->pending_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl bg-blue-50 px-3 py-2">
                                            <dt class="text-[11px] font-semibold text-blue-600 uppercase tracking-wide">Ù…Ø¯ÙÙˆØ¹</dt>
                                            <dd class="font-semibold text-blue-700">
                                                {{ number_format($partner->paid_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                            </dd>
                                        </div>
                                    </dl>
                                    <div class="mt-4 flex items-center justify-between">
                                        <p class="text-xs text-slate-500">
                                            {{ number_format($partner->referred_users_count) }} Ø¥Ø­Ø§Ù„Ø© Ù…Ø³Ø¬Ù„Ø©
                                        </p>
                                        <a href="{{ route('admin.referrals.show', $partner) }}" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-orange-300 hover:text-orange-600">
                                            Ø¥Ø¯Ø§Ø±Ø©
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                                Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø´Ø±ÙƒØ§Ø¡ Ù…Ø·Ø§Ø¨Ù‚ÙˆÙ† Ù„Ù†ØªÙŠØ¬Ø© Ø§Ù„Ø¨Ø­Ø« Ø§Ù„Ø­Ø§Ù„ÙŠØ©.
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 mt-4 pt-4">
                        {{ $partners->links() }}
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-1">ØªÙØ¹ÙŠÙ„ Ø´Ø±ÙŠÙƒ Ø¬Ø¯ÙŠØ¯</h2>
                        <p class="text-sm text-slate-500 mb-4">Ø§Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø¨Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø£Ùˆ Ø±Ù‚Ù… Ø§Ù„Ø­Ø³Ø§Ø¨ ÙˆÙØ¹Ù‘Ù„ ØµÙ„Ø§Ø­ÙŠØ© Ù…Ø´Ø§Ø±ÙƒØ© Ø§Ù„Ø±ÙˆØ§Ø¨Ø·.</p>
                        <form method="POST" action="{{ route('admin.referrals.activate') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ Ø£Ùˆ Ø±Ù‚Ù… Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…</label>
                                <input type="text" name="user_lookup" value="{{ old('user_lookup') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200" required>
                                @error('user_lookup')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Ù†Ø³Ø¨Ø© Ø§Ù„Ø¹Ù…ÙˆÙ„Ø© (Ø§Ø®ØªÙŠØ§Ø±ÙŠ)</label>
                                <input type="number" step="0.1" name="referral_commission_rate" value="{{ old('referral_commission_rate', config('referrals.default_rate')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Ø¹Ù…Ù„Ø© Ø§Ù„ØªØ¹Ø§Ù…Ù„ Ù…Ø¹ Ø§Ù„Ø´Ø±ÙŠÙƒ</label>
                                <select name="referral_commission_currency" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                                    @forelse ($currencyOptions as $code => $currency)
                                        <option value="{{ $code }}" @selected(old('referral_commission_currency', config('referrals.default_currency')) === $code)>
                                            {{ $currency['label'] ?? $code }} ({{ $currency['symbol'] ?? $code }})
                                        </option>
                                    @empty
                                        <option value="{{ $defaultReferralCurrency }}" selected>{{ $defaultReferralCurrency }}</option>
                                    @endforelse
                                </select>
                                @error('referral_commission_currency')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full rounded-2xl bg-orange-500 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                                ØªÙØ¹ÙŠÙ„ Ø§Ù„Ø´Ø±ÙŠÙƒ
                            </button>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">Ø£ÙØ¶Ù„ Ø§Ù„Ø´Ø±ÙƒØ§Ø¡</h3>
                        <ul class="space-y-3">
                            @forelse ($topPartners as $partner)
                                <li class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $partner->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $partner->email }}</p>
                                    <p class="text-xs text-emerald-600 mt-1">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¹Ù…ÙˆÙ„Ø§Øª {{ number_format($partner->lifetime_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø¨ÙŠØ§Ù†Ø§Øª Ø¨Ø¹Ø¯.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


