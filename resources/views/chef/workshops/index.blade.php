@extends('layouts.app')

@section('title', __('chef.dashboard.workshops.meta_title'))

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp
<div class="min-h-screen bg-gradient-to-b from-orange-50/50 to-white py-10">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.dashboard.workshops.hero.eyebrow') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('chef.dashboard.workshops.hero.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('chef.dashboard.workshops.hero.description') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('chef.workshops.create') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700">
                    <i class="fas fa-plus"></i>
                    {{ __('chef.dashboard.workshops.ctas.new') }}
                </a>
                <a href="{{ route('chef.workshops.earnings') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-emerald-600 shadow-sm hover:border-emerald-300 hover:text-emerald-700">
                    <i class="fas fa-wallet"></i>
                    {{ __('chef.dashboard.workshops.ctas.earnings') }}
                </a>
                <a href="{{ route('chef.dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-slate-600 shadow-sm hover:border-slate-300 hover:text-slate-800">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('chef.dashboard.workshops.ctas.recipes') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800 shadow">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 p-5 text-rose-700 shadow">
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        @endif
        @if (session('info'))
            <div class="mb-6 rounded-3xl border border-sky-200 bg-sky-50 p-5 text-sky-700 shadow">
                <p class="font-semibold">{{ session('info') }}</p>
            </div>
        @endif

        @php
            $resetWorkshopSlug = session('host_join_device_reset_workshop_slug') ?? old('reset_workshop_slug');
            $resetWorkshopTitle = session('host_join_device_reset_workshop_title') ?? old('reset_workshop_title');
            $resetReason = session('host_join_device_reset_reason') ?? old('reset_device_reason');
            $reasonKey = $resetReason ? 'chef.dashboard.workshops.device_reset.reasons.' . $resetReason : null;
            $reasonMessage = $reasonKey ? __($reasonKey) : null;
            if ($reasonKey && $reasonMessage === $reasonKey) {
                $reasonMessage = null;
            }
        @endphp

        @if ($resetWorkshopSlug)
            <div class="mb-8 rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-800 shadow">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-amber-600">{{ __('chef.dashboard.workshops.device_reset.badge') }}</p>
                            <h2 class="mt-1 text-lg font-bold text-amber-900">{{ __('chef.dashboard.workshops.device_reset.title', ['title' => $resetWorkshopTitle]) }}</h2>
                            <p class="mt-1 text-sm text-amber-700">
                                {{ __('chef.dashboard.workshops.device_reset.description') }}
                            </p>
                            @if ($reasonMessage)
                                <p class="mt-2 text-xs text-amber-700/80">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $reasonMessage }}
                                </p>
                            @endif
                        </div>

                        <form action="{{ route('chef.workshops.reset-device', ['workshop' => $resetWorkshopSlug]) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="reset_workshop_slug" value="{{ $resetWorkshopSlug }}">
                            <input type="hidden" name="reset_workshop_title" value="{{ $resetWorkshopTitle }}">
                            <input type="hidden" name="reset_device_reason" value="{{ $resetReason }}">

                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <label class="flex-1">
                                    <span class="sr-only">{{ __('chef.dashboard.workshops.device_reset.password_label') }}</span>
                                    <input
                                        type="password"
                                        name="password"
                                        class="w-full rounded-2xl border border-amber-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
                                        placeholder="{{ __('chef.dashboard.workshops.device_reset.password_placeholder') }}"
                                        required
                                    >
                                </label>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow hover:from-amber-600 hover:to-orange-600">
                                    <i class="fas fa-unlock"></i>
                                    {{ __('chef.dashboard.workshops.device_reset.confirm') }}
                                </button>
                                <a href="{{ route('chef.workshops.join', ['workshop' => $resetWorkshopSlug]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-white px-5 py-3 text-sm font-semibold text-amber-700 shadow-sm hover:border-amber-300 hover:text-amber-900">
                                    <i class="fas fa-redo"></i>
                                    {{ __('chef.dashboard.workshops.device_reset.retry') }}
                                </a>
                            </div>
                            @error('password')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </form>

                        <p class="text-xs text-amber-700">
                            {{ __('chef.dashboard.workshops.device_reset.footnote') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $statCards = [
                [
                    'label' => __('chef.dashboard.workshops.stats.total'),
                    'value' => $stats['total'],
                    'accent' => 'text-slate-900',
                ],
                [
                    'label' => __('chef.dashboard.workshops.stats.active'),
                    'value' => $stats['active'],
                    'accent' => 'text-emerald-600',
                ],
                [
                    'label' => __('chef.dashboard.workshops.stats.online'),
                    'value' => $stats['online'],
                    'accent' => 'text-indigo-600',
                ],
                [
                    'label' => __('chef.dashboard.workshops.stats.drafts'),
                    'value' => $stats['drafts'],
                    'accent' => 'text-orange-600',
                ],
            ];
        @endphp
        <div class="mb-8">
            <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 md:mx-0 md:grid md:grid-cols-4 md:gap-4 md:overflow-visible md:px-0 md:pb-0 md:snap-none">
                @foreach ($statCards as $card)
                    <div class="snap-start rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex min-w-[220px] flex-col flex-none md:min-w-0">
                        <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold {{ $card['accent'] }}">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            @forelse ($workshops as $workshop)
                @php
                    $coverImage = $workshop->image
                        ? (Str::startsWith($workshop->image, ['http://', 'https://'])
                            ? $workshop->image
                            : Storage::disk('public')->url($workshop->image))
                        : null;
                @endphp
                <div class="border-b border-slate-100 p-5 last:border-b-0">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 items-start gap-4">
                            <div class="h-16 w-20 overflow-hidden rounded-2xl bg-slate-100 shadow-inner">
                                @if ($coverImage)
                                    <img src="{{ $coverImage }}"
                                         alt="{{ $workshop->title }}"
                                         class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-400">
                                        <i class="fas fa-camera text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-orange-500">{{ $workshop->category }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $workshop->is_online ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-600' }}">
                                        <i class="fas {{ $workshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }}"></i>
                                        {{ $workshop->is_online ? __('chef.dashboard.workshops.badges.delivery.online') : __('chef.dashboard.workshops.badges.delivery.in_person') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $workshop->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                        <i class="fas {{ $workshop->is_active ? 'fa-circle-check' : 'fa-pause' }}"></i>
                                        {{ $workshop->is_active ? __('chef.dashboard.workshops.badges.status.published') : __('chef.dashboard.workshops.badges.status.draft') }}
                                    </span>
                                </div>
                                <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $workshop->title }}</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ optional($workshop->start_date)->locale(app()->getLocale())->translatedFormat(__('chef.workshops.datetime_format')) }}
                                    @if ($workshop->duration)
                                        {{ __('chef.dashboard.workshops.duration', ['minutes' => $workshop->duration]) }}
                                    @endif
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                    <span><i class="fas fa-users text-slate-400"></i> {{ __('chef.dashboard.workshops.capacity', ['current' => $workshop->confirmed_bookings ?? 0, 'max' => $workshop->max_participants]) }}</span>
                                    @if ($workshop->price)
                                        <span><i class="fas fa-tag text-slate-400"></i> {{ number_format($workshop->price, 2) }} {{ $workshop->currency }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-start gap-3 md:w-64">
                            @if ($workshop->is_online)
                                <div class="w-full rounded-2xl bg-slate-50 p-4 text-xs text-slate-600">
                                    <p class="font-semibold text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-shield-alt text-indigo-500"></i>
                                        {{ __('chef.dashboard.workshops.google_card.title') }}
                                    </p>
                                    <p class="mt-1 text-slate-500">
                                        {{ __('chef.dashboard.workshops.google_card.description') }}
                                    </p>
                                    @if ($workshop->meeting_link)
                                        <a href="{{ route('chef.workshops.join', $workshop) }}"
                                           class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow hover:from-indigo-600 hover:to-indigo-700">
                                            <i class="fas fa-play"></i>
                                            {{ __('chef.dashboard.workshops.google_card.launch') }}
                                        </a>
                                        @if ($workshop->meeting_started_at)
                                            <p class="mt-2 text-xs text-emerald-600 flex items-center gap-2">
                                                <i class="fas fa-check-circle"></i>
                                                {{ __('chef.dashboard.workshops.google_card.launched', ['time' => $workshop->meeting_started_at->locale(app()->getLocale())->diffForHumans()]) }}
                                            </p>
                                        @else
                                            <p class="mt-2 text-[11px] text-slate-400">
                                                {{ __('chef.dashboard.workshops.google_card.not_ready') }}
                                            </p>
                                        @endif
                                    @else
                                        <button type="button"
                                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-300 px-3 py-2 text-sm font-semibold text-slate-400"
                                                disabled>
                                            {{ __('chef.dashboard.workshops.google_card.pending_link') }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                            <div class="flex w-full flex-wrap items-center gap-2">
                                <a href="{{ route('chef.workshops.edit', $workshop) }}"
                                   class="flex-1 rounded-2xl border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900">
                                    {{ __('chef.dashboard.workshops.actions.edit') }}
                                </a>
                                <form action="{{ route('chef.workshops.destroy', $workshop) }}" method="POST" class="flex-1"
                                      onsubmit="return confirm('{{ __('chef.dashboard.workshops.actions.delete_confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full rounded-2xl border border-red-200 px-3 py-2 text-center text-sm font-semibold text-red-600 hover:bg-red-50">
                                        {{ __('chef.dashboard.workshops.actions.delete') }}
                                    </button>
                                </form>
                            </div>
                            @if ($workshop->is_active)
                                <a href="{{ route('workshop.show', $workshop) }}" target="_blank"
                                   class="w-full rounded-2xl bg-slate-900 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-slate-800">
                                    {{ __('chef.dashboard.workshops.actions.view_public') }}
                                </a>
                            @else
                                <div class="w-full rounded-2xl border border-dashed border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-400">
                                    {{ __('chef.dashboard.workshops.actions.pending_public') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-lg font-semibold text-slate-800">{{ __('chef.dashboard.workshops.empty.title') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('chef.dashboard.workshops.empty.description') }}</p>
                    <a href="{{ route('chef.workshops.create') }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700">
                        <i class="fas fa-plus"></i>
                        {{ __('chef.dashboard.workshops.empty.cta') }}
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $workshops->links() }}
        </div>
    </div>
</div>
@endsection


