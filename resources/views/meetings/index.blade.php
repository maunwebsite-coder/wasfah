@extends('layouts.app')

@section('title', 'اجتماعاتي')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-orange-50 via-white to-white py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-orange-100 bg-white px-6 py-10 shadow-xl">
                <div class="absolute -left-20 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-orange-100/40 blur-3xl"></div>
                <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-amber-100/40 blur-3xl"></div>

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.4em] text-orange-500">اجتماعاتك</p>
                        <h1 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">كل الغرف التي يمكنك دخولها</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600">
                            يجمع هذا القسم كل الاجتماعات المرتبطة بحسابك سواء كنت المضيف أو أحد المشاركين.
                            اختر الاجتماع المناسب للانضمام مباشرةً أو لمتابعة حالة الغرفة.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 text-sm text-slate-500">
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                            <i class="fas fa-clock text-orange-500"></i>
                            {{ Carbon::now()->locale('ar')->translatedFormat('d F Y • h:i a') }}
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                            <i class="fas fa-user text-orange-500"></i>
                            {{ $user->name }}
                        </div>
                    </div>
                </div>
            </div>

            @php
                $canManageWorkshops = $isAdmin || (method_exists($user, 'isChef') && $user->isChef());
            @endphp

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg backdrop-blur">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">بصفتي المضيف</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                ورش العمل الأونلاين التي تملك صلاحية تشغيلها أو إدارتها.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                            {{ $hostWorkshops->count() }} اجتماع
                        </span>
                    </div>

                    @if ($hostWorkshops->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                            لا توجد غرف متاحة لك كمضيف حالياً.
                            @if ($canManageWorkshops)
                                يمكنك إنشاء ورشة جديدة من لوحة الشيف.
                                <div class="mt-4">
                                    <a href="{{ route('chef.workshops.index') }}" class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                                        <i class="fas fa-plus-circle text-xs"></i>
                                        إدارة ورش الشيف
                                    </a>
                                </div>
                            @else
                                حالياً لا تمتلك صلاحية استضافة ورش، لكن يمكنك المشاركة في الورش من القسم المجاور.
                            @endif
                        </div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($hostWorkshops as $workshop)
                                @php
                                    $roomOpen = (bool) $workshop->meeting_started_at;
                                    $badgeClasses = $roomOpen
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-amber-100 text-amber-700';
                                    $statusLabel = $roomOpen ? 'الغرفة مفتوحة' : 'بانتظار فتح الغرفة';
                                    $startsAt = $workshop->start_date
                                        ? $workshop->start_date->locale('ar')->translatedFormat('d F Y • h:i a')
                                        : 'موعد مرن';
                                @endphp
                                <article class="rounded-2xl border border-slate-200 bg-slate-900 text-slate-100 shadow-lg overflow-hidden">
                                    <div class="border-b border-white/10 bg-slate-900/90 px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <h3 class="text-lg font-semibold text-white">{{ $workshop->title }}</h3>
                                                <p class="mt-1 text-xs text-slate-300">
                                                    @if ($workshop->chef && $workshop->chef->id !== $user->id)
                                                        مستضيف بواسطة {{ $workshop->chef->name }}
                                                    @else
                                                        أنت المضيف الرئيسي
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                                <i class="fas {{ $roomOpen ? 'fa-broadcast-tower' : 'fa-hourglass-half' }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="px-6 py-5 space-y-3 text-sm">
                                        <div class="flex flex-wrap items-center gap-3 text-slate-300">
                                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1">
                                                <i class="fas fa-calendar-alt text-orange-300"></i>
                                                {{ $startsAt }}
                                            </span>
                                            @if ($workshop->meeting_started_at)
                                                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1">
                                                    <i class="fas fa-clock text-emerald-300"></i>
                                                    فتح منذ {{ $workshop->meeting_started_at->locale('ar')->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            <a
                                                href="{{ route('chef.workshops.join', $workshop) }}"
                                                class="inline-flex items-center gap-2 rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-900 shadow transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                                            >
                                                <i class="fas fa-video"></i>
                                                دخول غرفة المضيف
                                            </a>
                                            <a
                                                href="{{ route('chef.workshops.index') }}"
                                                class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10"
                                            >
                                                <i class="fas fa-cog"></i>
                                                إدارة الورشة
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg backdrop-blur">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">كمشارك</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                كل الحجوزات المؤكدة أو المنتظرة التي تتيح لك الانضمام كمتعلم.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            {{ $participantBookings->count() }} حجز
                        </span>
                    </div>

                    @if ($participantBookings->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                            لم يتم العثور على حجوزات مرتبطة بحسابك حالياً. يمكنك استكشاف الورش المتاحة وحجز مقعدك.
                            <div class="mt-4">
                                <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-600">
                                    <i class="fas fa-search text-xs"></i>
                                    استكشاف ورش جديدة
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($participantBookings as $booking)
                                @php
                                    $workshop = $booking->workshop;
                                    $status = $booking->status;
                                    $statusClasses = [
                                        'confirmed' => ['bg' => 'bg-emerald-100 text-emerald-700', 'label' => 'مؤكد'],
                                        'pending' => ['bg' => 'bg-amber-100 text-amber-700', 'label' => 'بانتظار التأكيد'],
                                        'cancelled' => ['bg' => 'bg-rose-100 text-rose-700', 'label' => 'ملغي'],
                                    ];
                                    $statusInfo = $statusClasses[$status] ?? ['bg' => 'bg-slate-100 text-slate-600', 'label' => $status];
                                    $canJoin = $status === 'confirmed' && $workshop;
                                    $startsAt = $workshop && $workshop->start_date
                                        ? $workshop->start_date->locale('ar')->translatedFormat('d F Y • h:i a')
                                        : 'موعد مرن';
                                @endphp
                                <article class="rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-900">{{ $workshop?->title ?? 'ورشة غير متاحة' }}</h3>
                                                @if ($workshop?->chef)
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        مع {{ $workshop->chef->name }}
                                                    </p>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusInfo['bg'] }}">
                                                <i class="fas {{ $status === 'confirmed' ? 'fa-badge-check' : ($status === 'pending' ? 'fa-hourglass-half' : 'fa-ban') }}"></i>
                                                {{ $statusInfo['label'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="px-6 py-5 space-y-3 text-sm text-slate-600">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                                <i class="fas fa-calendar-alt text-indigo-500"></i>
                                                {{ $startsAt }}
                                            </span>
                                            @if ($booking->confirmed_at)
                                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                                    <i class="fas fa-check-circle text-emerald-500"></i>
                                                    مؤكدة منذ {{ $booking->confirmed_at->locale('ar')->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            <a
                                                href="{{ route('bookings.show', $booking) }}"
                                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                تفاصيل الحجز
                                            </a>

                                            @if ($canJoin)
                                                <a
                                                    href="{{ $booking->secure_join_url }}"
                                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400"
                                                >
                                                    <i class="fas fa-video"></i>
                                                    دخول غرفة المشاركة
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-400">
                                                    <i class="fas fa-lock"></i>
                                                    سيتاح الانضمام بعد تأكيد الحجز
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

