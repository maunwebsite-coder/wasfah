@php
    $features = old('features', $heroSlide->features ?? []);
    if (!is_array($features)) {
        $features = [];
    }
    $features = array_values(array_filter(array_map(function ($value) {
        return is_string($value) ? trim($value) : '';
    }, $features), function ($value) {
        return $value !== '';
    }));
    if (empty($features)) {
        $features = [''];
    }

    $rawActions = old('actions', $heroSlide->actions ?? []);
    if (!is_array($rawActions) || empty($rawActions)) {
        $rawActions = [
            ['label' => '', 'url' => '', 'icon' => '', 'type' => 'primary', 'behavior' => 'static', 'open_in_new_tab' => false],
        ];
    }
    $actions = [];
    foreach ($rawActions as $action) {
        $actions[] = [
            'label' => $action['label'] ?? '',
            'url' => $action['url'] ?? '',
            'icon' => $action['icon'] ?? '',
            'type' => $action['type'] ?? 'primary',
            'behavior' => $action['behavior'] ?? 'static',
            'open_in_new_tab' => !empty($action['open_in_new_tab']),
        ];
    }
    $desktopPreview = old('desktop_image_url') ?: ($heroSlide->desktop_image_url ?? null);
    $mobilePreview = old('mobile_image_url') ?: ($heroSlide->mobile_image_url ?? null);
    $desktopIsVideo = $desktopPreview
        ? \Illuminate\Support\Str::of($desktopPreview)->lower()->endsWith('.webm')
        : false;
    $mobileIsVideo = $mobilePreview
        ? \Illuminate\Support\Str::of($mobilePreview)->lower()->endsWith('.webm')
        : false;
    $maxUploadKilobytes = \App\Services\HeroSlideImageService::MAX_FILE_SIZE_KB;
    $maxUploadMegabytes = rtrim(rtrim(number_format($maxUploadKilobytes / 1024, 1), '0'), '.');
@endphp

