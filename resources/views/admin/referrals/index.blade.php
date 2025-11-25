@extends('layouts.app')

@section('title', 'برنامج الإحالات - الإدارة')

@section('content')
    @php
        $defaultReferralCurrency = config('referrals.default_currency', 'USD');
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
                        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide mb-2">الإدارة المالية</p>
                        <h1 class="text-3xl font-black text-slate-900 mb-3">برنامج الإحالات</h1>
                        <p class="text-slate-600 leading-relaxed max-w-2xl">
                            تتبع أداء شركاء النمو وحدد نسب العمولات المناسبة، مع نظرة كاملة على الحجوزات المدفوعة التي تستحق الدفع.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-emerald-600 uppercase">عمولات جاهزة</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format($stats['ready_amount'], 2) }} {{ $defaultReferralSymbol }}</p>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-blue-600 uppercase">مدفوع هذا الشهر</p>
                            <p class="text-2xl font-black text-blue-700">{{ number_format($stats['paid_amount'], 2) }} {{ $defaultReferralSymbol }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-slate-600 uppercase">عدد الشركاء</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($stats['partners_count']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-orange-600 uppercase">شركاء جدد هذا الشهر</p>
                            <p class="text-2xl font-black text-orange-600">{{ number_format($stats['new_partners_this_month']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <form method="GET" action="{{ route('admin.referrals.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="flex-1">
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">البحث عن شريك</label>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="الاسم، البريد أو كود الإحالة"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            >
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            <i class="fas fa-search ml-2"></i>
                            بحث
                        </button>
                    </form>

                    <div class="mt-6 hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الشريك</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الرمز</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">مستخدمون</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">جاهز</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">مدفوع</th>
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
                                                إدارة
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                            لا يوجد شركاء مطابقون لنتيجة البحث الحالية.
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
                                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">مستخدمون</dt>
                                            <dd class="font-semibold text-slate-700">
                                                {{ number_format($partner->referred_users_count) }}
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl bg-emerald-50 px-3 py-2">
                                            <dt class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide">جاهز</dt>
                                            <dd class="font-semibold text-emerald-700">
                                                {{ number_format($partner->pending_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl bg-blue-50 px-3 py-2">
                                            <dt class="text-[11px] font-semibold text-blue-600 uppercase tracking-wide">مدفوع</dt>
                                            <dd class="font-semibold text-blue-700">
                                                {{ number_format($partner->paid_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}
                                            </dd>
                                        </div>
                                    </dl>
                                    <div class="mt-4 flex items-center justify-between">
                                        <p class="text-xs text-slate-500">
                                            {{ number_format($partner->referred_users_count) }} إحالة مسجلة
                                        </p>
                                        <a href="{{ route('admin.referrals.show', $partner) }}" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-orange-300 hover:text-orange-600">
                                            إدارة
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                                لا يوجد شركاء مطابقون لنتيجة البحث الحالية.
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 mt-4 pt-4">
                        {{ $partners->links() }}
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-1">تفعيل شريك جديد</h2>
                        <p class="text-sm text-slate-500 mb-4">ابحث عن المستخدم بالبريد أو رقم الحساب وفعّل صلاحية مشاركة الروابط.</p>
                        <form method="POST" action="{{ route('admin.referrals.activate') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">البريد الإلكتروني أو رقم المستخدم</label>
                                <input type="text" name="user_lookup" value="{{ old('user_lookup') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200" required>
                                @error('user_lookup')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">نسبة العمولة (اختياري)</label>
                                <input type="number" step="0.1" name="referral_commission_rate" value="{{ old('referral_commission_rate', config('referrals.default_rate')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">عملة التعامل مع الشريك</label>
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
                                تفعيل الشريك
                            </button>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">أفضل الشركاء</h3>
                        <ul class="space-y-3">
                            @forelse ($topPartners as $partner)
                                <li class="rounded-2xl border border-slate-100 px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $partner->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $partner->email }}</p>
                                    <p class="text-xs text-emerald-600 mt-1">إجمالي العمولات {{ number_format($partner->lifetime_commission_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">لا توجد بيانات بعد.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

