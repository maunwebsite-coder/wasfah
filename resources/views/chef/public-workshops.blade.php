@extends('layouts.app')

@section('title', 'ورشات ' . ($chef->name ?? 'الشيف'))

@php
    use Illuminate\Support\Str;

    $publicHandle = $chef->username
        ?? ($chef->slug ?? Str::slug($chef->name ?? 'chef', '-'));
    $recordingStats = $recordingStats ?? [];
    $workshopsCount = $workshops->count();
    $recordingsTotal = max(0, (int) data_get($recordingStats, 'total', $workshopsCount));
    $recordingsInline = max(
        0,
        (int) data_get(
            $recordingStats,
            'inline',
            $workshops->filter(function ($workshop) {
                return ! empty($workshop->video_preview_url) || (bool) $workshop->is_direct_video;
            })->count()
        )
    );
    $recordingsDrive = max(0, (int) data_get($recordingStats, 'drive', $driveRecordings->count()));
    $breadcrumbs = [
        ['label' => __('breadcrumbs.home'), 'url' => route('home')],
        ['label' => __('breadcrumbs.chef.area'), 'url' => route('chef.dashboard')],
        ['label' => __('breadcrumbs.chef.sections.public'), 'url' => null],
        ['label' => __('breadcrumbs.workshops'), 'url' => null],
    ];
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 space-y-6">
            <section class="relative overflow-hidden rounded-3xl border border-orange-100 bg-gradient-to-br from-orange-50 via-white to-white p-6 md:p-8 shadow-sm">
                <div class="absolute left-0 top-0 h-32 w-32 -translate-x-1/2 -translate-y-1/2 rounded-full bg-orange-200/30 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-40 w-40 translate-x-1/3 translate-y-1/3 rounded-full bg-orange-100/40 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div class="h-24 w-24 overflow-hidden rounded-full bg-white shadow-lg ring-4 ring-white">
                            <img src="{{ $avatarUrl }}" alt="صورة {{ $chef->name }}" class="h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="space-y-2 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-3 py-1 font-semibold text-orange-600">
                                <i class="fas fa-play-circle text-xs"></i>
                                تسجيلات الشيف
                            </span>
                            @if ($publicHandle)
                                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 font-semibold text-gray-700">
                                    <i class="fa-solid fa-at text-orange-500"></i>
                                    <span dir="ltr">{{ '@' . $publicHandle }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 space-y-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">مكتبة التسجيلات</p>
                                <h1 class="text-3xl font-extrabold text-gray-900 md:text-4xl">ورشات {{ $chef->name }}</h1>
                                <p class="text-sm text-gray-600">عرض مبسط لجميع التسجيلات مع إمكانية المشاهدة داخل الصفحة أو عبر روابط مباشرة.</p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm font-semibold text-gray-700">
                                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1">
                                    <i class="fas fa-list text-xs text-orange-500"></i>
                                    {{ number_format($workshopsCount) }} ورشة
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1">
                                    <i class="fas fa-circle-play text-xs text-emerald-500"></i>
                                    {{ number_format($recordingsInline) }} يعمل داخل الصفحة
                                </span>
                                @if ($recordingsDrive > 0)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1">
                                        <i class="fab fa-google-drive text-xs text-sky-600"></i>
                                        {{ number_format($recordingsDrive) }} من Google Drive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-inner">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">إجمالي التسجيلات</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($recordingsTotal) }}</p>
                                <p class="text-sm text-gray-500">كل التسجيلات المنشورة للشيف في مكان واحد.</p>
                            </div>
                            <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-inner">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">تشغيل فوري</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($recordingsInline) }}</p>
                                <p class="text-sm text-gray-500">تسجيلات يمكن مشاهدتها مباشرة دون فتح صفحة جديدة.</p>
                            </div>
                            <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-inner">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">روابط موثقة</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($recordingsDrive) }}</p>
                                <p class="text-sm text-gray-500">تسجيلات مرفوعة على Google Drive بوضوح المصدر.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-100 bg-white p-6 md:p-8 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">التسجيلات الكاملة</p>
                        <h2 class="text-2xl font-bold text-gray-900">كل ورشات {{ $chef->name }}</h2>
                        <p class="text-sm text-gray-500">بطاقات بسيطة تعرض تفاصيل الورشة وطريقة مشاهدتها.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm font-semibold text-gray-700">
                        <i class="fas fa-film text-xs text-orange-500"></i>
                        {{ number_format($workshopsCount) }} ورشة
                    </span>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    @forelse ($workshops as $workshop)
                        @php
                            $recordingUrl = $workshop->recording_source_url;
                            $previewUrl = $workshop->video_preview_url;
                            $isDirectVideo = (bool) $workshop->is_direct_video;
                            $inlinePlayable = $previewUrl || $isDirectVideo;
                        @endphp
                        <article class="flex h-full flex-col gap-4 rounded-2xl border border-gray-100 bg-gray-50/60 p-4 shadow-inner">
                            <div class="relative aspect-video overflow-hidden rounded-xl bg-slate-900/80">
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
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900/70 p-5 text-center text-white">
                                        <p class="text-sm font-semibold">التسجيل متاح عبر رابط خارجي</p>
                                        <a
                                            href="{{ $recordingUrl }}"
                                            class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm transition hover:bg-gray-100"
                                        >
                                            <i class="fas fa-external-link-alt"></i>
                                            فتح التسجيل
                                        </a>
                                    </div>
                                @else
                                    <div class="absolute inset-0 grid place-items-center bg-slate-100 text-sm font-semibold text-gray-500">
                                        لا يوجد تسجيل متاح بعد
                                    </div>
                                @endif

                                <div class="absolute start-3 top-3 flex flex-wrap gap-2 text-xs">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 font-semibold text-gray-800 shadow-sm">
                                        <i class="fa-solid {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                        {{ $workshop->is_online ? 'أونلاين' : 'حضوري' }}
                                    </span>
                                    @if ($workshop->recording_from_drive ?? false)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 font-semibold text-sky-700 shadow-sm">
                                            <i class="fab fa-google-drive"></i>
                                            Google Drive
                                        </span>
                                    @endif
                                    @if ($inlinePlayable)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 font-semibold text-emerald-700 shadow-sm">
                                            <i class="fa-solid fa-circle-play"></i>
                                            يعمل داخل الصفحة
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col gap-4">
                                <div class="space-y-2">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $workshop->title }}</h3>
                                    @if ($workshop->description)
                                        <p class="text-sm leading-relaxed text-gray-600">
                                            {{ Str::limit(strip_tags($workshop->description), 170) }}
                                        </p>
                                    @endif
                                </div>

                                <div class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-xl border border-white/80 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">تاريخ الورشة</p>
                                        <p class="mt-1 font-semibold text-gray-900">
                                            {{ $workshop->formatted_start_date ?? 'سيتم التحديد لاحقاً' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl border border-white/80 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">وضع الحضور</p>
                                        <p class="mt-1 font-semibold text-gray-900">
                                            {{ $workshop->is_online ? 'أونلاين' : ($workshop->location ?? 'غير محدد') }}
                                        </p>
                                    </div>
                                </div>

                                @if ($recordingUrl)
                                    <div class="mt-auto flex flex-wrap gap-3 text-sm font-semibold">
                                        <a href="{{ $recordingUrl }}"
                                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-1 focus:ring-offset-white">
                                            <i class="fas fa-link"></i>
                                            مشاهدة التسجيل
                                        </a>
                                        @if ($inlinePlayable)
                                            <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1 text-gray-700">
                                                <i class="fas fa-circle-play text-orange-500"></i>
                                                يعمل داخل الصفحة
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-auto rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-600">
                                        سيتم إضافة التسجيل فور توفره
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-10 text-center text-gray-700 lg:col-span-2">
                            <p class="text-xl font-bold text-gray-800">لا توجد ورشات مسجّلة بعد</p>
                            <p class="mt-2 text-sm text-gray-600">عندما يقوم {{ $chef->name }} بإضافة تسجيلات جديدة ستظهر هنا فوراً.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            @if ($driveRecordings->isNotEmpty())
                <section class="rounded-3xl border border-gray-100 bg-white p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">Google Drive</p>
                            <h2 class="text-2xl font-bold text-gray-900">تسجيلات السحابة</h2>
                            <p class="text-sm text-gray-500">أحدث التسجيلات القادمة مباشرة من مجلد Google Drive الخاص بالشيف.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm font-semibold text-gray-700">
                            <i class="fab fa-google-drive text-xs text-orange-500"></i>
                            {{ number_format($driveRecordings->count()) }} تسجيل
                        </span>
                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        @foreach ($driveRecordings as $recording)
                            <article class="flex h-full flex-col gap-4 rounded-2xl border border-gray-100 bg-gray-50/60 p-4 shadow-inner">
                                <div class="relative aspect-video overflow-hidden rounded-xl bg-slate-900/80">
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
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900/70 p-5 text-center text-white">
                                            <p class="text-sm font-semibold">التسجيل متاح عبر Google Drive</p>
                                            <a
                                                href="{{ $recording['watch_url'] }}"
                                                target="_blank"
                                                rel="noreferrer"
                                                class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm transition hover:bg-gray-100"
                                            >
                                                <i class="fas fa-external-link"></i>
                                                فتح التسجيل
                                            </a>
                                        </div>
                                    @endif

                                    <div class="absolute start-3 top-3 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 font-semibold text-sky-700 shadow-sm">
                                            <i class="fab fa-google-drive"></i>
                                            Google Drive
                                        </span>
                                        @if (!empty($recording['matched_code']))
                                            <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 font-semibold text-orange-700 shadow-sm">
                                                <i class="fa-solid fa-hashtag"></i>
                                                {{ $recording['matched_code'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col gap-3">
                                    <div class="space-y-1">
                                        <h3 class="text-lg font-bold text-gray-900">{{ $recording['title'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $recording['modified_label'] }}</p>
                                    </div>
                                    <p class="text-sm leading-6 text-gray-600">
                                        {{ $recording['description'] }}
                                    </p>
                                    <div class="mt-auto flex flex-wrap gap-3 text-sm font-semibold">
                                        <a
                                            href="{{ $recording['watch_url'] }}"
                                            target="_blank"
                                            rel="noreferrer"
                                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-1 focus:ring-offset-white"
                                        >
                                            <i class="fas fa-play"></i>
                                            مشاهدة الآن
                                        </a>
                                        @if (!empty($recording['preview_url']))
                                            <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1 text-gray-700">
                                                <i class="fas fa-circle-play text-orange-500"></i>
                                                يعمل داخل الصفحة
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
