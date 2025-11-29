@extends('layouts.app')

@section('title', 'إضافة ورشة جديدة - لوحة الإدارة')

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 40%, #f4f6ff 100%);
        min-height: 100vh;
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
        background: linear-gradient(135deg, #1296d9, #050f9f);
    }

    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(244, 246, 255, 0.95) 55%, rgba(224, 238, 255, 0.95) 100%);
        border-radius: 1.75rem;
        border: 1px solid rgba(8, 25, 255, 0.16);
        padding: 2.75rem 2.5rem;
        box-shadow: 0 30px 60px -40px rgba(8, 25, 255, 0.6);
    }

    .page-hero::before,
    .page-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: 0.45;
        pointer-events: none;
    }

    .page-hero::before {
        width: 340px;
        height: 340px;
        top: -130px;
        left: -120px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.25), transparent 65%);
    }

    .page-hero::after {
        width: 420px;
        height: 420px;
        bottom: -180px;
        right: -150px;
        background: radial-gradient(circle, rgba(8, 25, 255, 0.28), transparent 68%);
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

    .hero-badge i {
        font-size: 0.95rem;
    }

    .hero-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
        margin-top: 2.5rem;
    }

    .hero-meta-card {
        background: rgba(255, 255, 255, 0.85);
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

    .admin-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1.75rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 35px 60px -45px rgba(30, 41, 59, 0.55);
        backdrop-filter: blur(10px);
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .section-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #fff;
        background: linear-gradient(135deg, #0819ff 0%, #0f78ad 100%);
        box-shadow: 0 12px 30px -20px rgba(8, 25, 255, 0.7);
    }

    .section-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #1f2937;
    }

    .section-description {
        margin-top: 0.4rem;
        font-size: 0.95rem;
        color: #64748b;
        line-height: 1.8;
        max-width: 45rem;
    }

    .section-body {
        margin-top: 1.8rem;
    }

    .section-divider {
        height: 1px;
        width: 100%;
        background: linear-gradient(90deg, rgba(8, 25, 255, 0.1), rgba(148, 163, 184, 0.45), rgba(8, 25, 255, 0.1));
        margin: 2.5rem 0;
    }

    .form-label {
        display: block;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.6rem;
        font-size: 0.95rem;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 0.9rem;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        transition: all 0.25s ease;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #0819ff;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(8, 25, 255, 0.12);
        transform: translateY(-1px);
    }

    .form-textarea {
        resize: vertical;
        min-height: 160px;
    }

    .form-hint {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 0.35rem;
    }

    .is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }

    .error-text {
        color: #ef4444;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.4rem;
    }

    .image-upload-area {
        border: 2px dashed rgba(148, 163, 184, 0.6);
        border-radius: 1.25rem;
        padding: 2.5rem;
        text-align: center;
        background: rgba(255, 255, 255, 0.92);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .image-upload-area:hover {
        border-color: rgba(8, 25, 255, 0.55);
        background: #f4f6ff;
        transform: translateY(-2px);
    }

    .image-upload-area.has-image {
        border-color: rgba(16, 185, 129, 0.45);
        background-color: #ecfdf5;
    }

    .upload-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #0819ff, #1296d9);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 18px 30px -25px rgba(8, 25, 255, 0.75);
        margin-top: 1rem;
    }

    .image-preview-card {
        margin-top: 1.5rem;
        display: inline-block;
        position: relative;
    }

    .image-preview-card img {
        width: 160px;
        height: 160px;
        border-radius: 1.1rem;
        object-fit: cover;
        border: 2px solid rgba(8, 25, 255, 0.4);
        box-shadow: 0 20px 40px -25px rgba(8, 25, 255, 0.6);
    }

    .remove-preview-btn {
        position: absolute;
        top: -12px;
        left: -12px;
        width: 34px;
        height: 34px;
        border-radius: 9999px;
        background: rgba(239, 68, 68, 0.9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px -12px rgba(239, 68, 68, 0.65);
        transition: transform 0.2s ease;
    }

    .remove-preview-btn:hover {
        transform: scale(1.05);
    }

    .recipe-wrapper {
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255, 237, 213, 0.9));
        border-radius: 1.5rem;
        border: 1px solid rgba(8, 25, 255, 0.18);
        padding: 2rem;
        box-shadow: 0 40px 60px -50px rgba(8, 25, 255, 0.6);
    }

    .recipe-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .recipe-search {
        flex: 1 1 260px;
        display: flex;
        align-items: center;
        background: rgba(255,255,255,0.92);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 0.9rem;
        padding: 0.5rem 0.85rem;
        gap: 0.65rem;
    }

    .recipe-search input {
        border: none;
        background: transparent;
        width: 100%;
        font-size: 0.95rem;
        color: #0f172a;
        outline: none;
    }

    .recipe-tools button {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 600;
        border-radius: 0.85rem;
        padding: 0.65rem 1.15rem;
        transition: all 0.2s ease;
    }

    .recipe-tools .select-all-btn {
        background: linear-gradient(135deg, #0819ff, #0f78ad);
        color: #fff;
        box-shadow: 0 18px 30px -25px rgba(8, 25, 255, 0.75);
    }

    .recipe-tools .clear-btn {
        background: rgba(148, 163, 184, 0.18);
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.25);
    }

    .recipes-grid {
        display: grid;
        gap: 1.2rem;
        max-height: 28rem;
        overflow-y: auto;
        padding-right: 0.35rem;
    }

    @media (min-width: 768px) {
        .recipes-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) {
        .recipes-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .recipe-item {
        position: relative;
        background: rgba(255,255,255,0.92);
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        padding: 1rem;
        transition: all 0.25s ease;
        box-shadow: 0 22px 40px -36px rgba(15, 23, 42, 0.45);
    }

    .recipe-item:hover {
        border-color: rgba(8, 25, 255, 0.45);
        transform: translateY(-3px);
    }

    .recipe-item.selected {
        border-color: rgba(8, 25, 255, 0.65);
        background: #f4f6ff;
        box-shadow: 0 26px 45px -32px rgba(8, 25, 255, 0.6);
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .status-card {
        position: relative;
        display: block;
    }

    .status-card input {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
    }

    .status-card-content {
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.1rem;
        background: rgba(255,255,255,0.92);
        padding: 1.05rem 1.15rem;
        display: flex;
        gap: 0.85rem;
        transition: all 0.25s ease;
        align-items: center;
        min-height: 110px;
        box-shadow: 0 20px 40px -38px rgba(15, 23, 42, 0.35);
    }

    .status-card-icon {
        width: 2.65rem;
        height: 2.65rem;
        border-radius: 0.9rem;
        background: linear-gradient(135deg, #0819ff, #0f78ad);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        box-shadow: 0 18px 24px -18px rgba(8, 25, 255, 0.75);
        flex-shrink: 0;
    }

    .status-card input:checked + .status-card-content {
        border-color: rgba(8, 25, 255, 0.55);
        background: #f4f6ff;
        transform: translateY(-2px);
        box-shadow: 0 28px 40px -28px rgba(8, 25, 255, 0.55);
    }

    .status-card input:checked + .status-card-content .status-card-icon {
        background: linear-gradient(135deg, #0819ff, #1ab8ff);
        box-shadow: 0 20px 30px -28px rgba(8, 25, 255, 0.6);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(226, 232, 240, 0.6);
        margin-top: 2.5rem;
    }

    .button-secondary,
    .button-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        border-radius: 0.9rem;
        padding: 0.95rem 2.25rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .button-secondary {
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(148, 163, 184, 0.4);
        color: #1f2937;
    }

    .button-secondary:hover {
        background: rgba(226, 232, 240, 0.55);
        transform: translateY(-2px);
    }

    .button-primary {
        background: linear-gradient(135deg, #0819ff 0%, #0f78ad 50%, #f472b6 100%);
        color: #fff;
        box-shadow: 0 28px 45px -28px rgba(8, 25, 255, 0.65);
        border: none;
    }

    .button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 32px 45px -25px rgba(8, 25, 255, 0.8);
    }

    .button-primary:disabled {
        opacity: 0.75;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
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

    .online-meeting-tools {
        border-radius: 1.25rem;
        border: 1px solid rgba(16, 185, 129, 0.18);
        background: rgba(236, 253, 245, 0.65);
        padding: 1rem 1.25rem;
    }

    .meeting-generator-card {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(16, 185, 129, 0.12);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        box-shadow: 0 18px 32px -25px rgba(5, 150, 105, 0.6);
    }

    .generator-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        justify-content: space-between;
    }

    .meeting-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        background: #e2e8f0;
        color: #0f172a;
    }

    .meeting-status[data-state="ready"] {
        background: #ecfdf5;
        color: #047857;
    }

    .meeting-status[data-state="manual"] {
        background: #e3ecff;
        color: #92400e;
    }

    .meeting-status[data-state="error"] {
        background: #fee2e2;
        color: #b91c1c;
    }

    .generate-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 999px;
        padding: 0.6rem 1.3rem;
        background: linear-gradient(135deg, #10b981, #0d9488);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 18px 32px -18px rgba(16, 185, 129, 0.8);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .generate-link-btn:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 34px -20px rgba(16, 185, 129, 0.9);
    }

    .generate-link-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .meeting-info-card {
        border-radius: 1rem;
        border: 1px dashed rgba(16, 185, 129, 0.4);
        background: rgba(236, 253, 245, 0.75);
        padding: 0.95rem 1.1rem;
    }

    .readonly-input {
        background: #f1f5f9;
        color: #475569;
    }
</style>
@endpush
@section('content')
<div class="py-10 md:py-16">
    <div class="container mx-auto px-4 max-w-6xl space-y-10">
        @php
            $recipesCount = $recipes->count();
            $isOnlineOld = old('is_online', false) ? true : false;
            $forceAutoMeetingLinks = $forceAutoMeetingLinks ?? false;
            $autoGenerateMeeting = $forceAutoMeetingLinks
                ? true
                : (int) old('auto_generate_meeting', 1) === 1;
            $storedMeetingLink = trim((string) old('meeting_link'));
            $hasGeneratedMeeting = $storedMeetingLink !== '';
            $meetingStatusState = !$isOnlineOld
                ? 'idle'
                : ($hasGeneratedMeeting
                    ? 'ready'
                    : ($autoGenerateMeeting ? 'idle' : 'manual'));
            $meetingStatusText = !$isOnlineOld
                ? 'قم بتفعيل الورشة الأونلاين للوصول إلى توليد الروابط.'
                        : ($hasGeneratedMeeting
                            ? 'تم إنشاء رابط Google Meet جاهز.'
                            : ($autoGenerateMeeting
                                ? 'سيتم توليد رابط Google Meet تلقائياً بعد الحفظ.'
                                : 'أدخل رابط الاجتماع المخصص يدوياً.'));
            $currencyOptions = \App\Support\Currency::all();
            $defaultCurrency = \App\Support\Currency::default();
        @endphp

        <div class="page-hero">
            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                <div class="flex-1 space-y-6">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/70 text-orange-600 font-semibold text-xs tracking-widest shadow-sm uppercase">
                        <i class="fas fa-chalkboard-teacher"></i>
                        لوحة إنشاء الورشة
                    </span>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-900">
                            ابدأ بإضافة ورشة جديدة إلى وصفة
                        </h1>
                        <p class="mt-4 text-slate-600 leading-relaxed max-w-3xl">
                            صممنا هذه الواجهة لتساعدك على إعداد ورشة مميزة خلال دقائق، مع أقسام واضحة تغطي كل ما يحتاجه فريقك من معلومات أساسية، محتوى تعليمي، وخيارات أونلاين وحضوري.
                        </p>
                    </div>

                    <div class="hero-badges">
                        <span class="hero-badge">
                            <i class="fas fa-magic"></i>
                            تصميم متدرج يقودك خطوة بخطوة
                        </span>
                        <span class="hero-badge">
                            <i class="fas fa-video"></i>
                            دعم كامل لورش الأونلاين والحضورية
                        </span>
                        <span class="hero-badge">
                            <i class="fas fa-utensils"></i>
                            أربط الوصفات المميزة مع كل ورشة
                        </span>
                    </div>
                </div>

                <div class="w-full lg:w-72 space-y-4">
                    <div class="hero-meta-card">
                        <span class="hero-meta-label">ابدأ من هنا</span>
                        <span class="hero-meta-value">املأ البيانات الأساسية</span>
                        <span class="hero-meta-sub">العنوان، المدرب، الفئة والمستوى تحدد هوية الورشة.</span>
                    </div>
                    <div class="hero-meta-card">
                        <span class="hero-meta-label">نصيحة سريعة</span>
                        <span class="hero-meta-value">جهز المحتوى التدريبي</span>
                        <span class="hero-meta-sub">اكتب ما سيتعلمه المشاركون لتسويق الورشة بسهولة.</span>
                    </div>
                </div>
            </div>

            <div class="hero-meta-grid">
                <div class="hero-meta-card">
                    <span class="hero-meta-label">الوصفات المتاحة</span>
                    <span class="hero-meta-value">{{ $recipesCount }}</span>
                    <span class="hero-meta-sub">يمكنك اختيار أي وصفة لإرفاقها بالورشة الجديدة.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">أنماط الورشة</span>
                    <span class="hero-meta-value">أونلاين أو حضورية</span>
                    <span class="hero-meta-sub">بدّل بينهما بسهولة مع الحقول الذكية المتغيرة.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">المدة المقترحة</span>
                    <span class="hero-meta-value">30 - 240 دقيقة</span>
                    <span class="hero-meta-sub">حدد زمن الجلسة لتوقعات أوضح للمشاركين.</span>
                </div>
                <div class="hero-meta-card">
                    <span class="hero-meta-label">التجربة المثالية</span>
                    <span class="hero-meta-value">معلومات دقيقة</span>
                    <span class="hero-meta-sub">كلما كانت البيانات أوضح زادت ثقة المشتركين.</span>
                </div>
            </div>
        </div>

        <div class="admin-card p-6 md:p-10">
            <form id="workshop-create-form" action="{{ route('admin.workshops.store') }}" method="POST" enctype="multipart/form-data" class="space-y-12">
                @csrf

                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <div>
                            <h2 class="section-title">البيانات الأساسية</h2>
                            <p class="section-description">
                                تبدأ الورشة من هنا: حدد العنوان، المدرب، الفئة والمستوى لتظهر الورشة بالشكل الصحيح في المنصة.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        @php
                            $defaultTimezone = old(
                                'host_timezone',
                                auth()->user()->timezone
                                    ?? request()->cookie('user_timezone')
                                    ?? \App\Support\Timezones::defaultHostTimezone()
                            );
                            if (! \App\Support\Timezones::isAllowedHostTimezone($defaultTimezone)) {
                                $defaultTimezone = \App\Support\Timezones::defaultHostTimezone();
                            }
                        @endphp
                        <div class="mb-4">
                            <label for="host_timezone" class="form-label">{{ __('chef.workshop_form.fields.host_timezone.label') }}</label>
                            <select id="host_timezone" name="host_timezone" class="form-select @error('host_timezone') is-invalid @enderror" required>
                                @foreach($timezoneOptions ?? [] as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}" @selected($defaultTimezone === $tzValue)>{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('chef.workshop_form.fields.host_timezone.helper', ['detected' => request()->cookie('user_timezone') ?? __('chef.workshop_form.fields.host_timezone.unknown')]) }}
                            </p>
                            @error('host_timezone')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="title" class="form-label">عنوان الورشة *</label>
                                <input id="title" name="title" type="text" value="{{ old('title') }}" required
                                       class="form-input @error('title') is-invalid @enderror"
                                       placeholder="مثال: تعلم صنع الكيك الفرنسي">
                                @error('title')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="instructor" class="form-label">اسم المدرب *</label>
                                <input id="instructor" name="instructor" type="text" value="{{ old('instructor') }}" required
                                       class="form-input @error('instructor') is-invalid @enderror"
                                       placeholder="مثال: الشيف أحمد محمد">
                                @error('instructor')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="category" class="form-label">فئة الورشة *</label>
                                <select id="category" name="category" required
                                        class="form-select @error('category') is-invalid @enderror">
                                    <option value="">اختر الفئة</option>
                                    <option value="cooking" {{ old('category') == 'cooking' ? 'selected' : '' }}>طبخ</option>
                                    <option value="baking" {{ old('category') == 'baking' ? 'selected' : '' }}>خبز</option>
                                    <option value="desserts" {{ old('category') == 'desserts' ? 'selected' : '' }}>حلويات</option>
                                    <option value="beverages" {{ old('category') == 'beverages' ? 'selected' : '' }}>مشروبات</option>
                                    <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>أخرى</option>
                                </select>
                                @error('category')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="level" class="form-label">مستوى الورشة *</label>
                                <select id="level" name="level" required
                                        class="form-select @error('level') is-invalid @enderror">
                                    <option value="">اختر المستوى</option>
                                    <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                    <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                    <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                </select>
                                @error('level')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <div class="section-divider"></div>
                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h2 class="section-title">الجدول والتسعير</h2>
                            <p class="section-description">
                                حدد المواعيد، السعر، وعدد المقاعد حتى يتضح للمشاركين ما ينتظرهم عند الحجز.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="start_date" class="form-label">تاريخ البداية *</label>
                                <input id="start_date" name="start_date" type="datetime-local" value="{{ old('start_date') }}" required
                                       class="form-input @error('start_date') is-invalid @enderror">
                                @error('start_date')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="form-label">تاريخ النهاية *</label>
                                <input id="end_date" name="end_date" type="datetime-local" value="{{ old('end_date') }}" required
                                       class="form-input @error('end_date') is-invalid @enderror">
                                @error('end_date')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="duration" class="form-label">مدة الورشة (بالدقائق) *</label>
                                <input id="duration" name="duration" type="number" min="1" value="{{ old('duration') }}" required
                                       class="form-input @error('duration') is-invalid @enderror"
                                       placeholder="120">
                                @error('duration')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="max_participants" class="form-label">العدد الأقصى للمشاركين *</label>
                                <input id="max_participants" name="max_participants" type="number" min="1" value="{{ old('max_participants') }}" required
                                       class="form-input @error('max_participants') is-invalid @enderror"
                                       placeholder="20">
                                @error('max_participants')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price" class="form-label">السعر *</label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" required
                                           class="form-input flex-1 @error('price') is-invalid @enderror"
                                           placeholder="0.00">
                                    <select name="currency"
                                            class="form-select sm:w-44 @error('currency') is-invalid @enderror">
                                        @foreach($currencyOptions as $code => $currency)
                                            <option value="{{ $code }}" {{ old('currency', $defaultCurrency) == $code ? 'selected' : '' }}>
                                                {{ $currency['label'] ?? $code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('price')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                                @error('currency')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <p class="form-label">آخر موعد للتسجيل</p>
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-800">
                                    يتم ضبط هذا الموعد تلقائياً قبل بداية الورشة بدقيقتين وفقاً لتوقيت المضيف.
                                </div>
                                <p class="form-hint mt-2">يتم إغلاق التسجيل تلقائياً بدون الحاجة لأي إجراء يدوي.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="section-divider"></div>

                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div>
                            <h2 class="section-title">الموقع وطرق الوصول</h2>
                            <p class="section-description">
                                وفّر التفاصيل اللازمة للوصول إلى الورشة سواء كانت حضورية أو أونلاين مع رابط الاجتماع للمشاركين.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="location" class="form-label">الموقع (للورش الحضورية)</label>
                                <input id="location" name="location" type="text" value="{{ old('location') }}" required
                                       class="form-input @error('location') is-invalid @enderror"
                                       placeholder="مثال: عمان - شارع الملكة رانيا">
                                <p id="location-help" class="form-hint">يجب تحديد الموقع عند اختيار ورشة حضورية.</p>
                                @error('location')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address" class="form-label">العنوان التفصيلي</label>
                                <input id="address" name="address" type="text" value="{{ old('address') }}"
                                       class="form-input @error('address') is-invalid @enderror"
                                       placeholder="تفاصيل إضافية للموقع (إن وجدت)">
                                @error('address')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-admin.online-meeting-tools
                                :is-online="$isOnlineOld"
                                :auto-generate="$autoGenerateMeeting"
                                :meeting-link="$storedMeetingLink"
                                :has-generated-meeting="$hasGeneratedMeeting"
                                :meeting-status-state="$meetingStatusState"
                                :meeting-status-text="$meetingStatusText"
                                :generate-url="route('admin.workshops.generate-link')"
                                :force-auto="$forceAutoMeetingLinks"
                            />

                            <div class="md:col-span-2">
                                <label for="recording_url" class="form-label">رابط تسجيل Google Drive</label>
                                <input
                                    id="recording_url"
                                    name="recording_url"
                                    type="url"
                                    value="{{ old('recording_url') }}"
                                    class="form-input @error('recording_url') is-invalid @enderror"
                                    placeholder="https://drive.google.com/file/d/XXXX/view"
                                >
                                <p class="form-hint">ضع رابط المشاركة من Google Drive وسيتم تضمين الفيديو على الصفحة العامة للشيف.</p>
                                @error('recording_url')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <div class="section-divider"></div>

                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-align-left"></i>
                        </div>
                        <div>
                            <h2 class="section-title">المحتوى والتجربة التعليمية</h2>
                            <p class="section-description">
                                شارك القصة الكاملة للورشة، ما الذي سيتعلمه المشاركون، والمتطلبات التي يحتاجونها قبل الانضمام.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="grid gap-6">
                            <div>
                                <label for="description" class="form-label">وصف الورشة *</label>
                                <textarea id="description" name="description" rows="4" required
                                          class="form-textarea @error('description') is-invalid @enderror"
                                          placeholder="اكتب وصفاً مختصراً جذاباً للورشة...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="content" class="form-label">محتوى الورشة التفصيلي</label>
                                <textarea id="content" name="content" rows="7"
                                          class="form-textarea @error('content') is-invalid @enderror"
                                          placeholder="اكتب محتوى مفصلاً عن الورشة...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="what_you_will_learn" class="form-label">ما سيتعلمه المشاركون</label>
                                    <textarea id="what_you_will_learn" name="what_you_will_learn" rows="4"
                                              class="form-textarea @error('what_you_will_learn') is-invalid @enderror"
                                              placeholder="أبرز نقاط التعلم الرئيسية...">{{ old('what_you_will_learn') }}</textarea>
                                    @error('what_you_will_learn')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="requirements" class="form-label">متطلبات الورشة</label>
                                    <textarea id="requirements" name="requirements" rows="4"
                                              class="form-textarea @error('requirements') is-invalid @enderror"
                                              placeholder="أدخل المتطلبات المسبقة...">{{ old('requirements') }}</textarea>
                                    @error('requirements')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="materials_needed" class="form-label">المواد المطلوبة</label>
                                    <textarea id="materials_needed" name="materials_needed" rows="4"
                                              class="form-textarea @error('materials_needed') is-invalid @enderror"
                                              placeholder="اذكر الأدوات أو المكونات المطلوبة...">{{ old('materials_needed') }}</textarea>
                                    @error('materials_needed')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="instructor_bio" class="form-label">نبذة عن المدرب</label>
                                    <textarea id="instructor_bio" name="instructor_bio" rows="4"
                                              class="form-textarea @error('instructor_bio') is-invalid @enderror"
                                              placeholder="أضف ملخصاً عن خبرات المدرب...">{{ old('instructor_bio') }}</textarea>
                                    @error('instructor_bio')
                                        <p class="error-text">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="featured_description" class="form-label">وصف الورشة المميزة</label>
                                <textarea id="featured_description" name="featured_description" rows="3"
                                          class="form-textarea @error('featured_description') is-invalid @enderror"
                                          placeholder="وصف مختصر يظهر في بطاقة الورشة المميزة (اختياري)">{{ old('featured_description') }}</textarea>
                                <p class="form-hint">يساعد على إبراز الورشة في الصفحة الرئيسية عند تمييزها.</p>
                                @error('featured_description')
                                    <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <div class="section-divider"></div>
                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div>
                            <h2 class="section-title">الصورة الرئيسية</h2>
                            <p class="section-description">
                                اختر صورة جذابة تمثل أجواء الورشة. ندعم صوراً حتى 5 ميجابايت مع ضغط تلقائي للحفاظ على الجودة.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div id="image-upload-area" class="image-upload-area">
                            <div class="flex flex-col items-center gap-3 text-slate-600">
                                <i class="fas fa-cloud-upload-alt text-3xl text-purple-400"></i>
                                <h3 class="text-lg font-semibold text-slate-800">اسحب وأفلت الصورة هنا</h3>
                                <p class="text-sm text-slate-500">أو انقر للاختيار من جهازك (JPEG، PNG، GIF، WebP حتى 5MB)</p>
                                <span class="upload-badge">
                                    <i class="fas fa-folder-open"></i>
                                    اختر صورة
                                </span>
                            </div>
                        </div>

                        <input id="image"
                               name="image"
                               type="file"
                               accept="image/*"
                               class="hidden"
                               onchange="handleImageUpload(this)"
                               data-max-size="5120"
                               data-max-size-message="لا يمكن رفع صورة أكبر من 5 ميجابايت."
                               data-error-target="#admin_workshop_image_error">

                        <div id="image-preview" class="hidden">
                            <div class="image-preview-card">
                                <img id="preview-img" src="#" alt="معاينة الصورة الجديدة" loading="lazy">
                                <button type="button" id="remove-preview" class="remove-preview-btn" onclick="removeImagePreview()">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            <p id="image-info" class="mt-2 text-xs text-slate-500"></p>
                        </div>

                        <p id="admin_workshop_image_error" class="text-sm text-red-600 mt-2 hidden"></p>

                        @error('image')
                            <p class="error-text mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <div class="section-divider"></div>

                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div>
                            <h2 class="section-title">الوصفات المرتبطة</h2>
                            <p class="section-description">
                                اختر الوصفات التي سيتم تغطيتها في هذه الورشة لعرضها للمشاركين بشكل تلقائي في صفحة التفاصيل.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="recipe-wrapper">
                            <div class="recipe-tools">
                                <div class="recipe-search">
                                    <i class="fas fa-search text-slate-400"></i>
                                    <input id="recipe-search" type="text" placeholder="البحث في الوصفات...">
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" id="select-all-recipes" class="select-all-btn">
                                        <i class="fas fa-check-double"></i>
                                        اختيار الكل
                                    </button>
                                    <button type="button" id="clear-selection" class="clear-btn">
                                        <i class="fas fa-eraser"></i>
                                        إلغاء الكل
                                    </button>
                                </div>
                            </div>

                            @php
                                $selectedRecipes = old('recipe_ids', []);
                            @endphp
                            <div id="recipes-container" class="recipes-grid">
                                @foreach($recipes as $recipe)
                                    @php
                                        $isSelected = in_array($recipe->recipe_id, $selectedRecipes);
                                    @endphp
                                    <div class="recipe-item {{ $isSelected ? 'selected' : '' }}" data-recipe-id="{{ $recipe->recipe_id }}" data-recipe-title="{{ strtolower($recipe->title) }}">
                                        <label class="flex cursor-pointer items-start gap-3">
                                            <input type="checkbox" name="recipe_ids[]" value="{{ $recipe->recipe_id }}" {{ $isSelected ? 'checked' : '' }}
                                                   class="recipe-checkbox mt-1 h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400">
                                            <div class="flex-1 space-y-3">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $recipe->image_url ?: 'https://placehold.co/60x60/f87171/FFFFFF?text=وصفة' }}"
                                                         alt="{{ $recipe->title }}"
                                                         class="h-14 w-14 rounded-xl border border-slate-200 object-cover"
                                                        onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-slate-900 leading-snug">{{ $recipe->title }}</h4>
                                                        <p class="text-xs text-slate-500">{{ $recipe->author }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between text-xs text-slate-500">
                                                    <span><i class="fas fa-clock ml-1 text-slate-400"></i>{{ ($recipe->prep_time ?? 0) + ($recipe->cook_time ?? 0) }} دقيقة</span>
                                                    <span><i class="fas fa-users ml-1 text-slate-400"></i>{{ $recipe->servings ?? 0 }} حصة</span>
                                                    <span class="rounded-full bg-orange-100 px-2 py-1 text-orange-600">
                                                        {{ $recipe->difficulty === 'easy' ? 'سهل' : ($recipe->difficulty === 'medium' ? 'متوسط' : 'صعب') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @if($recipes->count() === 0)
                                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white/70 py-12 text-slate-500">
                                    <i class="fas fa-utensils text-3xl mb-3 text-slate-400"></i>
                                    <p>لا توجد وصفات متاحة حالياً.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="section-divider"></div>

                <section>
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                        <div>
                            <h2 class="section-title">إعدادات الحالة والتحكم</h2>
                            <p class="section-description">
                                فعّل الورشة مباشرة، حدد إن كانت أونلاين، واختر ما إذا كنت ترغب بتمييزها كأقرب ورشة قادمة.
                            </p>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="status-grid">
                            <label class="status-card">
                                <input type="checkbox" name="is_online" value="1" {{ old('is_online') ? 'checked' : '' }}>
                                <div class="status-card-content">
                                    <span class="status-card-icon">
                                        <i class="fas fa-wifi"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">ورشة أونلاين</p>
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                            فعّل هذا الخيار إذا كانت الجلسات تتم عبر الإنترنت وسيُرسل الرابط للمشاركين تلقائياً.
                                        </p>
                                    </div>
                                </div>
                            </label>

                            <label class="status-card">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <div class="status-card-content">
                                    <span class="status-card-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">تفعيل الورشة</p>
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                            عند التفعيل، ستظهر الورشة في صفحة الورش ويمكن للمستخدمين الحجز فوراً.
                                        </p>
                                    </div>
                                </div>
                            </label>

                            <label class="status-card">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <div class="status-card-content">
                                    <span class="status-card-icon">
                                        <i class="fas fa-crown"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">ورشة مميزة</p>
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                            اختيارها يجعلها الورشة القادمة على الصفحة الرئيسية ويلغي تمييز أي ورشة أخرى حالياً.
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <a href="{{ route('admin.workshops.index') }}" class="button-secondary">
                        <i class="fas fa-arrow-right"></i>
                        إلغاء
                    </a>
                    <button type="submit" class="button-primary">
                        <i class="fas fa-save"></i>
                        حفظ الورشة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
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

function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('نوع الملف غير مدعوم. يرجى اختيار صورة JPG أو PNG أو GIF أو WebP', 'error');
        input.value = '';
        return;
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotification('حجم الصورة يجب أن يكون أقل من 5 ميجابايت', 'error');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = event => {
        const previewImg = document.getElementById('preview-img');
        const imagePreview = document.getElementById('image-preview');
        const imageInfo = document.getElementById('image-info');
        const imageUploadArea = document.getElementById('image-upload-area');

        previewImg.src = event.target.result;
        imagePreview.classList.remove('hidden');
        imageUploadArea.classList.add('has-image');

        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
        imageInfo.textContent = `الملف: ${file.name} (${fileSizeMB} MB)`;

        showNotification('تم تحميل الصورة بنجاح', 'success');
    };

    reader.readAsDataURL(file);
}

function removeImagePreview() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const imageUploadArea = document.getElementById('image-upload-area');

    imageInput.value = '';
    imagePreview.classList.add('hidden');
    imageUploadArea.classList.remove('has-image');

    showNotification('تم حذف الصورة المحددة', 'success');
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('workshop-create-form');
    if (!form) {
        return;
    }

    const featuredCheckbox = form.querySelector('input[name="is_featured"]');
    const isOnlineCheckbox = form.querySelector('input[name="is_online"]');
    const imageInput = document.getElementById('image');
    const imageUploadArea = document.getElementById('image-upload-area');
    const recipeSearch = document.getElementById('recipe-search');
    const selectAllBtn = document.getElementById('select-all-recipes');
    const clearSelectionBtn = document.getElementById('clear-selection');
    const recipeCheckboxes = document.querySelectorAll('.recipe-checkbox');
    const recipeItems = document.querySelectorAll('.recipe-item');
    const locationInput = document.getElementById('location');
    const meetingLinkInput = document.getElementById('meeting_link');
    const locationHelp = document.getElementById('location-help');
    const meetingLinkHelp = document.getElementById('meeting-link-help');
    const onlineMeetingTools = document.getElementById('onlineMeetingTools');
    const manualMeetingField = document.getElementById('manualMeetingField');
    const autoGenerateInput = document.getElementById('auto_generate_meeting');
    const generateBtn = document.getElementById('generateMeetLinkBtn');
    const generatedInfo = document.getElementById('generatedMeetingInfo');
    const meetingStatusBadge = document.getElementById('meetingStatusBadge');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (imageUploadArea) {
        ['dragenter', 'dragover'].forEach(evt => {
            imageUploadArea.addEventListener(evt, event => {
                event.preventDefault();
                imageUploadArea.classList.add('has-image');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(evt => {
            imageUploadArea.addEventListener(evt, event => {
                event.preventDefault();
                imageUploadArea.classList.remove('has-image');
            });
        });

        imageUploadArea.addEventListener('drop', event => {
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                imageInput.files = files;
                handleImageUpload(imageInput);
            }
        });

        imageUploadArea.addEventListener('click', () => {
            imageInput.click();
        });
    }

    function setMeetingStatus(state, text) {
        if (!meetingStatusBadge) {
            return;
        }
        meetingStatusBadge.dataset.state = state;
        meetingStatusBadge.textContent = text;
    }

    function renderGeneratedInfo(link) {
        if (!generatedInfo) {
            return;
        }

        if (!link) {
            generatedInfo.classList.add('hidden');
            generatedInfo.innerHTML = '';
            return;
        }

        generatedInfo.classList.remove('hidden');
        generatedInfo.innerHTML = '';

        const title = document.createElement('p');
        title.className = 'font-semibold text-slate-800';
        title.textContent = 'تم إنشاء رابط Google Meet:';
        generatedInfo.appendChild(title);

        const linkEl = document.createElement('p');
        linkEl.className = 'mt-1 text-sm break-all text-slate-700';
        linkEl.textContent = link;
        generatedInfo.appendChild(linkEl);
    }

    function toggleMeetingInputState() {
        if (!meetingLinkInput) {
            return;
        }

        const isOnline = !!isOnlineCheckbox?.checked;
        const autoGenerate = !!autoGenerateInput?.checked;
        const hasGeneratedLink = Boolean((meetingLinkInput.value || '').trim());

        meetingLinkInput.placeholder = isOnline
            ? 'https://meet.google.com/abc-defg-hij'
            : 'يمكن ترك الحقل فارغاً للورش الحضورية';

        meetingLinkInput.readOnly = isOnline && autoGenerate;
        meetingLinkInput.required = isOnline && !autoGenerate;
        meetingLinkInput.classList.toggle('readonly-input', isOnline && autoGenerate);

        if (meetingLinkHelp) {
            if (!isOnline) {
                meetingLinkHelp.textContent = 'هذا الحقل اختياري للورش الحضورية.';
            } else if (autoGenerate) {
                meetingLinkHelp.textContent = 'سيتم تعيين الرابط تلقائياً بعد الحفظ أو فور الضغط على زر التوليد.';
            } else {
                meetingLinkHelp.textContent = 'أدخل رابط الاجتماع، وسيظهر للمشاركين بعد تأكيد الحجز.';
            }
        }

        if (meetingStatusBadge) {
            if (!isOnline) {
                setMeetingStatus('idle', 'قم بتفعيل الورشة الأونلاين للوصول إلى توليد الروابط.');
            } else if (hasGeneratedLink) {
                setMeetingStatus('ready', 'تم إنشاء رابط Google Meet جاهز للمشاركين.');
            } else if (autoGenerate) {
                setMeetingStatus('idle', 'سيتم توليد رابط Google Meet تلقائياً بعد الحفظ.');
            } else {
                setMeetingStatus('manual', 'يرجى لصق رابط الاجتماع المخصص يدوياً.');
            }
        }
    }

    function toggleOnlineFields() {
        if (!isOnlineCheckbox) {
            toggleMeetingInputState();
            return;
        }

        const isOnline = isOnlineCheckbox.checked;

        if (locationInput) {
            locationInput.required = !isOnline;
            locationInput.placeholder = isOnline ? 'مثال: أونلاين عبر Google Meet أو وصف موجز' : 'مثال: عمان - شارع الملكة رانيا';
            locationInput.classList.toggle('border-emerald-400', isOnline);
            if (locationHelp) {
                locationHelp.textContent = isOnline
                    ? 'الحقل اختياري للورش الأونلاين، ويمكنك إضافة وصف عام للمكان إذا رغبت.'
                    : 'يجب تحديد الموقع عند اختيار ورشة حضورية.';
            }
        }

        if (onlineMeetingTools) {
            onlineMeetingTools.classList.toggle('hidden', !isOnline);
        }

        if (manualMeetingField) {
            manualMeetingField.classList.toggle('opacity-60', !isOnline);
        }

        if (generateBtn) {
            generateBtn.disabled = !isOnline;
        }

        toggleMeetingInputState();
    }

    if (isOnlineCheckbox) {
        toggleOnlineFields();
        isOnlineCheckbox.addEventListener('change', toggleOnlineFields);
    } else {
        toggleMeetingInputState();
    }

    autoGenerateInput?.addEventListener('change', () => {
        if (!isOnlineCheckbox?.checked) {
            autoGenerateInput.checked = false;
        }
        toggleMeetingInputState();
    });

    generateBtn?.addEventListener('click', async () => {
        if (!isOnlineCheckbox?.checked) {
            showNotification('يرجى تفعيل خيار الورشة الأونلاين قبل توليد الرابط.', 'warning');
            return;
        }

        if (!csrfToken) {
            showNotification('تعذر العثور على رمز الحماية. أعد تحميل الصفحة ثم حاول مجدداً.', 'error');
            return;
        }

        const titleInput = document.getElementById('title');
        const startDateInput = document.getElementById('start_date');
        const title = titleInput?.value?.trim();
        const startDate = startDateInput?.value || null;

        if (!title) {
            showNotification('يرجى إدخال عنوان الورشة قبل توليد الرابط.', 'warning');
            titleInput?.focus();
            return;
        }

        const originalHtml = generateBtn.innerHTML;
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>جاري التوليد...</span>';
        setMeetingStatus('idle', 'جاري توليد رابط جديد...');

        try {
            const response = await fetch(generateBtn.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title,
                    start_date: startDate,
                }),
            });

            if (!response.ok) {
                let message = 'تعذر توليد الرابط حالياً.';
                try {
                    const payload = await response.json();
                    message = payload?.message
                        ?? Object.values(payload?.errors || {})[0]?.[0]
                        ?? message;
                } catch (error) {
                    // ignore parsing errors
                }
                throw new Error(message);
            }

            const data = await response.json();
            if (meetingLinkInput) {
                meetingLinkInput.value = data.meeting_link;
            }
            if (autoGenerateInput) {
                autoGenerateInput.checked = true;
            }

            renderGeneratedInfo(data.meeting_link);
            toggleMeetingInputState();
            setMeetingStatus('ready', 'تم إنشاء رابط Google Meet جاهز للمشاركين.');
            showNotification('تم إنشاء رابط Google Meet بنجاح.', 'success');
        } catch (error) {
            console.error(error);
            setMeetingStatus('error', 'فشل توليد الرابط، أعد المحاولة.');
            showNotification(error.message || 'تعذر توليد الرابط حالياً.', 'error');
        } finally {
            generateBtn.disabled = !isOnlineCheckbox?.checked;
            generateBtn.innerHTML = originalHtml;
        }
    });

    renderGeneratedInfo(meetingLinkInput?.value || '');

    if (recipeSearch) {
        recipeSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            recipeItems.forEach(item => {
                const title = item.getAttribute('data-recipe-title');
                item.style.display = title.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            recipeCheckboxes.forEach(checkbox => {
                const item = checkbox.closest('.recipe-item');
                if (item && item.style.display !== 'none') {
                    checkbox.checked = true;
                    item.classList.add('selected');
                }
            });
        });
    }

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', () => {
            recipeCheckboxes.forEach(checkbox => checkbox.checked = false);
            recipeItems.forEach(item => item.classList.remove('selected'));
        });
    }

    recipeCheckboxes.forEach(checkbox => {
        const recipeItem = checkbox.closest('.recipe-item');

        if (checkbox.checked && recipeItem) {
            recipeItem.classList.add('selected');
        }

        checkbox.addEventListener('change', () => {
            if (!recipeItem) {
                return;
            }

            if (checkbox.checked) {
                recipeItem.classList.add('selected');
            } else {
                recipeItem.classList.remove('selected');
            }
        });
    });

    if (featuredCheckbox) {
        featuredCheckbox.addEventListener('change', function() {
            if (this.checked) {
                fetch('/admin/workshops/check-featured')
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasFeatured) {
                            const confirmed = confirm('يوجد ورشة مميزة حالياً. هل تريد جعل هذه الورشة هي الورشة المميزة الجديدة؟ سيتم إلغاء تمييز الورشة السابقة.');
                            if (!confirmed) {
                                featuredCheckbox.checked = false;
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }

    form.addEventListener('submit', async event => {
        if (form.dataset.submitting === 'true') {
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const showLoading = () => {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>جاري الحفظ...</span>';
            }
        };

        if (featuredCheckbox && featuredCheckbox.checked) {
            event.preventDefault();
            try {
                const response = await fetch('/admin/workshops/check-featured');
                const data = await response.json();
                if (data.hasFeatured) {
                    const confirmed = confirm('يوجد ورشة مميزة حالياً. هل تريد جعل هذه الورشة هي الورشة المميزة الجديدة؟ سيتم إلغاء تمييز الورشة السابقة.');
                    if (!confirmed) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-save"></i><span>حفظ الورشة</span>';
                        }
                        return;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }

            showLoading();
            form.dataset.submitting = 'true';
            form.submit();
            return;
        }

        showLoading();
        form.dataset.submitting = 'true';
    });
});
</script>
@endpush













