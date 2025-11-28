@extends('layouts.app')

@section('title', __('chef.recordings.title'))

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-10">
        <div class="container mx-auto px-4">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-500">{{ __('chef.recordings.eyebrow') }}</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('chef.recordings.title') }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ __('chef.recordings.description', ['name' => auth()->user()->name]) }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('chef.workshops.index') }}"
                       class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-slate-700 shadow-sm hover:border-slate-300 hover:text-slate-900">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('chef.dashboard.workshops.ctas.recipes') }}
                    </a>
                    <a href="{{ route('chef.workshops.create') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700">
                        <i class="fas fa-plus"></i>
                        {{ __('chef.dashboard.workshops.ctas.new') }}
                    </a>
                </div>
            </div>

            @if (! $driveEnabled)
                <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-amber-800 shadow">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <div>
                            <p class="font-semibold">خدمة Google Drive غير مفعّلة</p>
                            <p class="text-sm text-amber-700 mt-1">لن يتم جلب التسجيلات تلقائياً حتى يتم تفعيل إعدادات Google Drive.</p>
                        </div>
                    </div>
                </div>
            @endif

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

            <div class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                @forelse ($workshops as $workshop)
                    @php
                        $coverImage = $workshop->image
                            ? (Str::startsWith($workshop->image, ['http://', 'https://'])
                                ? $workshop->image
                                : Storage::disk('public')->url($workshop->image))
                            : null;
                        $recordingUrl = $workshop->recording_source_url;
                        $recordingPreview = $workshop->recording_preview_url;
                    @endphp
                    <div class="border-b border-slate-100 p-5 last:border-b-0">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex flex-1 items-start gap-4">
                                <div class="h-24 w-32 overflow-hidden rounded-2xl bg-slate-100 shadow-inner">
                                    @if ($coverImage)
                                        <img src="{{ $coverImage }}" alt="{{ $workshop->title }}" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-400">
                                            <i class="fas fa-video text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold {{ $workshop->is_online ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-600' }}">
                                            <i class="fas {{ $workshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }}"></i>
                                            {{ $workshop->is_online ? __('chef.dashboard.workshops.badges.delivery.online') : __('chef.dashboard.workshops.badges.delivery.in_person') }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold {{ $workshop->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                            <i class="fas {{ $workshop->is_active ? 'fa-circle-check' : 'fa-pause' }}"></i>
                                            {{ $workshop->is_active ? __('chef.dashboard.workshops.badges.status.published') : __('chef.dashboard.workshops.badges.status.draft') }}
                                        </span>
                                        @if ($workshop->meeting_code)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                                <i class="fas fa-key"></i>
                                                <span class="uppercase">{{ $workshop->meeting_code }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <h2 class="text-xl font-bold text-slate-900">{{ $workshop->title }}</h2>
                                    <p class="text-sm text-slate-500">
                                        {{ $workshop->formatted_start_date ?? __('chef.workshops.unscheduled_time') }}
                                    </p>
                                    <p class="text-sm text-slate-600">
                                        {{ Str::limit(strip_tags($workshop->description ?? ''), 120) }}
                                    </p>
                                </div>
                            </div>
                            <div class="lg:w-80 space-y-3">
                                @if ($recordingUrl)
                                    @if ($recordingPreview)
                                        <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-inner">
                                            <iframe src="{{ $recordingPreview }}" class="aspect-video w-full" allow="autoplay; fullscreen" loading="lazy"></iframe>
                                        </div>
                                    @endif
                                    <a href="{{ $recordingUrl }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-slate-800">
                                        <i class="fas fa-play"></i>
                                        {{ __('chef.recordings.cta.watch') }}
                                    </a>
                                @else
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                        لم يتم العثور على تسجيل حتى الآن. جرّب مزامنة الرابط من Google Drive.
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('chef.workshops.recording', $workshop) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:border-indigo-300 hover:text-indigo-700">
                                        <i class="fas fa-rotate"></i>
                                        تحديث الرابط من Google Drive
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <p class="text-lg font-semibold text-slate-800">لا توجد تسجيلات حتى الآن</p>
                        <p class="mt-2 text-sm text-slate-500">بعد انتهاء الورشات ومزامنة التسجيل سيتم عرضه هنا.</p>
                        <a href="{{ route('chef.workshops.index') }}"
                           class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700">
                            <i class="fas fa-arrow-right"></i>
                            إدارة الورشات
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