<div class="space-y-8">
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الشارة</label>
                <input type="text" name="badge" value="{{ old('badge', $heroSlide->badge ?? '') }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="مثال: ورشات العمل">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العنوان <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $heroSlide->title ?? '') }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" required>
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                <textarea name="description" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">{{ old('description', $heroSlide->description ?? '') }}</textarea>
                @error('description')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نص بديل للصورة</label>
                <input type="text" name="image_alt" value="{{ old('image_alt', $heroSlide->image_alt ?? '') }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ترتيب الظهور</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $heroSlide->sort_order ?? null) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="يتم التعيين تلقائياً عند تركه فارغاً">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">وسائط الشريحة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">وسائط سطح المكتب (صورة أو WEBM)</label>
                @if($desktopPreview)
                    <div class="mb-3">
                        @if($desktopIsVideo)
                            <video class="w-full rounded-xl border" controls muted loop playsinline preload="metadata">
                                <source src="{{ $desktopPreview }}" type="video/webm">
                                {{ __('لا يمكن عرض الفيديو في هذا المتصفح.') }}
                            </video>
                        @else
                            <img src="{{ $desktopPreview }}" alt="desktop preview" class="rounded-xl border" loading="lazy">
                        @endif
                    </div>
                @endif
                <div class="space-y-2">
                    <input type="file"
                           id="desktop_image_input"
                           name="desktop_image"
                           accept=".jpg,.jpeg,.png,.gif,.bmp,.svg,.webp,.webm"
                           class="hidden"
                           data-max-size="{{ $maxUploadKilobytes }}"
                           data-max-size-message="لا يمكن رفع ملف أكبر من {{ $maxUploadMegabytes }} ميجابايت."
                           data-error-target="#desktop_image_error">
                    <label for="desktop_image_input" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-dashed border-gray-300 text-gray-700 bg-gray-50 hover:bg-white hover:border-orange-300 transition cursor-pointer">
                        <i class="fas fa-upload text-orange-500"></i>
                        <span>اختر ملفاً من جهازك</span>
                    </label>
                    <p class="text-xs text-gray-500" id="desktop_file_name">لم يتم اختيار ملف بعد.</p>
                </div>
                <p class="text-xs text-gray-500 mt-1">استخدم هذا القسم لرفع صورة أو فيديو من جهازك.</p>
                <p class="text-xs text-gray-500 mt-1">الأنواع المدعومة: JPG, PNG, GIF, SVG, WEBP أو فيديو WEBM (بحد أقصى {{ $maxUploadMegabytes }}MB).</p>
                <p class="text-xs text-orange-600 mt-1">يتم تحويل الصور تلقائياً إلى صيغة WebP بجودة 80% مع ضبط العرض الأقصى إلى 1920px للحفاظ على سرعة التحميل.</p>
                <p id="desktop_image_error" class="text-xs text-red-600 mt-1 hidden"></p>
                <p class="text-xs text-gray-500 mt-1">أو استخدم رابط مباشر:</p>
                <input type="url" name="desktop_image_url" value="{{ old('desktop_image_url') }}" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="https://example.com/desktop.jpg">
                @if($heroSlide?->desktop_image_path || $desktopPreview)
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 bg-red-50 px-3 py-2 rounded-lg cursor-pointer">
                            <input type="checkbox" name="remove_desktop_image" value="1" class="text-red-500" {{ old('remove_desktop_image') ? 'checked' : '' }}>
                            <span>حذف الوسائط الحالية عند الحفظ</span>
                        </label>
                        <span class="text-xs text-gray-500">بعد الحذف يمكنك رفع ملف جديد من جهازك.</span>
                    </div>
                @endif
                @error('desktop_image')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">وسائط الجوال (صورة أو WEBM)</label>
                @if($mobilePreview)
                    <div class="mb-3">
                        @if($mobileIsVideo)
                            <video class="w-full rounded-xl border" controls muted loop playsinline preload="metadata">
                                <source src="{{ $mobilePreview }}" type="video/webm">
                                {{ __('لا يمكن عرض الفيديو في هذا المتصفح.') }}
                            </video>
                        @else
                            <img src="{{ $mobilePreview }}" alt="mobile preview" class="rounded-xl border" loading="lazy">
                        @endif
                    </div>
                @endif
                <div class="space-y-2">
                    <input type="file"
                           id="mobile_image_input"
                           name="mobile_image"
                           accept=".jpg,.jpeg,.png,.gif,.bmp,.svg,.webp,.webm"
                           class="hidden"
                           data-max-size="{{ $maxUploadKilobytes }}"
                           data-max-size-message="لا يمكن رفع ملف أكبر من {{ $maxUploadMegabytes }} ميجابايت."
                           data-error-target="#mobile_image_error">
                    <label for="mobile_image_input" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-dashed border-gray-300 text-gray-700 bg-gray-50 hover:bg-white hover:border-orange-300 transition cursor-pointer">
                        <i class="fas fa-upload text-orange-500"></i>
                        <span>اختر ملفاً مخصصاً للجوال</span>
                    </label>
                    <p class="text-xs text-gray-500" id="mobile_file_name">لم يتم اختيار ملف بعد.</p>
                </div>
                <p class="text-xs text-gray-500 mt-1">استخدم هذا القسم لرفع صورة أو فيديو عمودي من جهازك.</p>
                <p class="text-xs text-gray-500 mt-1">الأنواع المدعومة: JPG, PNG, GIF, SVG, WEBP أو فيديو WEBM (بحد أقصى {{ $maxUploadMegabytes }}MB).</p>
                <p class="text-xs text-orange-600 mt-1">سيتم ضغط الصورة وتحويلها إلى WebP (جودة 80%) مع حد أقصى للعرض 1920px لملاءمة الأجهزة المحمولة.</p>
                <p id="mobile_image_error" class="text-xs text-red-600 mt-1 hidden"></p>
                <p class="text-xs text-gray-500 mt-1">أو استخدم رابط مباشر:</p>
                <input type="url" name="mobile_image_url" value="{{ old('mobile_image_url') }}" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="https://example.com/mobile.jpg">
                @if($heroSlide?->mobile_image_path || $mobilePreview)
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 bg-red-50 px-3 py-2 rounded-lg cursor-pointer">
                            <input type="checkbox" name="remove_mobile_image" value="1" class="text-red-500" {{ old('remove_mobile_image') ? 'checked' : '' }}>
                            <span>حذف وسائط الجوال الحالية عند الحفظ</span>
                        </label>
                        <span class="text-xs text-gray-500">بعد الحذف يمكنك رفع نسخة مخصصة للجوال.</span>
                    </div>
                @endif
                @error('mobile_image')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900">المميزات</h3>
            <button type="button" id="add-feature" class="text-orange-600 text-sm font-semibold">
                <i class="fas fa-plus ml-1"></i> إضافة ميزة
            </button>
        </div>
        <div id="features-list" class="space-y-3">
            @foreach($features as $feature)
                <div class="flex items-center gap-3">
                    <input type="text" name="features[]" value="{{ $feature }}" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="مثال: جلسات تفاعلية محدودة العدد">
                    <button type="button" class="remove-feature text-red-500"><i class="fas fa-times"></i></button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-col gap-2 mb-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">الأزرار (CTA)</h3>
                <button type="button" id="add-action" class="text-orange-600 text-sm font-semibold">
                    <i class="fas fa-plus ml-1"></i> إضافة زر
                </button>
            </div>
            <p class="text-sm text-gray-500">يمكنك استخدام إجراءات ديناميكية لتغيير الرابط حسب حالة المستخدم.</p>
        </div>
        <div id="actions-list" data-next-index="{{ count($actions) }}" class="space-y-4">
            @foreach($actions as $index => $action)
                <div class="border border-gray-100 rounded-2xl p-4 action-card">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">نص الزر</label>
                            <input type="text" name="actions[{{ $index }}][label]" value="{{ $action['label'] }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الأيقونة (اختياري)</label>
                            <input type="text" name="actions[{{ $index }}][icon]" value="{{ $action['icon'] }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="مثال: fas fa-calendar-alt">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">نمط الزر</label>
                            @php
                                $types = ['primary' => 'أساسي', 'secondary' => 'ثانوي', 'accent' => 'مميز', 'ghost' => 'رابط'];
                            @endphp
                            <select name="actions[{{ $index }}][type]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ $action['type'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">نوع الإجراء</label>
                            <select name="actions[{{ $index }}][behavior]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">
                                <option value="static" {{ $action['behavior'] === 'static' ? 'selected' : '' }}>رابط ثابت</option>
                                <option value="create_workshop" {{ $action['behavior'] === 'create_workshop' ? 'selected' : '' }}>زر إنشاء ورشة (ديناميكي)</option>
                                <option value="create_wasfah_link" {{ $action['behavior'] === 'create_wasfah_link' ? 'selected' : '' }}>زر Wasfah Links (ديناميكي)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">الرابط</label>
                            <input type="url" name="actions[{{ $index }}][url]" value="{{ $action['url'] }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="يستخدم فقط مع الروابط الثابتة">
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="actions[{{ $index }}][open_in_new_tab]" value="1" {{ $action['open_in_new_tab'] ? 'checked' : '' }}>
                            فتح في نافذة جديدة
                        </label>
                        <button type="button" class="remove-action text-red-500 text-sm">حذف الزر</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">الحالة</h3>
        <div class="flex items-center gap-4">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-gray-800 font-semibold">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $heroSlide->is_active ?? true) ? 'checked' : '' }}>
                الشريحة نشطة
            </label>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var featuresList = document.getElementById('features-list');
        var addFeatureBtn = document.getElementById('add-feature');
        var actionsList = document.getElementById('actions-list');
        var addActionBtn = document.getElementById('add-action');

        if (addFeatureBtn && featuresList) {
            addFeatureBtn.addEventListener('click', function () {
                var wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-3';
                wrapper.innerHTML = '<input type="text" name="features[]" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="أضف ميزة جديدة">' +
                    '<button type="button" class="remove-feature text-red-500"><i class="fas fa-times"></i></button>';
                featuresList.appendChild(wrapper);
            });

            featuresList.addEventListener('click', function (event) {
                var removeBtn = event.target.closest('.remove-feature');
                if (!removeBtn) {
                    return;
                }
                var row = removeBtn.closest('.flex');
                if (row && featuresList.children.length > 1) {
                    row.remove();
                } else if (row) {
                    var input = row.querySelector('input');
                    if (input) {
                        input.value = '';
                    }
                }
            });
        }

        if (addActionBtn && actionsList) {
            addActionBtn.addEventListener('click', function () {
                var nextIndex = parseInt(actionsList.dataset.nextIndex || actionsList.children.length, 10);
                var template = '' +
                    '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">نص الزر</label>' +
                            '<input type="text" name="actions[__INDEX__][label]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">الأيقونة (اختياري)</label>' +
                            '<input type="text" name="actions[__INDEX__][icon]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="مثال: fas fa-link">' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">نمط الزر</label>' +
                            '<select name="actions[__INDEX__][type]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">' +
                                '<option value="primary">أساسي</option>' +
                                '<option value="secondary">ثانوي</option>' +
                                '<option value="accent">مميز</option>' +
                                '<option value="ghost">رابط</option>' +
                            '</select>' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">نوع الإجراء</label>' +
                            '<select name="actions[__INDEX__][behavior]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200">' +
                                '<option value="static">رابط ثابت</option>' +
                                '<option value="create_workshop">زر إنشاء ورشة</option>' +
                                '<option value="create_wasfah_link">زر Wasfah Links</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="md:col-span-2">' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">الرابط</label>' +
                            '<input type="url" name="actions[__INDEX__][url]" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-200" placeholder="يستخدم فقط مع الروابط الثابتة">' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex items-center justify-between mt-3">' +
                        '<label class="inline-flex items-center gap-2 text-sm text-gray-600">' +
                            '<input type="checkbox" name="actions[__INDEX__][open_in_new_tab]" value="1">' +
                            'فتح في نافذة جديدة' +
                        '</label>' +
                        '<button type="button" class="remove-action text-red-500 text-sm">حذف الزر</button>' +
                    '</div>';
                var card = document.createElement('div');
                card.className = 'border border-gray-100 rounded-2xl p-4 action-card';
                card.innerHTML = template.replace(/__INDEX__/g, nextIndex);
                actionsList.appendChild(card);
                actionsList.dataset.nextIndex = nextIndex + 1;
            });

            actionsList.addEventListener('click', function (event) {
                var removeBtn = event.target.closest('.remove-action');
                if (!removeBtn) {
                    return;
                }
                var card = removeBtn.closest('.action-card');
                if (card) {
                    card.remove();
                }
            });
        }

        function bindUploadPreview(inputId, labelId) {
            var input = document.getElementById(inputId);
            var label = document.getElementById(labelId);

            if (!input || !label) {
                return;
            }

            input.addEventListener('change', function () {
                if (input.files && input.files.length > 0) {
                    label.textContent = input.files[0].name;
                } else {
                    label.textContent = 'لم يتم اختيار ملف بعد.';
                }
            });
        }

        bindUploadPreview('desktop_image_input', 'desktop_file_name');
        bindUploadPreview('mobile_image_input', 'mobile_file_name');
    });
</script>
@endpush

