@extends('layouts.app')

@section('title', 'إدارة شريك الإحالات')

@section('content')
    <div class="bg-slate-50 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide mb-2">شريك إحالات</p>
                        <h1 class="text-3xl font-black text-slate-900 mb-2">{{ $user->name }}</h1>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        <p class="text-xs text-slate-400 mt-1">منضم منذ {{ $user->referral_partner_since_at?->locale('ar')->translatedFormat('d F Y') ?? '—' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-slate-600 uppercase">المستخدمون المدعوون</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($user->referredUsers()->count()) }}</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-emerald-600 uppercase">عمولة جاهزة</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format($commissionTotals['ready'], 2) }} {{ $user->referral_currency_symbol }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">إعدادات الشريك</h2>
                    <form method="POST" action="{{ route('admin.referrals.update', $user) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">تفعيل الشريك</p>
                                <p class="text-xs text-slate-500">منح/إلغاء صلاحية مشاركة روابط الإحالة.</p>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_referral_partner" value="0">
                                <input type="checkbox" name="is_referral_partner" value="1" class="sr-only peer" {{ old('is_referral_partner', $user->isReferralPartner()) ? 'checked' : '' }}>
                                <div class="h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-orange-500 transition relative">
                                    <span class="absolute top-0.5 right-0.5 h-5 w-5 rounded-full bg-white shadow peer-checked:translate-x-[-18px] transition"></span>
                                </div>
                            </label>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">نسبة العمولة</label>
                            <input
                                type="number"
                                name="referral_commission_rate"
                                step="0.1"
                                min="0"
                                max="100"
                                value="{{ old('referral_commission_rate', $user->referral_commission_rate ?? config('referrals.default_rate')) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                                required
                            >
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">عملة التعامل مع هذا الشريك</label>
                            <select
                                name="referral_commission_currency"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            >
                                @forelse ($currencyOptions as $code => $currency)
                                    <option value="{{ $code }}" @selected(old('referral_commission_currency', $user->referral_currency_code) === $code)>
                                        {{ $currency['label'] ?? $code }} ({{ $currency['symbol'] ?? $code }})
                                    </option>
                                @empty
                                    <option value="{{ $user->referral_currency_code }}" selected>{{ $user->referral_currency_label }}</option>
                                @endforelse
                            </select>
                            @error('referral_commission_currency')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if($user->role === \App\Models\User::ROLE_CHEF)
                            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">عدم اقتطاع نسبة المنصة من ورش هذا الشريك</p>
                                    <p class="text-xs text-slate-500">عند التفعيل سيتم تحويل صافي المبلغ كاملاً لهذا الشريك في حال كان هو الشيف المستضيف.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">ينطبق فقط على هذا الحساب عند تنظيمه لورشاته.</p>
                                </div>
                                <div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="referral_skip_platform_fee" value="0">
                                        <input
                                            type="checkbox"
                                            name="referral_skip_platform_fee"
                                            value="1"
                                            class="sr-only peer"
                                            {{ old('referral_skip_platform_fee', $user->referral_skip_platform_fee) ? 'checked' : '' }}
                                        >
                                        <div class="h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-emerald-500 transition relative">
                                            <span class="absolute top-0.5 right-0.5 h-5 w-5 rounded-full bg-white shadow peer-checked:translate-x-[-18px] transition"></span>
                                        </div>
                                    </label>
                                    @error('referral_skip_platform_fee')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        <div>
                            <label class="text-xs font-semibold text-slate-600 mb-1 block">ملاحظات داخلية</label>
                            <textarea
                                name="referral_admin_notes"
                                rows="3"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            >{{ old('referral_admin_notes', $user->referral_admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            حفظ التعديلات
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">ملخص العمولات</h3>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                        <p class="text-xs font-semibold text-emerald-600 uppercase">جاهزة للدفع</p>
                        <p class="text-xl font-black text-emerald-700">{{ number_format($commissionTotals['ready'], 2) }} {{ $user->referral_currency_symbol }}</p>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                        <p class="text-xs font-semibold text-blue-600 uppercase">تم دفعها</p>
                        <p class="text-xl font-black text-blue-700">{{ number_format($commissionTotals['paid'], 2) }} {{ $user->referral_currency_symbol }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/60 px-4 py-3">
                        <p class="text-xs font-semibold text-rose-600 uppercase">ملغاة</p>
                        <p class="text-xl font-black text-rose-700">{{ number_format($commissionTotals['cancelled'], 2) }} {{ $user->referral_currency_symbol }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold text-slate-600 uppercase">إجمالي الحجوزات</p>
                        <p class="text-xl font-black text-slate-900">{{ number_format($commissionTotals['count']) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide">الشيفات المدعوون</p>
                            <h2 class="text-xl font-bold text-slate-900">قائمة مختصرة</h2>
                        </div>
                        <span class="text-xs text-slate-500">({{ number_format($referredChefs->count()) }})</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($referredChefs as $chef)
                            <div class="rounded-2xl border border-slate-100 px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $chef->name }}</p>
                                <p class="text-xs text-slate-500">{{ $chef->email }}</p>
                                <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                                    <span>ورش {{ $chef->workshops_count }}</span>
                                    <span>حجوزات {{ $chef->generated_commissions_count }}</span>
                                    <span>عمولات {{ number_format($chef->generated_commissions_total ?? 0, 2) }} {{ $user->referral_currency_symbol }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">لا يوجد شيفات مسجلون عبر هذا الشريك.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">آخر المستخدمين</p>
                            <h2 class="text-xl font-bold text-slate-900">مسجلون عبر الرابط</h2>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($recentReferrals as $ref)
                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $ref->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $ref->email }}</p>
                                </div>
                                <p class="text-xs text-slate-400">{{ $ref->created_at->locale('ar')->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">لا توجد تسجيلات حديثة.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase">سجل العمولات</p>
                        <h2 class="text-xl font-bold text-slate-900">الحجوزات المدفوعة</h2>
                    </div>
                    <span class="text-xs text-slate-500">إجمالي {{ number_format($commissions->total()) }}</span>
                </div>
                <div class="overflow-x-auto">
                    @php
                        $commissionStatusMeta = [
                            \App\Models\ReferralCommission::STATUS_READY => [
                                'label' => 'جاهزة للتحويل',
                                'classes' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                            ],
                            \App\Models\ReferralCommission::STATUS_PAID => [
                                'label' => 'تم دفعها',
                                'classes' => 'bg-blue-50 text-blue-700 border border-blue-100',
                            ],
                            \App\Models\ReferralCommission::STATUS_PENDING => [
                                'label' => 'قيد المراجعة',
                                'classes' => 'bg-amber-50 text-amber-700 border border-amber-100',
                            ],
                            \App\Models\ReferralCommission::STATUS_CANCELLED => [
                                'label' => 'ملغاة',
                                'classes' => 'bg-rose-50 text-rose-700 border border-rose-100',
                            ],
                        ];
                    @endphp
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الورشة</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">المشارك</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">المبلغ</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الحالة</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">تاريخ</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($commissions as $commission)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $commission->workshop?->title ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ optional($commission->workshop?->start_date)->locale('ar')->translatedFormat('d F Y') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $commission->participant?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-emerald-600">{{ number_format($commission->commission_amount, 2) }} {{ $commission->currency_symbol }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        @php
                                            $statusMeta = $commissionStatusMeta[$commission->status] ?? null;
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['classes'] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            {{ $statusMeta['label'] ?? $commission->status }}
                                        </span>
                                        @if($commission->status === \App\Models\ReferralCommission::STATUS_PAID && $commission->paid_at)
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                تم التحويل بتاريخ {{ $commission->paid_at->locale('ar')->translatedFormat('d F Y - h:i a') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ optional($commission->earned_at ?? $commission->created_at)->locale('ar')->translatedFormat('d F Y - h:i a') }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                    @if(in_array($commission->status, [\App\Models\ReferralCommission::STATUS_READY, \App\Models\ReferralCommission::STATUS_PENDING]))
                                            <form method="POST" action="{{ route('admin.referrals.commissions.update-status', [$user, $commission]) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \App\Models\ReferralCommission::STATUS_PAID }}">
                                                <input
                                                    type="text"
                                                    name="notes"
                                                    placeholder="ملاحظات التحويل (اختياري)"
                                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:border-orange-400 focus:ring-1 focus:ring-orange-200"
                                                >
                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                                    onclick="return confirm('هل تريد تعليم هذه العمولة كمحوّلة/مدفوعة؟');"
                                                >
                                                    تحويل / تم دفع المبلغ
                                                </button>
                                            </form>
                                        @elseif($commission->status === \App\Models\ReferralCommission::STATUS_PAID)
                                            <p class="text-xs text-emerald-600 font-semibold">تم تحويل المبلغ</p>
                                            <form method="POST" action="{{ route('admin.referrals.commissions.update-status', [$user, $commission]) }}" class="space-y-2 mt-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \App\Models\ReferralCommission::STATUS_READY }}">
                                                <input
                                                    type="text"
                                                    name="notes"
                                                    placeholder="ملاحظات (اختياري)"
                                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:border-orange-400 focus:ring-1 focus:ring-orange-200"
                                                >
                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                                    onclick="return confirm('إرجاع هذه العمولة إلى حالة جاهزة؟');"
                                                >
                                                    إعادة كجاهزة
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">لا توجد إجراءات</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">لا توجد عمولات بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $commissions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

