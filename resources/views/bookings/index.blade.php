@extends('layouts.app')

@section('title', __('bookings.meta_title'))

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $currentLocale = app()->getLocale();
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            <div class="rounded-3xl border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-white p-6 md:p-8 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-orange-200/25 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-48 h-48 bg-orange-100/30 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

                <div class="relative flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">{{ __('bookings.hero.title') }}</h1>
                        <p class="mt-2 text-sm md:text-base text-gray-600">
                            {{ __('bookings.hero.description') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                            <i class="fas fa-search text-xs"></i>
                            {{ __('bookings.hero.explore') }}
                        </a>
                        <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-semibold text-orange-600 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700">
                            <i class="fas fa-user-circle text-xs"></i>
                            {{ __('bookings.hero.profile') }}
                        </a>
                    </div>
                </div>
            </div>

            @if (isset($recordingEntries) && $recordingEntries->isNotEmpty())
                @php
                    $isRtl = app()->getLocale() === 'ar';
                    $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
                @endphp
                <section class="mt-8 rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">{{ __('chef.recordings.eyebrow') }}</p>
                            <h2 class="text-2xl font-bold text-slate-900">{{ __('chef.recordings.title') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('chef.recordings.description', ['name' => auth()->user()->name]) }}</p>
                            <p class="mt-2 text-xs font-semibold text-slate-500">{{ __('bookings.recordings.manage_note') }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-2 text-sm text-slate-500 md:items-end">
                            <span>{{ __('bookings.stats.total') }}: {{ $recordingEntries->count() }}</span>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-800" data-show-hidden>
                                <i class="fa-solid fa-eye"></i>
                                <span>{{ __('bookings.recordings.show_all') }}</span>
                                <span class="text-[10px] font-bold text-rose-500" data-hidden-count></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('bookings.history.labels.workshop') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('bookings.history.labels.date') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('bookings.history.labels.format') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('bookings.recordings.access_label') }}</th>
                                    <th class="px-4 py-3 text-left">Preview</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                    <th class="px-4 py-3 text-left">Visibility</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm" id="recording-table-body">
                                @foreach ($recordingEntries as $entry)
                                    <tr data-recording-card data-recording-id="{{ $entry['id'] }}" class="{{ (!empty($entry['hidden']) && $entry['hidden']) ? 'bg-rose-50/40' : '' }}">
                                        <td class="px-4 py-3 align-top">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 text-xs font-semibold text-orange-600">
                                                    <i class="fa-solid fa-circle-play"></i>
                                                    <span>{{ $entry['badge'] ?? __('chef.recordings.badges.available') }}</span>
                                                </div>
                                                @if (!empty($entry['hidden']) && $entry['hidden'] === true)
                                                    <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-[11px] font-semibold text-rose-700">
                                                        <i class="fa-solid fa-eye-slash"></i>
                                                        {{ __('bookings.recordings.hidden_badge') }}
                                                    </div>
                                                @endif
                                                @if (!empty($entry['hidden_global']) && $entry['hidden_global'] === true)
                                                    <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-[11px] font-semibold text-rose-700">
                                                        <i class="fa-solid fa-ban"></i>
                                                        {{ __('bookings.recordings.hide_platform') }}
                                                    </div>
                                                @endif
                                                <p class="font-bold text-slate-900">{{ $entry['title'] }}</p>
                                                @if (!empty($entry['excerpt']))
                                                    <p class="text-xs text-slate-500">{{ $entry['excerpt'] }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="space-y-1">
                                                @if (!empty($entry['date_label']))
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-calendar-day text-amber-500"></i>
                                                        <span>{{ $entry['date_label'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            @if (!empty($entry['location_label']))
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                                    <span>{{ $entry['location_label'] }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            @if (!empty($entry['access']))
                                                <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                                    <i class="fa-solid fa-lock text-slate-500"></i>
                                                    <span>{{ $entry['access'] }}</span>
                                                </div>
                                            @endif
                                            @if (!empty($entry['viewer_count']))
                                                <div class="mt-2 text-xs text-slate-600">
                                                    {{ __('bookings.recordings.viewers', ['count' => $entry['viewer_count'] ?? 0]) }}
                                                </div>
                                            @endif
                                            @if (!empty($entry['viewer_names']))
                                                <div class="mt-1 text-[11px] text-slate-500">
                                                    {{ __('bookings.recordings.viewer_list') }}: {{ implode(', ', $entry['viewer_names']) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="w-48 overflow-hidden rounded-lg border border-slate-100 bg-slate-900/80">
                                                @if (!empty($entry['preview_url']))
                                                    <iframe
                                                        src="{{ $entry['preview_url'] }}"
                                                        class="h-28 w-full"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen
                                                        loading="lazy"
                                                    ></iframe>
                                                @elseif (!empty($entry['is_direct_video']) && !empty($entry['watch_url']))
                                                    <video controls preload="metadata" playsinline class="h-28 w-full object-cover bg-black">
                                                        <source src="{{ $entry['watch_url'] }}">
                                                    </video>
                                                @else
                                                    <div class="flex h-28 w-full flex-col items-center justify-center gap-2 text-xs font-semibold text-slate-200">
                                                        <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                                                        <span>{{ __('chef.recordings.fallback_drive') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex flex-wrap gap-2">
                                                @if (!empty($entry['watch_url']))
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300"
                                                        data-copy-link="{{ $entry['watch_url'] }}"
                                                    >
                                                        <i class="fa-solid fa-link"></i>
                                                        {{ __('bookings.recordings.copy_link') }}
                                                    </button>
                                                    <a
                                                        href="{{ $entry['watch_url'] }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-orange-600"
                                                    >
                                                        <i class="fa-solid fa-play"></i>
                                                        {{ __('chef.recordings.cta.watch') }}
                                                    </a>
                                                @endif
                                                @if (!empty($entry['details_url']))
                                                    <a
                                                        href="{{ $entry['details_url'] }}"
                                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-gray-300"
                                                    >
                                                        {{ $entry['type'] === 'booking' ? __('bookings.history.actions.details') : __('chef.workshops.view_details') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                @endif
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50"
                                                    data-hide-recording
                                                >
                                                    <i class="fa-solid fa-eye-slash"></i>
                                                    {{ __('bookings.recordings.hide') }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            @if (!empty($entry['is_owner']) && $entry['is_owner'] === true && !empty($entry['workshop_id']))
                                                <div class="flex flex-col gap-2">
                                                    <form action="{{ route('bookings.recordings.visibility', $entry['workshop_id']) }}" method="POST" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="hidden" value="{{ $entry['hidden'] ? 0 : 1 }}">
                                                        <input type="hidden" name="scope" value="public">
                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border {{ $entry['hidden'] ? 'border-emerald-200 text-emerald-700' : 'border-slate-200 text-slate-700' }} bg-white px-3 py-2 text-xs font-semibold transition hover:border-slate-300">
                                                            <i class="fa-solid {{ $entry['hidden'] ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                            {{ $entry['hidden'] ? __('bookings.recordings.show_global') : __('bookings.recordings.hide_global') }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('bookings.recordings.visibility', $entry['workshop_id']) }}" method="POST" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="hidden" value="{{ $entry['hidden_global'] ? 0 : 1 }}">
                                                        <input type="hidden" name="scope" value="platform">
                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border {{ $entry['hidden_global'] ? 'border-emerald-200 text-emerald-700' : 'border-red-200 text-red-600' }} bg-white px-3 py-2 text-xs font-semibold transition hover:border-slate-300">
                                                            <i class="fa-solid {{ $entry['hidden_global'] ? 'fa-eye' : 'fa-ban' }}"></i>
                                                            {{ $entry['hidden_global'] ? __('bookings.recordings.show_platform') : __('bookings.recordings.hide_platform') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-500">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const storageKey = 'recordManagement.hiddenRecordings';

            const loadHidden = () => {
                try {
                    return new Set(JSON.parse(localStorage.getItem(storageKey) || '[]'));
                } catch (_) {
                    return new Set();
                }
            };

            const saveHidden = (set) => {
                localStorage.setItem(storageKey, JSON.stringify(Array.from(set)));
            };

            const hiddenIds = loadHidden();
            const cards = document.querySelectorAll('[data-recording-card]');
            const hiddenCountEl = document.querySelector('[data-hidden-count]');
            const showHiddenBtn = document.querySelector('[data-show-hidden]');

            const updateHiddenCount = () => {
                if (!hiddenCountEl) return;
                const count = hiddenIds.size;
                hiddenCountEl.textContent = count > 0 ? `(${count})` : '';
            };

            const applyHiddenState = () => {
                cards.forEach((card) => {
                    const id = card.getAttribute('data-recording-id');
                    card.classList.toggle('hidden', hiddenIds.has(id));
                });
                updateHiddenCount();
            };

            document.querySelectorAll('[data-hide-recording]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const card = btn.closest('[data-recording-card]');
                    if (!card) return;
                    const id = card.getAttribute('data-recording-id');
                    hiddenIds.add(id);
                    saveHidden(hiddenIds);
                    applyHiddenState();
                });
            });

            if (showHiddenBtn) {
                showHiddenBtn.addEventListener('click', () => {
                    hiddenIds.clear();
                    saveHidden(hiddenIds);
                    applyHiddenState();
                });
            }

            document.querySelectorAll('[data-copy-link]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const url = btn.getAttribute('data-copy-link');
                    if (!url) return;
                    try {
                        await navigator.clipboard.writeText(url);
                        btn.textContent = '{{ __('bookings.recordings.copied') }}';
                        setTimeout(() => (btn.textContent = '{{ __('bookings.recordings.copy_link') }}'), 1200);
                    } catch (error) {
                        alert(url);
                    }
                });
            });

            applyHiddenState();
        })();
    </script>
@endpush

