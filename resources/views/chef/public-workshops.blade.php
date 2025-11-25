@extends('layouts.app')

@section('title', 'ورشات ' . ($chef->name ?? 'الشيف'))

@php
    use Illuminate\Support\Str;

    $publicHandle = $chef->username
        ?? ($chef->slug ?? Str::slug($chef->name ?? 'chef', '-'));
@endphp

@section('content')
    <div class="bg-slate-50 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-orange-100 bg-gradient-to-br from-white via-orange-50 to-orange-100 p-8 shadow-xl sm:p-10">
                <div class="flex flex-col items-center gap-6 text-center">
                    <div class="relative h-28 w-28 overflow-hidden rounded-full border-4 border-white shadow-2xl">
                        <img src="{{ $avatarUrl }}" alt="صورة {{ $chef->name }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-500">ورشات مسجّلة</p>
                        <h1 class="mt-3 text-3xl font-black text-slate-900 sm:text-4xl">
                            كل الورشات الخاصة بـ {{ $chef->name }}
                        </h1>
                        @if ($publicHandle)
                            <p class="mt-2 text-sm font-semibold text-slate-500">
                                @<span dir="ltr">{{ $publicHandle }}</span>
                            </p>
                        @endif
                        <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600">
                            هنا تجد مكتبة الورشات المسجّلة للشيف، يمكنك مشاهدة التسجيل مباشرةً داخل الموقع بدون الحاجة لمغادرته.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">جميع الورشات</h2>
                        <p class="text-sm text-slate-500">مشاهدة سلسة لكل التسجيلات المتاحة</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-orange-100 bg-white px-4 py-2 text-sm font-semibold text-orange-600 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        {{ number_format($workshops->count()) }} ورشة
                    </span>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    @forelse ($workshops as $workshop)
                        @php
                            $recordingUrl = $workshop->recording_source_url;
                            $previewUrl = $workshop->video_preview_url;
                            $isDirectVideo = (bool) $workshop->is_direct_video;
                        @endphp
                        <article class="flex flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-xl ring-1 ring-slate-50 transition hover:-translate-y-1 hover:shadow-2xl">
                            <div class="relative aspect-video overflow-hidden bg-slate-100">
                                @if ($previewUrl)
                                    <iframe
                                        src="{{ $previewUrl }}"
                                        class="h-full w-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                @elseif ($isDirectVideo && $recordingUrl)
                                    <video controls preload="metadata" playsinline class="h-full w-full bg-black object-cover">
                                        <source src="{{ $recordingUrl }}">
                                        متصفحك لا يدعم تشغيل الفيديو.
                                    </video>
                                @elseif ($recordingUrl)
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900/5 p-6 text-center">
                                        <p class="text-sm font-semibold text-slate-600">التسجيل متاح عبر منصة خارجية</p>
                                        <a href="{{ $recordingUrl }}"
                                            class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                                            <i class="fas fa-right-to-bracket"></i>
                                            فتح التسجيل
                                        </a>
                                    </div>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900/5 text-sm font-semibold text-slate-500">
                                        لا يوجد تسجيل متاح
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col gap-4 p-6">
                                <div>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-orange-600">
                                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                        ورشة طهي
                                    </span>
                                    <h3 class="mt-3 text-2xl font-bold text-slate-900">{{ $workshop->title }}</h3>
                                    @if ($workshop->description)
                                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                            {{ Str::limit(strip_tags($workshop->description), 180) }}
                                        </p>
                                    @endif
                                </div>

                                <dl class="grid gap-4 text-sm text-slate-600 sm:grid-cols-2">
                                    <div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3">
                                        <i class="fas fa-calendar text-orange-500"></i>
                                        <div>
                                            <dt class="text-xs uppercase tracking-widest text-slate-400">تاريخ الورشة</dt>
                                            <dd class="font-semibold text-slate-800">
                                                {{ $workshop->formatted_start_date ?? 'سيتم التحديد لاحقاً' }}
                                            </dd>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3">
                                        <i class="fas {{ $workshop->is_online ? 'fa-wifi' : 'fa-location-dot' }} text-orange-500"></i>
                                        <div>
                                            <dt class="text-xs uppercase tracking-widest text-slate-400">وضع الحضور</dt>
                                            <dd class="font-semibold text-slate-800">
                                                {{ $workshop->is_online ? 'أونلاين' : ($workshop->location ?? 'غير محدد') }}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>

                                @if ($recordingUrl)
                                    <div class="mt-auto flex flex-wrap gap-3">
                                        <a href="{{ $recordingUrl }}"
                                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-orange-200 hover:text-orange-600">
                                            <i class="fas fa-link"></i>
                                            مشاهدة التسجيل
                                        </a>
                                        @if ($previewUrl || $isDirectVideo)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                                                <i class="fas fa-circle-play text-orange-500"></i>
                                                يعمل داخل الصفحة
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm lg:col-span-2">
                            <p class="text-2xl font-bold text-slate-800">لا توجد ورشات مسجّلة بعد</p>
                            <p class="mt-3 text-slate-600">
                                عندما يقوم {{ $chef->name }} بإضافة تسجيلات جديدة ستظهر هنا فوراً.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if ($driveRecordings->isNotEmpty())
                <div class="mt-16">
                    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">تسجيلات Google Drive</h2>
                            <p class="text-sm text-slate-500">أحدث التسجيلات القادمة مباشرة من مجلد Google Drive الخاص بالشيف</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm">
                            <i class="fas fa-cloud text-orange-500"></i>
                            {{ number_format($driveRecordings->count()) }} تسجيل
                        </span>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-2">
                        @foreach ($driveRecordings as $recording)
                            <article class="flex flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-xl ring-1 ring-slate-50 transition hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative aspect-video overflow-hidden bg-slate-900/5">
                                    @if (!empty($recording['preview_url']))
                                        <iframe
                                            src="{{ $recording['preview_url'] }}"
                                            class="h-full w-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            loading="lazy"
                                            title="Google Drive recording preview"
                                        ></iframe>
                                    @else
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900/60 p-6 text-center text-white">
                                            <p class="text-sm font-semibold">التسجيل متاح عبر Google Drive</p>
                                            <a
                                                href="{{ $recording['watch_url'] }}"
                                                target="_blank"
                                                rel="noreferrer"
                                                class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-white"
                                            >
                                                <i class="fas fa-external-link"></i>
                                                فتح التسجيل
                                            </a>
                                        </div>
                                    @endif
                                    <span class="absolute start-3 top-3 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-600">
                                        <i class="fab fa-google-drive text-orange-500"></i>
                                        Google Drive
                                    </span>
                                </div>
                                <div class="flex flex-1 flex-col gap-4 p-6">
                                    <div class="flex flex-col gap-2">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Recording</p>
                                        <h3 class="text-xl font-bold text-slate-900">{{ $recording['title'] }}</h3>
                                        <p class="text-sm text-slate-500">{{ $recording['modified_label'] }}</p>
                                    </div>
                                    <p class="text-sm leading-6 text-slate-600">
                                        {{ $recording['description'] }}
                                    </p>
                                    <div class="mt-auto flex flex-wrap items-center gap-3">
                                        <a
                                            href="{{ $recording['watch_url'] }}"
                                            target="_blank"
                                            rel="noreferrer"
                                            class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600"
                                        >
                                            <i class="fas fa-play"></i>
                                            مشاهدة الآن
                                        </a>
                                        @if (!empty($recording['preview_url']))
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                                                <i class="fas fa-circle-play text-orange-500"></i>
                                                يعمل داخل الصفحة
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection


