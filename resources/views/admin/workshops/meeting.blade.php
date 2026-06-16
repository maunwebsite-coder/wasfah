@extends('layouts.app')

@section('title', 'غرفة الإدارة - ' . $workshop->title)

@push('styles')
<style>
    body {
        background: radial-gradient(circle at top left, rgba(8, 47, 73, 0.96), rgba(15, 23, 42, 0.92));
        min-height: 100vh;
    }

    footer {
        display: none !important;
    }

    .admin-meet-shell {
        max-width: 960px;
        margin: 0 auto;
        padding: 3rem 1.5rem 4rem;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .admin-card {
        border-radius: 1.75rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
        box-shadow: 0 40px 65px -50px rgba(15, 23, 42, 0.9);
        color: #f8fafc;
        padding: 2.5rem;
    }

    .info-card {
        border-radius: 1.5rem;
        border: 1px dashed rgba(125, 211, 252, 0.6);
        background: rgba(15, 118, 110, 0.08);
        color: #f9eff1;
        padding: 2rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 999px;
        padding: 0.85rem 1.75rem;
        font-weight: 600;
        transition: transform 150ms ease, box-shadow 150ms ease;
    }

    .action-btn.primary {
        background: linear-gradient(120deg, #6b2e30, #fb7185);
        color: #0f172a;
        box-shadow: 0 12px 30px rgba(251, 113, 133, 0.35);
    }

    .action-btn.secondary {
        border: 1px solid rgba(226, 232, 240, 0.4);
        color: #e2e8f0;
        background: transparent;
    }

    .action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .action-btn:not(:disabled):hover {
        transform: translateY(-2px);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge[data-state="ready"] {
        background: rgba(52, 211, 153, 0.15);
        color: #34d399;
        border: 1px solid rgba(52, 211, 153, 0.35);
    }

    .status-badge[data-state="pending"] {
        background: rgba(251, 191, 36, 0.15);
        color: #9f5e63;
        border: 1px solid rgba(251, 191, 36, 0.35);
    }

    .status-badge[data-state="locked"] {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.35);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen py-6">
    <div class="admin-meet-shell">
        <div class="admin-card space-y-6">
            <div class="space-y-3">
                <p class="uppercase tracking-[0.45em] text-xs text-sky-200/70">لوحة تحكم اجتماع Google Meet</p>
                <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ $workshop->title }}</h1>
                <p class="text-slate-300 text-sm leading-relaxed max-w-2xl">
                    تم إنشاء هذا الاجتماع عبر Google Meet لتقديم الورشة للمشاركين. استخدم الزر أدناه لفتح الجلسة من حساب Google المخوّل، وشارك الرابط مع الفريق الاحتياطي إذا احتجت لذلك.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">التاريخ والوقت</p>
                    <p class="mt-2 text-lg font-semibold text-white">
                        {{ optional($workshop->start_date)->locale('ar')->translatedFormat('d F Y • h:i a') ?? 'غير محدد' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">رابط الاجتماع</p>
                    <p class="mt-2 text-sm break-all text-emerald-200">{{ $meetingLink }}</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white/5 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">رابط المضيف الآمن</p>
                @if($hostRedirectUrl)
                    <p class="mt-2 text-sm break-all text-emerald-200">{{ $hostRedirectUrl }}</p>
                    <p class="mt-2 text-xs text-slate-300">شارك هذا الرابط مع الشيف بدلاً من رابط Google Meet المباشر لضمان دخوله الفوري كمضيف.</p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <a href="{{ $hostRedirectUrl }}" target="_blank" rel="noopener noreferrer" class="action-btn primary">
                            <i class="fab fa-google"></i>
                            فتح الرابط الآمن
                        </a>
                        <button
                            type="button"
                            class="action-btn secondary"
                            id="copySecureHostLink"
                            data-link="{{ $hostRedirectUrl }}">
                            <i class="fas fa-link"></i>
                            نسخ الرابط الآمن
                        </button>
                    </div>
                @else
                    <p class="mt-2 text-sm text-amber-100">أضف بريد Google للشيف من ملفه الشخصي لتفعيل الرابط الآمن.</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span id="meetingStatusBadge" class="status-badge" data-state="{{ $workshop->meeting_started_at ? 'ready' : 'pending' }}">
                    <i class="fas {{ $workshop->meeting_started_at ? 'fa-circle-check' : 'fa-clock' }}"></i>
                    {{ $workshop->meeting_started_at ? 'الاجتماع جاهز' : 'ينتظر بدء المضيف' }}
                </span>
                @if($workshop->meeting_locked_at)
                    <span class="status-badge" data-state="locked">
                        <i class="fas fa-lock"></i>
                        تم قفل الغرفة عند {{ $workshop->meeting_locked_at->locale('ar')->translatedFormat('h:i a') }}
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ $meetingLink }}" target="_blank" rel="noopener noreferrer" class="action-btn primary" id="openMeetingBtn">
                    <i class="fab fa-google"></i>
                    فتح الاجتماع في Google Meet
                </a>
                <button
                    type="button"
                    class="action-btn secondary"
                    id="copyAdminMeetingLink"
                    data-link="{{ $meetingLink }}">
                    <i class="fas fa-copy"></i>
                    نسخ الرابط
                </button>
            </div>
        </div>

        <div class="info-card space-y-4">
            <h2 class="text-2xl font-semibold text-white">إرشادات سريعة للمضيف</h2>
            <ul class="space-y-3 text-sm leading-relaxed text-slate-100">
                <li class="flex gap-3">
                    <i class="fas fa-circle text-xs text-emerald-300 mt-1.5"></i>
                    استخدم حساب Google المخوّل ({{ config('services.google_meet.organizer_email') ?? 'حساب Google الرسمي' }}) لضمان امتلاكك لصلاحيات التسجيل والتحكم.
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-circle text-xs text-emerald-300 mt-1.5"></i>
                    اطلب من فريقك الانضمام قبل 5 دقائق للتأكد من وضوح الصوت والصورة.
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-circle text-xs text-emerald-300 mt-1.5"></i>
                    في حال تغير الرابط، اضغط "توليد رابط جديد" من لوحة تعديل الورشة ثم أعد مشاركة الرابط مع المشاركين.
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-circle text-xs text-emerald-300 mt-1.5"></i>
                    يمكنك قفل الاجتماع من لوحة الورشة داخل النظام للحفاظ على الأمن بعد اكتمال الحضور.
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const copyButton = document.getElementById('copyAdminMeetingLink');
        const meetingStatusBadge = document.getElementById('meetingStatusBadge');
        const meetingStarted = @json((bool) $workshop->meeting_started_at);

        if (!meetingStarted && meetingStatusBadge) {
            meetingStatusBadge.dataset.state = 'pending';
            meetingStatusBadge.innerHTML = '<i class="fas fa-clock"></i> سيتم فتح الاجتماع بعد دخولك';
        }

        const secureLinkButton = document.getElementById('copySecureHostLink');

        const handleCopy = (button, successLabel, defaultLabel) => {
            const link = button?.dataset.link;
            if (!button || !link) {
                return;
            }

            navigator.clipboard.writeText(link).then(() => {
                button.classList.add('ring-4', 'ring-emerald-300/60');
                button.innerHTML = successLabel;
                setTimeout(() => {
                    button.classList.remove('ring-4', 'ring-emerald-300/60');
                    button.innerHTML = defaultLabel;
                }, 2200);
            });
        };

        copyButton?.addEventListener('click', () => {
            handleCopy(
                copyButton,
                '<i class="fas fa-check-circle"></i> تم النسخ',
                '<i class="fas fa-copy"></i> نسخ الرابط'
            );
        });
        secureLinkButton?.addEventListener('click', () => {
            handleCopy(
                secureLinkButton,
                '<i class="fas fa-check-circle"></i> تم نسخ الرابط الآمن',
                '<i class="fas fa-link"></i> نسخ الرابط الآمن'
            );
        });
    });
</script>
@endpush






