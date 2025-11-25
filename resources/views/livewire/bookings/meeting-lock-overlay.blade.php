<div wire:poll.5s="refreshLock" class="absolute inset-0 pointer-events-none">
    <style>
        .session-lock-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
        }
        .session-lock-overlay .lock-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.1);
            color: #f8fafc;
        }
    </style>
    @if ($meetingLocked)
        <div class="session-lock-overlay pointer-events-auto flex flex-col items-center justify-center text-center" aria-hidden="false">
            <span class="lock-icon" aria-hidden="true">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
            <p class="text-sm text-rose-100 mt-3">تم قفل الاجتماع مؤقتاً. يرجى انتظار المضيف.</p>
            <span class="sr-only">بانتظار عودة المضيف</span>
        </div>
    @endif
</div>

