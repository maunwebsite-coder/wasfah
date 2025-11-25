@extends('layouts.app')

@section('title', __('chef.workshops_earnings.title'))

@section('content')
@php
    $formatCurrency = function ($amount) {
        $value = (float) ($amount ?? 0);
        return number_format($value, 2) . ' د.أ';
    };

    $formatNumber = fn ($number) => number_format((int) ($number ?? 0));
@endphp
<div class="min-h-screen bg-gradient-to-b from-orange-50/70 to-white py-10">
    <div class="container mx-auto px-4 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">
                    {{ __('chef.workshops_earnings.hero.eyebrow') }}
                </p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">
                    {{ __('chef.workshops_earnings.hero.heading') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ __('chef.workshops_earnings.hero.description') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('chef.workshops.create') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700">
                    <i class="fas fa-plus"></i>
                    {{ __('chef.workshops_earnings.hero.cta.new') }}
                </a>
                <a href="{{ route('chef.workshops.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-slate-600 shadow-sm hover:border-slate-300 hover:text-slate-800">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('chef.workshops_earnings.hero.cta.back') }}
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5 text-amber-900 shadow">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-amber-500 shadow-inner">
                    <i class="fas fa-balance-scale text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-semibold uppercase tracking-wider text-amber-500">
                        {{ __('chef.workshops_earnings.notice.eyebrow') }}
                    </p>
                    <h2 class="text-lg font-bold text-amber-900">
                        {{ __('chef.workshops_earnings.notice.title') }}
                    </h2>
                    <p class="text-sm leading-relaxed text-amber-800">
                        {{ __('chef.workshops_earnings.notice.description') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-4">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.stats.gross.label') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $formatCurrency($lifetimeGross) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.stats.gross.hint') }}
                </p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.stats.net.label') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $formatCurrency($lifetimeNet) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.stats.net.hint') }}
                </p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.stats.paid_seats.label') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $formatNumber($paidSeats) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.stats.paid_seats.hint') }}
                </p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.stats.average.label') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $formatCurrency($averageNetSeat) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.stats.average.hint') }}
                </p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.current.label') }}
                </p>
                <div class="mt-3 flex items-baseline gap-3">
                    <p class="text-3xl font-bold text-slate-900">{{ $formatCurrency($currentMonthGross) }}</p>
                    @php
                        $delta = $currentMonthGross - $previousMonthGross;
                        $deltaLabel = ($delta >= 0 ? '+' : '') . $formatCurrency($delta);
                    @endphp
                    <span class="text-sm font-semibold {{ $delta >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ __('chef.workshops_earnings.monthly.current.delta', ['amount' => $deltaLabel]) }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.current.net_label') }}
                    <span class="font-semibold text-emerald-600">{{ $formatCurrency($currentMonthNet) }}</span>
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.current.hint') }}
                </p>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-semibold text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.previous.label') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $formatCurrency($previousMonthGross) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.previous.net_label') }}
                    <span class="font-semibold text-slate-700">{{ $formatCurrency($previousMonthNet) }}</span>
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ __('chef.workshops_earnings.monthly.previous.hint') }}
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">
                        {{ __('chef.workshops_earnings.leaderboard.eyebrow') }}
                    </p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">
                        {{ __('chef.workshops_earnings.leaderboard.title') }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('chef.workshops_earnings.leaderboard.description') }}
                    </p>
                </div>
                <a href="{{ route('chef.workshops.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:border-slate-300 hover:text-slate-800">
                    {{ __('chef.workshops_earnings.leaderboard.button') }}
                </a>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">
                                {{ __('chef.workshops_earnings.leaderboard.table.workshop') }}
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">
                                {{ __('chef.workshops_earnings.leaderboard.table.start_date') }}
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">
                                {{ __('chef.workshops_earnings.leaderboard.table.paid') }}
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">
                                {{ __('chef.workshops_earnings.leaderboard.table.gross') }}
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">
                                {{ __('chef.workshops_earnings.leaderboard.table.net') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($workshopBreakdown as $workshop)
                            @php
                                $paidTotal = (float) ($workshop->paid_total ?? 0);
                                $netAmount = (float) ($workshop->chef_net_total ?? 0);
                                $capacityValue = $workshop->max_participants
                                    ? $workshop->max_participants
                                    : __('chef.workshops_earnings.leaderboard.capacity_unknown');
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ $workshop->title }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ __('chef.workshops_earnings.leaderboard.capacity', ['capacity' => $capacityValue]) }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    @if ($workshop->start_date)
                                        {{ $workshop->start_date->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') }}
                                    @else
                                        <span class="text-xs text-slate-400">
                                            {{ __('chef.workshops_earnings.leaderboard.date_pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-slate-900 font-semibold">
                                    {{ $formatNumber($workshop->paid_seats) }}
                                </td>
                                <td class="px-4 py-4 text-slate-900 font-semibold">
                                    {{ $formatCurrency($paidTotal) }}
                                </td>
                                <td class="px-4 py-4 text-emerald-600 font-semibold">
                                    {{ $formatCurrency($netAmount) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                    {{ __('chef.workshops_earnings.leaderboard.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

