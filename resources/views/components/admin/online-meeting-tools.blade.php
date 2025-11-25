@props([
    'isOnline' => false,
    'autoGenerate' => false,
    'meetingLink' => '',
    'hasGeneratedMeeting' => false,
    'meetingStatusState' => 'idle',
    'meetingStatusText' => 'سيتم توليد الرابط تلقائياً بعد الحفظ.',
    'generateUrl' => '#',
    'labelClass' => 'form-label',
    'inputClass' => 'form-input',
    'inputErrorClass' => 'is-invalid',
    'hintClass' => 'form-hint',
    'errorClass' => 'error-text',
    'forceAuto' => false,
])

@php
    $meetingLinkValue = trim((string) ($meetingLink ?? ''));
    $showGeneratedInfo = $hasGeneratedMeeting || $meetingLinkValue !== '';
    $autoLockEnabled = (bool) $forceAuto;

    $inputClasses = trim(
        $inputClass .
        ($isOnline && $autoGenerate ? ' readonly-input' : '') .
        ($errors->has('meeting_link') ? ' ' . $inputErrorClass : '')
    );

    if ($autoLockEnabled) {
        $autoGenerate = true;
    }
@endphp

<div {{ $attributes->class(['md:col-span-2 space-y-4']) }}>
    <div id="onlineMeetingTools" class="online-meeting-tools {{ $isOnline ? '' : 'hidden' }} space-y-3">
        <div class="meeting-generator-card">
            <div>
                <p class="text-sm font-semibold text-slate-800">توليد رابط الاجتماع الذكي</p>
                <p class="text-xs text-slate-500">
                    احصل على رابط Google Meet رسمي بضغطة زر أو دع النظام يجهزه عند الحفظ.
                </p>
            </div>
            <div class="generator-actions">
                <span class="meeting-status" id="meetingStatusBadge" data-state="{{ $meetingStatusState }}">
                    {{ $meetingStatusText }}
                </span>
                <div class="flex flex-wrap items-center gap-3">
                    @if($autoLockEnabled)
                        <input type="hidden" name="auto_generate_meeting" value="1">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 text-emerald-700">
                            <input type="checkbox" name="auto_generate_meeting" id="auto_generate_meeting" value="1" checked disabled>
                            توليد تلقائي عبر Google Meet
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-700">
                                مفعل دائماً
                            </span>
                        </label>
                    @else
                        <input type="hidden" name="auto_generate_meeting" value="0">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="auto_generate_meeting" id="auto_generate_meeting" value="1" {{ $autoGenerate ? 'checked' : '' }}>
                            توليد تلقائي عبر Google Meet
                        </label>
                    @endif
                    <button type="button"
                            id="generateMeetLinkBtn"
                            data-url="{{ $generateUrl }}"
                            class="generate-link-btn">
                        <i class="fas fa-bolt"></i>
                        <span>توليد رابط الآن</span>
                    </button>
                </div>
            </div>
        </div>
        <div id="generatedMeetingInfo" class="{{ $showGeneratedInfo ? '' : 'hidden' }} meeting-info-card space-y-2">
            @if($showGeneratedInfo)
                <p class="font-semibold text-slate-800">تم إنشاء رابط Google Meet:</p>
                <p class="mt-1 text-sm break-all text-slate-700">{{ $meetingLinkValue }}</p>
            @else
                <p class="text-sm text-slate-600">سيظهر الرابط والمعلومات الإضافية هنا بعد توليده.</p>
            @endif
        </div>
    </div>

    <div id="manualMeetingField" class="space-y-2">
        <label for="meeting_link" class="{{ $labelClass }}">رابط الاجتماع (للورش الأونلاين)</label>
        <input
            id="meeting_link"
            name="meeting_link"
            type="url"
            value="{{ $meetingLinkValue }}"
            class="{{ $inputClasses }}"
            placeholder="https://meet.google.com/abc-defg-hij"
            @if($isOnline && !$autoGenerate) required @endif
            @if($isOnline && $autoGenerate) readonly @endif
        >
        <p id="meeting-link-help" class="{{ $hintClass }}">
            @if(!$isOnline)
                هذا الحقل اختياري للورش الحضورية.
            @elseif($autoLockEnabled)
                سيتم تعيين الرابط تلقائياً بعد الحفظ ولا يمكن تعديله يدوياً.
            @elseif($autoGenerate)
                سيتم تعيين الرابط تلقائياً بعد الحفظ أو فور الضغط على زر التوليد.
            @else
                يجب إضافة رابط الاجتماع، وسيظهر للمشاركين بعد تأكيد الحجز.
            @endif
        </p>
        @error('meeting_link')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
</div>

