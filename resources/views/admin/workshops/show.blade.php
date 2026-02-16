@extends('layouts.app')

@section('title', 'تفاصيل الورشة - ' . $workshop->title)

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #f9eff1 45%, #ffffff 100%);
        min-height: 100vh;
    }

    .admin-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 35px 60px -45px rgba(30, 41, 59, 0.45);
        backdrop-filter: blur(8px);
    }

    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255,255,255,0.96) 0%, rgba(254, 243, 199, 0.94) 55%, rgba(224, 238, 255, 0.94) 100%);
        border-radius: 1.75rem;
        border: 1px solid rgba(107, 46, 48, 0.16);
        padding: 2.75rem 2.5rem;
        box-shadow: 0 35px 60px -42px rgba(107, 46, 48, 0.55);
    }

    .page-hero::before,
    .page-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: 0.4;
        pointer-events: none;
    }

    .page-hero::before {
        width: 360px;
        height: 360px;
        top: -150px;
        left: -140px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.25), transparent 65%);
    }

    .page-hero::after {
        width: 420px;
        height: 420px;
        bottom: -220px;
        right: -170px;
        background: radial-gradient(circle, rgba(107, 46, 48, 0.3), transparent 70%);
    }

    .page-hero > * {
        position: relative;
        z-index: 1;
    }

    .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.95rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.85rem;
        background: rgba(255,255,255,0.65);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: #92400e;
        backdrop-filter: blur(6px);
    }

    .hero-badge--active {
        background: rgba(16, 185, 129, 0.2);
        border-color: rgba(16, 185, 129, 0.35);
        color: #047857;
    }

    .hero-badge--inactive {
        background: rgba(248, 113, 113, 0.2);
        border-color: rgba(248, 113, 113, 0.35);
        color: #b91c1c;
    }

    .hero-badge--online {
        background: rgba(59, 130, 246, 0.22);
        border-color: rgba(59, 130, 246, 0.35);
        color: #8f4a50;
    }

    .hero-badge--featured {
        background: rgba(250, 204, 21, 0.3);
        border-color: rgba(250, 204, 21, 0.45);
        color: #4d1f22;
    }

    .hero-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1.25rem;
        margin-top: 2.5rem;
    }

    .hero-meta-card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 1.15rem;
        border: 1px solid rgba(203, 213, 225, 0.45);
        padding: 1.1rem 1.35rem;
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        min-height: 110px;
        box-shadow: 0 20px 45px -38px rgba(14, 165, 233, 0.45);
    }

    .hero-meta-label {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #64748b;
        text-transform: uppercase;
    }

    .hero-meta-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .hero-meta-sub {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .detail-heading {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.65rem;
    }

    .detail-block + .detail-block {
        border-top: 1px solid rgba(226, 232, 240, 0.7);
        padding-top: 1.35rem;
        margin-top: 1.35rem;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 9999px;
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 0.35rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
    }

    .progress-bar {
        position: relative;
        height: 0.65rem;
        background: rgba(226, 232, 240, 0.8);
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-fill {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #6b2e30, #9f5e63);
        border-radius: inherit;
        transition: width 0.4s ease;
    }

    .notification {
        position: fixed;
        top: 24px;
        right: 24px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 1.35rem;
        border-radius: 0.9rem;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 18px 30px -18px rgba(15, 23, 42, 0.25);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 1200;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .notification.error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .notification.warning {
        background: linear-gradient(135deg, #8f4a50, #4d1f22);
    }

    @media (max-width: 1024px) {
        .page-hero {
            padding: 2.25rem;
        }
    }

    @media (max-width: 768px) {
        .page-hero {
            padding: 2rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $bookingsCount = (int) ($workshop->bookings_count ?? 0);
    $maxParticipants = max((int) ($workshop->max_participants ?? 0), 1);
    $occupancy = min(100, round(($bookingsCount / $maxParticipants) * 100));
    $isOnline = (bool) $workshop->is_online;
    $isFeatured = (bool) $workshop->is_featured;
    $hasMeetingLink = $isOnline && $workshop->meeting_link;
    $currencyOptions = \App\Support\Currency::all();
    $currencyMeta = $currencyOptions[$workshop->currency] ?? [
        'label' => $workshop->currency,
        'symbol' => $workshop->currency,
    ];
@endphp
<div class="py-10 md:py-16">
    <div class="container mx-auto px-4 max-w-6xl space-y-10">
        <div class="page-hero">
            <div class="flex flex-col xl:flex-row gap-8 xl:gap-12">
                <div class="flex-1 space-y-6">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/70 text-orange-600 font-semibold text-xs tracking-widest shadow-sm uppercase">
                        <i class="fas fa-chalkboard-teacher"></i>
                        لوحة تفاصيل الورشة
                    </span>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight">
                            {{ $workshop->title }}
                        </h1>
                        <p class="mt-4 text-slate-600 leading-relaxed max-w-3xl">
                            تابع حالة الورشة، راجع محتواها، وتأكد من أن كل التفاصيل المرتبطة بالجلسات أو الوصفات محدثة قبل مشاركتها مع المتدربين.
                        </p>
                    </div>

                    <div class="hero-badges">
                        <span class="hero-badge {{ $workshop->is_active ? 'hero-badge--active' : 'hero-badge--inactive' }}">
                            <i class="fas {{ $workshop->is_active ? 'fa-bolt' : 'fa-pause-circle' }}"></i>
                            {{ $workshop->is_active ? 'الورشة نشطة' : 'الورشة غير مفعّلة' }}
                        </span>
                        <span class="hero-badge {{ $isOnline ? 'hero-badge--online' : '' }}">
                            <i class="fas {{ $isOnline ? 'fa-video' : 'fa-map-marker-alt' }}"></i>
                            {{ $isOnline ? 'ورشة أونلاين' : 'ورشة حضورية' }}
                        </span>
                        @if($isFeatured)
                            <span class="hero-badge hero-badge--featured">
                                <i class="fas fa-crown"></i>
                                الورشة المميزة الحالية
                            </span>
                        @endif
                    </div>
                </div>

                <div class="w-full xl:w-80 space-y-4">
                    <div class="hero-meta-card">
                        <span class="hero-meta-label">المدرب</span>
                        <span class="hero-meta-value">{{ $workshop->instructor }}</span>
                        <span class="hero-meta-sub">شارك ملف المدرب أو نبذة عنه أسفل الصفحة.</span>
                    </div>
                    <div class="hero-meta-card">
                        <span class="hero-meta-label">السعر</span>
                        <span class="hero-meta-value">
                            {{ number_format($workshop->price, 2) }}
                            {{ $currencyMeta['symbol'] ?? $workshop->currency }}
                            <span class="text-sm text-slate-500">({{ $workshop->currency }})</span>
                        </span>
                        <span class="hero-meta-sub">
                            {{ $currencyMeta['label'] ?? $workshop->currency }} · تأكد من أن التسعير متوافق مع المحتوى والمدة.
                        </span>
                    </div>
                </div>
            </div>

            <div class="hero-meta-grid">
                <div class="hero-meta-card">
                    <span class="hero-meta-label">تاريخ البداية</span>
                    <span class="hero-meta-value">{{ optional($workshop->start_date)->format('d/m/Y، H:i') ?? 'غير محدد' }}</span>
                    <span class="hero-meta-sub">موعد انطلاق الورشة.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">تاريخ النهاية</span>
                    <span class="hero-meta-value">{{ optional($workshop->end_date)->format('d/m/Y، H:i') ?? 'غير محدد' }}</span>
                    <span class="hero-meta-sub">خاتمة الورشة أو آخر جلسة.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">مدة الورشة</span>
                    <span class="hero-meta-value">{{ $workshop->duration ?? 0 }} دقيقة</span>
                    <span class="hero-meta-sub">الوقت الإجمالي المتوقع لكل المشاركين.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">الاشغال</span>
                    <span class="hero-meta-value">{{ $occupancy }}%</span>
                    <span class="progress-bar mt-2">
                        <span class="progress-fill" style="width: {{ $occupancy }}%"></span>
                    </span>
                    <span class="hero-meta-sub">{{ $bookingsCount }} / {{ $maxParticipants }} مقعد محجوز.</span>
                </div>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-8">
                <div class="admin-card overflow-hidden">
                    <img src="{{ $workshop->image ? asset('storage/' . $workshop->image) : 'https://placehold.co/1200x600/0f4c73/FFFFFF?text=ورشة' }}"
                         alt="{{ $workshop->title }}"
                         class="h-72 w-full object-cover" loading="lazy">
                </div>

                <div class="admin-card p-6 md:p-8">
                    <div class="detail-block">
                        <h2 class="detail-heading">وصف الورشة</h2>
                        <p class="leading-relaxed text-slate-600">{!! nl2br(e($workshop->description)) !!}</p>
                    </div>

                    <div class="detail-block">
                        <h2 class="detail-heading">الجدول والتفاصيل الزمنية</h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">البداية</span>
                                <p class="mt-2 text-slate-800 font-semibold">{{ optional($workshop->start_date)->format('d/m/Y، H:i') ?? 'غير محدد' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">النهاية</span>
                                <p class="mt-2 text-slate-800 font-semibold">{{ optional($workshop->end_date)->format('d/m/Y، H:i') ?? 'غير محدد' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">التسجيل متاح حتى</span>
                                <p class="mt-2 text-slate-800 font-semibold">{{ optional($workshop->registration_deadline)->format('d/m/Y، H:i') ?? 'لم يحدد' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">عدد المقاعد</span>
                                <p class="mt-2 text-slate-800 font-semibold">{{ $maxParticipants }} مقعد</p>
                            </div>
                        </div>
                    </div>

                    @if($workshop->content)
                        <div class="detail-block">
                            <h2 class="detail-heading">المحتوى التفصيلي</h2>
                            <div class="prose max-w-none rtl:text-right">
                                <p class="leading-relaxed text-slate-600">{!! nl2br(e($workshop->content)) !!}</p>
                            </div>
                        </div>
                    @endif

                    <div class="detail-block grid gap-4 md:grid-cols-2">
                        @if($workshop->what_you_will_learn)
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 mb-3">ما سيتعلمه المشاركون</h3>
                                <p class="text-slate-600 leading-relaxed">{!! nl2br(e($workshop->what_you_will_learn)) !!}</p>
                            </div>
                        @endif
                        @if($workshop->requirements)
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 mb-3">المتطلبات</h3>
                                <p class="text-slate-600 leading-relaxed">{!! nl2br(e($workshop->requirements)) !!}</p>
                            </div>
                        @endif
                        @if($workshop->materials_needed)
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-bold text-slate-900 mb-3">المواد والأدوات المطلوبة</h3>
                                <p class="text-slate-600 leading-relaxed">{!! nl2br(e($workshop->materials_needed)) !!}</p>
                            </div>
                        @endif
                        @if($workshop->instructor_bio)
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-bold text-slate-900 mb-3">نبذة عن المدرب</h3>
                                <p class="text-slate-600 leading-relaxed">{!! nl2br(e($workshop->instructor_bio)) !!}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="admin-card p-6 md:p-8">
                    <div class="detail-block">
                        <h2 class="detail-heading">معلومات الوصول</h2>
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <span class="chip">
                                    <i class="fas {{ $isOnline ? 'fa-video' : 'fa-store' }} text-orange-400"></i>
                                    {{ $isOnline ? 'جلسة أونلاين' : 'جلسة حضورية' }}
                                </span>
                                <span class="chip">
                                    <i class="fas fa-clock text-orange-400"></i>
                                    {{ $workshop->duration ?? 0 }} دقيقة</span>
                                <span class="chip">
                                    <i class="fas fa-users text-orange-400"></i>
                                    {{ $bookingsCount }} حجز مؤكد</span>
                            </div>

                            @if(!$isOnline && $workshop->location)
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 mb-2">الموقع</h3>
                                    <p class="text-slate-600 leading-relaxed">{{ $workshop->location }}</p>
                                    @if($workshop->address)
                                        <p class="text-xs text-slate-500 mt-1">{{ $workshop->address }}</p>
                                    @endif
                                </div>
                            @endif

                            @if($hasMeetingLink)
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 mb-2">رابط الاجتماع</h3>
                                    <div class="flex flex-col gap-3">
                                        <code class="truncate rounded-xl bg-slate-100 px-4 py-2 text-sm text-slate-700">{{ $workshop->meeting_link }}</code>
                                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5"
                                                data-copy-target="{{ $workshop->meeting_link }}">
                                            <i class="fas fa-copy"></i>
                                            نسخ الرابط للمشاركين
                                        </button>
                                        @if($workshop->meeting_provider === 'google_meet')
                                            <a href="{{ route('admin.workshops.meeting', $workshop) }}"
                                               class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-400 to-sky-500 px-4 py-2 text-sm font-semibold text-slate-900 shadow-md transition hover:-translate-y-0.5">
                                                <i class="fas fa-user-shield"></i>
                                                فتح غرفة التحكم الإداري
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="admin-card p-6 md:p-8">
                    <div class="detail-block">
                        <h2 class="detail-heading">الوصفات المرفقة</h2>
                        @if($workshop->recipes && $workshop->recipes->count())
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach($workshop->recipes as $recipe)
                                    <div class="rounded-2xl border border-slate-100 bg-white/90 p-4 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ $recipe->image_url ?: 'https://placehold.co/80x80/0f4c73/FFFFFF?text=وصفة' }}"
                                                 alt="{{ $recipe->title }}"
                                                 class="h-16 w-16 rounded-xl border border-slate-200 object-cover"
                                                onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                                            <div class="space-y-1">
                                                <p class="text-sm font-bold text-slate-900">{{ $recipe->title }}</p>
                                                <p class="text-xs text-slate-500">{{ $recipe->author }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-500">لم يتم ربط أية وصفات بهذه الورشة حتى الآن.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="admin-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">ملخص سريع</h3>
                        <a href="{{ route('admin.workshops.index') }}" class="text-sm font-semibold text-orange-500 hover:text-orange-600">
                            <i class="fas fa-arrow-right ml-2"></i>عودة للقائمة
                        </a>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">تاريخ الإنشاء</span>
                            <span class="font-semibold text-slate-800">{{ optional($workshop->created_at)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">آخر تحديث</span>
                            <span class="font-semibold text-slate-800">{{ optional($workshop->updated_at)->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">الفئة</span>
                            <span class="font-semibold text-slate-800">{{ $workshop->category ?: 'غير محددة' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">المستوى</span>
                            <span class="font-semibold text-slate-800">
                                @switch($workshop->level)
                                    @case('beginner') مبتدئ @break
                                    @case('intermediate') متوسط @break
                                    @case('advanced') متقدم @break
                                    @default غير محدد
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>

                <div class="admin-card p-6">
                    <h3 class="text-lg font-bold text-slate-900">الحجوزات</h3>
                    <div class="mt-6 space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">الحجوزات المؤكدة</span>
                            <span class="font-semibold text-slate-900">{{ $bookingsCount }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">المقاعد المتاحة</span>
                            <span class="font-semibold text-slate-900">{{ max($maxParticipants - $bookingsCount, 0) }}</span>
                        </div>
                        <div class="progress-bar">
                            <span class="progress-fill" style="width: {{ $occupancy }}%"></span>
                        </div>
                        <p class="text-xs text-slate-500 text-center">{{ $occupancy }}% من الورشة ممتلئ حالياً.</p>
                    </div>
                </div>

                <div class="admin-card p-6 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">الإجراءات</h3>
                    <a href="{{ route('admin.workshops.edit', $workshop->id) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5">
                        <i class="fas fa-edit"></i>
                        تعديل الورشة
                    </a>

                    <form action="{{ route('admin.workshops.toggle-status', $workshop->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 {{ $workshop->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-emerald-500 hover:bg-emerald-600' }}">
                            <i class="fas {{ $workshop->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                            {{ $workshop->is_active ? 'إلغاء تفعيل الورشة' : 'تفعيل الورشة' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.workshops.destroy', $workshop->id) }}" method="POST"
                          class="delete-workshop-form"
                          data-workshop-title="{{ $workshop->title }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                            حذف الورشة
                        </button>
                    </form>
</div>
</div>
</div>
</div>
</div>
@endsection

@include('admin.workshops.partials.delete-confirm-script')

@push('styles')
    @include('admin.workshops.partials.swal-styles')
@endpush

@push('scripts')
<script>
function showNotification(message, type = 'success') {
    document.querySelectorAll('.notification').forEach(el => el.remove());

    const icon = type === 'error' ? 'fa-exclamation-triangle' : (type === 'warning' ? 'fa-info-circle' : 'fa-check');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;

    document.body.appendChild(notification);
    requestAnimationFrame(() => notification.classList.add('show'));

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3200);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button[data-copy-target]').forEach(button => {
        button.addEventListener('click', async () => {
            const text = button.getAttribute('data-copy-target');
            if (!text) {
                return;
            }
            try {
                await navigator.clipboard.writeText(text);
                showNotification('تم نسخ رابط الاجتماع بنجاح!', 'success');
            } catch (error) {
                console.error(error);
                showNotification('تعذّر نسخ الرابط. يرجى المحاولة يدوياً.', 'error');
            }
        });
    });
});
</script>
@endpush












