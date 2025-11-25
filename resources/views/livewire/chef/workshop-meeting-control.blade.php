<div class="flex flex-col items-start gap-3 text-sm text-slate-700" id="meetingStateLabel">
    @if ($started)
        <p class="text-slate-600">
            تم فتح الاجتماع {{ $startedAtHuman }}. راقب لوحة التحكم أدناه لإدارة البث.
        </p>
    @else
        <p class="text-slate-600">
            سيتم تشغيل غرفة الاجتماع فور الموافقة. تأكد من أن الصوت والكاميرا جاهزان وأن اسمك المعروض هو الاسم الصحيح قبل المتابعة.
        </p>
        <label class="mt-2 inline-flex items-start gap-2 text-sm text-slate-700">
            <input
                type="checkbox"
                wire:model.live="confirmHost"
                class="mt-1 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-200"
            >
            <span>
                أؤكد أنني المضيف وأن الاسم الذي سيظهر للمشاركين هو الاسم الصحيح الخاص بي.
            </span>
        </label>
        <button
            type="button"
            id="startMeetingBtn"
            wire:click="startMeeting"
            wire:target="startMeeting"
            wire:loading.attr="disabled"
            @disabled(! $confirmHost)
            class="mt-3 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:from-emerald-500 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="startMeeting">
                <i class="fas fa-unlock"></i>
                افتح الاجتماع للمشاركين
            </span>
            <span class="inline-flex items-center gap-2" wire:loading wire:target="startMeeting">
                <i class="fas fa-spinner fa-spin"></i>
                يتم البدء...
            </span>
        </button>
        <span class="mt-2 text-slate-500">
            فور فتح الاجتماع سيُخطر المشاركون وستظهر لهم الغرفة كمتاحة للانضمام.
        </span>
    @endif

    @if ($errorMessage)
        <span class="mt-2 text-sm text-rose-500" role="alert">
            {{ $errorMessage }}
        </span>
    @endif
</div>

