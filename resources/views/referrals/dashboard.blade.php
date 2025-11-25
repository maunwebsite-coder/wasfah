@extends('layouts.app')

@section('title', __('referrals.title'))

@php
    $locale = app()->getLocale();
@endphp

@section('content')
    <div class="bg-slate-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white rounded-3xl shadow-sm border border-orange-100 p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-sm font-semibold text-orange-500 mb-2">{{ __('referrals.hero.badge') }}</p>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-4">
                            {{ __('referrals.hero.welcome', ['name' => $partner->name]) }}
                        </h1>
                        <p class="text-slate-600 leading-relaxed max-w-2xl">
                            {{ __('referrals.hero.description', [
                                'rate' => number_format($partner->referral_commission_rate ?? config('referrals.default_rate'), 2),
                            ]) }}
                        </p>
                    </div>
                    <div class="w-full lg:w-1/2">
                        <label class="text-sm font-semibold text-slate-600 mb-2 block">{{ __('referrals.hero.link_label') }}</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    readonly
                                    id="referral-link-input"
                                    value="{{ $referralLink }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                            >
                            </div>
                            <button
                                id="copy-referral-link"
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-200"
                            >
                                <i class="fas fa-copy mr-2"></i>
                                {{ __('referrals.hero.copy_button') }}
                            </button>
                        </div>
                        <p id="copy-feedback" class="text-xs text-emerald-600 mt-2 hidden">{{ __('referrals.hero.copy_feedback') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-orange-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide">{{ __('referrals.stats.registered_users') }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($referredUsersCount) }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('referrals.stats.registered_chefs', ['count' => number_format($referredChefsCount)]) }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wide">{{ __('referrals.stats.ready_commissions') }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($readyAmount, 2) }} {{ $partner->referral_currency_symbol }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('referrals.stats.ready_bookings', ['count' => number_format($readyCount)]) }}</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">{{ __('referrals.stats.total_paid') }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($paidAmount, 2) }} {{ $partner->referral_currency_symbol }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('referrals.stats.total_paid_hint') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.stats.active_chefs') }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($activeChefsCount) }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ __('referrals.stats.active_chefs_hint') }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide">{{ __('referrals.chefs.badge') }}</p>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('referrals.chefs.title') }}</h2>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600">
                            {{ __('referrals.chefs.count', ['count' => number_format($referredChefsCount)]) }}
                        </span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($referredChefs as $chef)
                            <div class="rounded-2xl border border-slate-100 p-4 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $chef->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $chef->email }}</p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ __('referrals.chefs.activity', [
                                            'workshops' => number_format($chef->workshops_count),
                                            'bookings' => number_format($chef->referral_commissions_count),
                                        ]) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500">{{ __('referrals.chefs.revenue_label') }}</p>
                                    <p class="text-lg font-bold text-emerald-600">{{ number_format($chef->referral_commissions_total ?? 0, 2) }} {{ $partner->referral_currency_symbol }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('referrals.chefs.empty') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">{{ __('referrals.commissions_summary.badge') }}</p>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('referrals.commissions_summary.title') }}</h2>
                        </div>
                    </div>
                    <dl class="space-y-4">
                        <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                            <dt class="text-sm font-semibold text-emerald-700">{{ __('referrals.commissions_summary.ready') }}</dt>
                            <dd class="text-lg font-black text-emerald-700">{{ number_format($readyAmount, 2) }} {{ $partner->referral_currency_symbol }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                            <dt class="text-sm font-semibold text-blue-700">{{ __('referrals.commissions_summary.paid') }}</dt>
                            <dd class="text-lg font-black text-blue-700">{{ number_format($paidAmount, 2) }} {{ $partner->referral_currency_symbol }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <dt class="text-sm font-semibold text-slate-600">{{ __('referrals.commissions_summary.cancelled') }}</dt>
                            <dd class="text-lg font-black text-slate-700">{{ number_format($cancelledAmount, 2) }} {{ $partner->referral_currency_symbol }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                            <dt class="text-sm font-semibold text-orange-600">{{ __('referrals.commissions_summary.total') }}</dt>
                            <dd class="text-lg font-black text-orange-600">{{ number_format($totalCommissions) }}</dd>
                        </div>
                    </dl>
                    <p class="text-xs text-slate-400 mt-4">{{ __('referrals.commissions_summary.note') }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.recent.badge') }}</p>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('referrals.recent.title') }}</h2>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentReferrals as $ref)
                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $ref->name ?? __('referrals.recent.nameless') }}</p>
                                    <p class="text-xs text-slate-500">{{ $ref->email }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    @php
                                        $roleKey = 'roles.' . $ref->role;
                                        $roleLabel = \Illuminate\Support\Facades\Lang::hasForLocale($roleKey, $locale)
                                            ? __($roleKey, [], $locale)
                                            : $ref->role;
                                    @endphp
                                    <p class="font-semibold text-slate-600">
                                        {{ $roleLabel }}
                                    </p>
                                    <p>{{ optional($ref->created_at)->locale($locale)->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('referrals.recent.empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.badge') }}</p>
                        <h2 class="text-xl font-bold text-slate-900">{{ __('referrals.history.title') }}</h2>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ __('referrals.history.count', ['count' => number_format($commissions->total())]) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.workshop') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.chef') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.participant') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.share') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('referrals.history.table.due_date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($commissions as $commission)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-900">{{ $commission->workshop?->title ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ optional($commission->workshop?->start_date)->locale($locale)->translatedFormat('d F Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $commission->referredUser?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $commission->participant?->name ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-emerald-600">{{ number_format($commission->commission_amount, 2) }} {{ $commission->currency_symbol }}</p>
                                        <p class="text-xs text-slate-400">{{ __('referrals.history.table.rate', ['rate' => number_format($commission->commission_rate, 2)]) }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClasses = [
                                                \App\Models\ReferralCommission::STATUS_READY => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                \App\Models\ReferralCommission::STATUS_PAID => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                \App\Models\ReferralCommission::STATUS_CANCELLED => 'bg-rose-50 text-rose-700 border border-rose-200',
                                                \App\Models\ReferralCommission::STATUS_PENDING => 'bg-slate-50 text-slate-600 border border-slate-200',
                                            ];
                                            $statusLabels = [
                                                \App\Models\ReferralCommission::STATUS_READY => __('referrals.statuses.ready'),
                                                \App\Models\ReferralCommission::STATUS_PAID => __('referrals.statuses.paid'),
                                                \App\Models\ReferralCommission::STATUS_CANCELLED => __('referrals.statuses.cancelled'),
                                                \App\Models\ReferralCommission::STATUS_PENDING => __('referrals.statuses.pending'),
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$commission->status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $statusLabels[$commission->status] ?? ucfirst($commission->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ optional($commission->earned_at ?? $commission->created_at)->locale($locale)->translatedFormat('d F Y - h:i a') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                                        {{ __('referrals.history.empty') }}
                                    </td>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const copyButton = document.getElementById('copy-referral-link');
            const input = document.getElementById('referral-link-input');
            const feedback = document.getElementById('copy-feedback');
            const copyErrorMessage = @json(__('referrals.hero.copy_error'));

            if (copyButton && input) {
                copyButton.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(input.value);
                        feedback?.classList.remove('hidden');
                        setTimeout(() => feedback?.classList.add('hidden'), 2500);
                    } catch (error) {
                        alert(copyErrorMessage);
                    }
                });
            }
        });
    </script>
@endsection

