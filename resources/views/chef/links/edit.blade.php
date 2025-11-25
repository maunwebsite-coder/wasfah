@extends('layouts.app')

@section('title', 'صفحة روابط ' . config('app.name', 'Peahskill') . ' الخاصة بي')

@section('content')
@php
    $brandName = config('app.name', 'Peahskill');
    $brandLinksLabel = $brandName . ' Links';
    $totalItemsCount = $items->count();
    $activeItemsCount = $items->where('is_active', true)->count();
    $inactiveItemsCount = $totalItemsCount - $activeItemsCount;
    $headlineValue = old('headline', $page->headline) ?? '';
    $subheadlineValue = old('subheadline', $page->subheadline) ?? '';
    $bioValue = old('bio', $page->bio) ?? '';
    $ctaLabelValue = old('cta_label', $page->cta_label) ?? '';
    $ctaUrlValue = old('cta_url', $page->cta_url) ?: '#';
    $accentColorValue = old('accent_color', $page->accent_color ?? $accentColor ?? '#0f4c73') ?: '#0f4c73';
    $lastUpdated = $page->updated_at?->locale('ar')->diffForHumans() ?? 'الآن';
    $heroPlaceholder = \App\Support\BrandAssets::logoAsset('webp');
    $heroPreviewDefault = $heroImageUrl ?: $heroPlaceholder;
    $linkImagePlaceholder = \App\Support\BrandAssets::logoAsset('webp');
    $linkImageAllowedList = str_replace(', ', '، ', \App\Support\ImageUploadConstraints::allowedExtensionsList());
    $linkImageMaxSize = \App\Support\ImageUploadConstraints::maxMegabytes();
    $createContextActive = old('form_context') === 'create';
    $createTitle = $createContextActive ? old('title') : '';
    $createSubtitle = $createContextActive ? old('subtitle') : '';
    $createUrl = $createContextActive ? old('url') : '';
    $createIcon = $createContextActive ? old('icon') : '';
    $createIsActive = $createContextActive ? (bool) old('is_active', true) : true;
    $showUpcomingWorkshop = (bool) old('show_upcoming_workshop', $page->show_upcoming_workshop);
    $hasUpcomingWorkshop = (bool) $upcomingWorkshop;
    $countChars = static function ($value) {
        $string = (string) ($value ?? '');
        return function_exists('mb_strlen') ? mb_strlen($string) : strlen($string);
    };
@endphp
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wider text-orange-500 font-semibold mb-2">روابط {{ $brandName }}</p>
                <h1 class="text-3xl font-bold text-gray-900">إدارة صفحة الروابط الخاصة بك</h1>
                <p class="text-gray-600 mt-1">خصص صفحتك الموحدة وشاركها مع جمهورك عبر المنصات الاجتماعية.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center md:justify-end">
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-orange-600 hover:bg-orange-50 transition sm:w-auto">
                    <i class="fas fa-external-link-alt"></i>
                    معاينة الصفحة
                </a>
                <button type="button" data-copy-target="#chef-links-url" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition sm:w-auto">
                    <i class="fas fa-copy"></i>
                    نسخ الرابط
                </button>
                <input id="chef-links-url" type="text" class="sr-only" value="{{ $publicUrl }}" readonly>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                <p class="font-semibold mb-2">حدثت بعض الأخطاء:</p>
                <ul class="list-disc pr-6 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-3 lg:items-start">
            <div class="order-2 space-y-8 lg:order-1 lg:col-span-2">
                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6 sm:py-4">
                        <h2 class="text-lg font-semibold text-gray-800">محتوى الصفحة</h2>
                        <p class="text-sm text-gray-500 mt-1">حدد العناوين والألوان وصورة العرض لتظهر بشكل متناسق مع هويتك.</p>
                    </div>

                    <form action="{{ route('chef.links.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-4 py-5 sm:px-6 sm:py-6">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="headline" class="text-sm font-medium text-gray-700">عنوان الصفحة</label>
                                <input type="text" id="headline" name="headline" value="{{ $headlineValue }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="مثال: روابط الشيف {{ $page->user->name ?? '' }}" data-preview-target="headline" data-counter-source="headline" data-counter-max="80">
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span>حتى 80 حرف</span>
                                    <span class="font-semibold text-gray-500" data-counter-display="headline">{{ $countChars($headlineValue) }} / 80</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="subheadline" class="text-sm font-medium text-gray-700">العنوان الفرعي</label>
                                <input type="text" id="subheadline" name="subheadline" value="{{ $subheadlineValue }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="جملة قصيرة تُعرِّف عنك" data-preview-target="subheadline" data-counter-source="subheadline" data-counter-max="120">
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span>حتى 120 حرف</span>
                                    <span class="font-semibold text-gray-500" data-counter-display="subheadline">{{ $countChars($subheadlineValue) }} / 120</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="bio" class="text-sm font-medium text-gray-700">الوصف التعريفي</label>
                            <textarea id="bio" name="bio" rows="4" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100 resize-none" placeholder="عرّف جمهورك بنفسك وبنوع المحتوى الذي تقدمه." data-preview-target="bio" data-preview-type="multiline" data-counter-source="bio" data-counter-max="240">{{ $bioValue }}</textarea>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>نوصي بكتابة 2-3 جمل مؤثرة</span>
                                <span class="font-semibold text-gray-500" data-counter-display="bio">{{ $countChars($bioValue) }} / 240</span>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="cta_label" class="text-sm font-medium text-gray-700">نص زر الدعوة</label>
                                <input type="text" id="cta_label" name="cta_label" value="{{ $ctaLabelValue }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="مثال: احجز ورشة مباشرة" data-preview-target="cta_label" data-counter-source="cta_label" data-counter-max="40">
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span>حتى 40 حرف</span>
                                    <span class="font-semibold text-gray-500" data-counter-display="cta_label">{{ $countChars($ctaLabelValue) }} / 40</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="cta_url" class="text-sm font-medium text-gray-700">رابط زر الدعوة</label>
                                <input type="url" id="cta_url" name="cta_url" value="{{ old('cta_url', $page->cta_url) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="https://" data-preview-target="cta_url">
                                <p class="text-xs text-gray-500">تأكد من إضافة https:// لبداية الرابط.</p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="accent_color" class="text-sm font-medium text-gray-700">لون التمييز</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="accent_color_picker" value="{{ $accentColorValue }}" class="h-12 w-14 rounded-xl border border-gray-200" data-preview-target="accent_color">
                                    <input type="text" id="accent_color" name="accent_color" value="{{ old('accent_color', $page->accent_color) }}" class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="#0f4c73" data-preview-target="accent_color">
                                </div>
                                <p class="text-xs text-gray-500">يمكنك لصق كود اللون (Hex) أو اختياره من لوحة الألوان.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">حالة النشر</label>
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    <input type="checkbox" name="is_published" value="1" class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                                    إظهار الصفحة للعامة
                                </label>
                                <p class="text-xs text-gray-500">يمكنك إخفاء الصفحة أثناء عملك على تصميمها، وستبقى معاينتها متاحة لك فقط.</p>
                            </div>
                        </div>

                        <div class="space-y-4 rounded-2xl border border-orange-100 bg-orange-50/40 px-5 py-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">إبراز الورشة القادمة</p>
                                    <p class="text-xs text-gray-500 mt-1">فعّل الخيار لإظهار بطاقة الورشة القادمة تلقائياً في صفحة {{ $brandLinksLabel }} الخاصة بك.</p>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $hasUpcomingWorkshop ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="h-2 w-2 rounded-full {{ $hasUpcomingWorkshop ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $hasUpcomingWorkshop ? 'ورشة متاحة' : 'لا توجد ورش نشطة' }}
                                </span>
                            </div>
                            <label class="flex items-start gap-3 rounded-2xl border border-dashed {{ $showUpcomingWorkshop && $hasUpcomingWorkshop ? 'border-orange-300 bg-white' : 'border-orange-200 bg-white/70' }} px-4 py-3 text-sm text-gray-700">
                                <input type="checkbox" name="show_upcoming_workshop" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400" {{ $showUpcomingWorkshop && $hasUpcomingWorkshop ? 'checked' : '' }} {{ $hasUpcomingWorkshop ? '' : 'disabled' }}>
                                <span>
                                    عرض بطاقة الورشة القادمة في الصفحة العامة
                                    @unless ($hasUpcomingWorkshop)
                                        <span class="mt-1 block text-xs text-gray-500">لا توجد ورشة نشطة بتاريخ مستقبلي. أضف ورشة جديدة أو حدّث موعد ورشتك القادمة لتفعيل هذا الخيار.</span>
                                    @endunless
                                </span>
                            </label>
                            @if ($hasUpcomingWorkshop && $upcomingWorkshop)
                                @php
                                    $workshopDate = optional($upcomingWorkshop->start_date)->locale('ar')->translatedFormat('d F Y • h:i a');
                                    $workshopLocation = $upcomingWorkshop->is_online ? 'أونلاين عبر المنصة' : ($upcomingWorkshop->location ?: 'سيتم تحديد الموقع');
                                    $workshopPrice = $upcomingWorkshop->formatted_price ?? (number_format((float) ($upcomingWorkshop->price ?? 0), 2) . ' ' . ($upcomingWorkshop->currency ?? 'USD'));
                                @endphp
                                <div class="rounded-2xl border border-dashed border-orange-200 bg-white/90 px-4 py-4 shadow-sm">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900">{{ $upcomingWorkshop->title }}</h3>
                                            <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($upcomingWorkshop->description ?? 'ورشة مميزة يقدمها الشيف.', 120) }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-600">
                                            <i class="fas fa-bolt"></i>
                        قريباً
                                        </span>
                                    </div>
                                    <div class="mt-4 grid gap-3 text-xs text-gray-600 md:grid-cols-3">
                                        @if ($workshopDate)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-calendar text-orange-500"></i>
                                                <span>{{ $workshopDate }}</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <i class="fas {{ $upcomingWorkshop->is_online ? 'fa-globe' : 'fa-location-dot' }} text-emerald-500"></i>
                                            <span>{{ $workshopLocation }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-money-bill text-amber-500"></i>
                                            <span>{{ $workshopPrice }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex flex-col gap-3 text-xs text-gray-500 sm:flex-row sm:flex-wrap sm:items-center">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                                            <i class="fas fa-users"></i>
                                            {{ number_format($upcomingWorkshop->bookings_count ?? 0) }} حجز
                                        </span>
                                        @if ($upcomingWorkshop->registration_deadline)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                                                <i class="fas fa-hourglass-half"></i>
                                                التسجيل حتى {{ $upcomingWorkshop->registration_deadline->locale('ar')->translatedFormat('d F Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('workshop.show', ['workshop' => $upcomingWorkshop->slug]) }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700 sm:w-auto sm:justify-start">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                            عرض صفحة الورشة
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700">صورة الغلاف</label>
                            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                <div class="h-32 w-32 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 flex items-center justify-center">
                                    <img src="{{ $heroPreviewDefault }}" alt="صورة الصفحة" class="h-full w-full object-cover" id="preview-hero-image" data-original="{{ $heroPreviewDefault }}" data-fallback="{{ $heroPlaceholder }}" loading="lazy" decoding="async" width="128" height="128">
                                </div>
                                <div class="flex-1 space-y-3">
                                    <input type="file"
                                           name="hero_image"
                                           accept="image/*"
                                           class="block w-full rounded-xl border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                           data-preview-target="hero_image"
                                           data-max-size="5120"
                                           data-max-size-message="لا يمكن رفع صورة أكبر من 5 ميجابايت."
                                           data-error-target="#hero_image_error">
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" data-preview-target="remove_hero_image">
                                        إزالة الصورة الحالية
                                    </label>
                                    <p class="text-xs text-gray-500">الحد الأقصى لحجم الصورة 5MB. يفضّل استخدام صورة مربعة عالية الدقة.</p>
                                    <p id="hero_image_error" class="text-xs text-red-600 hidden"></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition sm:w-auto">
                                <i class="fas fa-save"></i>
                                حفظ المحتوى
                            </button>
                            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-3 text-gray-600 hover:bg-gray-50 transition sm:w-auto">
                                <i class="fas fa-eye"></i>
                                مشاهدة الصفحة
                            </a>
                        </div>
                    </form>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6 sm:py-4">
                        <h2 class="text-lg font-semibold text-gray-800">روابط الصفحة</h2>
                        <p class="text-sm text-gray-500 mt-1">أضف الروابط المهمة لجمهورك ورتّبها حسب الأولوية.</p>
                    </div>

                    <div class="space-y-4 px-4 py-5 sm:px-6 sm:py-6">
                        @forelse ($items as $item)
                            @php
                                $isCurrent = (int) old('item_id') === $item->id && old('form_context') !== 'create';
                                $itemTitle = $isCurrent ? old('title', $item->title) : $item->title;
                                $itemSubtitle = $isCurrent ? old('subtitle', $item->subtitle) : $item->subtitle;
                                $itemUrl = $isCurrent ? old('url', $item->url) : $item->url;
                                $itemIcon = $isCurrent ? old('icon', $item->icon) : $item->icon;
                                $itemPosition = $isCurrent ? old('position', $item->position) : $item->position;
                                $itemStoredImageUrl = $item->image_url;
                                $itemImagePreviewId = 'link-image-preview-' . $item->id;
                                $itemRemoveImageChecked = $isCurrent ? (bool) old('remove_image') : false;
                                $itemPreviewImage = $itemRemoveImageChecked ? null : $itemStoredImageUrl;
                            @endphp
                            <details class="group rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-200" @if($isCurrent) open @endif>
                                <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-4 px-5 py-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-300">
                                    <div class="flex flex-1 items-center gap-3 min-w-0">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl text-lg text-white" data-accent-swatch style="background-color: {{ $accentColorValue }};">
                                            <i class="{{ $itemIcon ?: 'fas fa-link' }}"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 truncate">{{ $itemTitle ?: 'رابط بدون عنوان' }}</p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $itemSubtitle ? \Illuminate\Support\Str::limit($itemSubtitle, 60) : \Illuminate\Support\Str::limit($itemUrl, 60) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-3 text-xs sm:flex-row sm:flex-wrap sm:items-center">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 font-semibold {{ $item->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                            <span class="h-2 w-2 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            {{ $item->is_active ? 'مفعل' : 'معطل' }}
                                        </span>
                                        <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-500">#{{ $item->position }}</span>
                                        <button type="button" class="inline-flex w-full items-center justify-center gap-1 rounded-full border border-gray-200 px-3 py-1 font-semibold text-gray-500 hover:text-gray-700 transition sm:w-auto" data-copy-link="{{ $item->url }}">
                                            <i class="fas fa-copy text-xs"></i>
                                            نسخ الرابط
                                        </button>
                                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-open:rotate-180"></i>
                                    </div>
                                </summary>
                                <div class="border-t border-gray-100 px-5 py-5 space-y-4">
                                    <form action="{{ route('chef.links.items.update', $item) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">عنوان الرابط</label>
                                                <input type="text" name="title" value="{{ $itemTitle }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">الوصف المختصر</label>
                                                <input type="text" name="subtitle" value="{{ $itemSubtitle }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="اختياري">
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">الرابط</label>
                                                <input type="url" name="url" value="{{ $itemUrl }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">رمز الأيقونة</label>
                                                <input type="text" name="icon" value="{{ $itemIcon }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="مثال: fas fa-play">
                                                <p class="text-xs text-gray-500">استخدم أيقونات Font Awesome المتاحة.</p>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">الصورة الرئيسية للرابط</label>
                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                                <div class="h-24 w-24 overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50 flex-shrink-0">
                                                    <img src="{{ $itemPreviewImage ?? $linkImagePlaceholder }}" alt="صورة الرابط" class="h-full w-full object-cover" id="{{ $itemImagePreviewId }}" data-default="{{ $itemStoredImageUrl ?? $linkImagePlaceholder }}" data-placeholder="{{ $linkImagePlaceholder }}" loading="lazy" width="96" height="96">
                                                </div>
                                                <div class="flex-1 space-y-2">
                                                    <label class="inline-flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-700 cursor-pointer hover:border-orange-400 hover:text-orange-500 transition">
                                                        <i class="fas fa-upload text-base"></i>
                                                        <span>رفع صورة حديثة</span>
                                                        <input type="file" id="link-image-input-{{ $item->id }}" name="image" accept="image/*" class="sr-only link-image-input" data-preview="#{{ $itemImagePreviewId }}" data-remove-checkbox="#remove-image-{{ $item->id }}">
                                                    </label>
                                                    <label class="flex items-center gap-2 text-sm text-gray-600 {{ $itemStoredImageUrl ? '' : 'opacity-60' }}">
                                                        <input type="checkbox" id="remove-image-{{ $item->id }}" name="remove_image" value="1" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" {{ $itemStoredImageUrl ? '' : 'disabled' }} {{ $itemRemoveImageChecked ? 'checked' : '' }} data-link-image-remove data-preview="#{{ $itemImagePreviewId }}" data-placeholder="{{ $linkImagePlaceholder }}" data-target-input="#link-image-input-{{ $item->id }}">
                                                        إزالة الصورة الرئيسية
                                                    </label>
                                                    <p class="text-xs text-gray-500">ستظهر هذه الصورة كخلفية كاملة للبطاقة. يدعم {{ $linkImageAllowedList }} حتى {{ $linkImageMaxSize }} ميجابايت.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">ترتيب العرض</label>
                                                <input type="number" min="1" name="position" value="{{ $itemPosition }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">حالة الرابط</label>
                                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400" {{ ($isCurrent ? old('is_active') : $item->is_active) ? 'checked' : '' }}>
                                                    نشط
                                                </label>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 text-white text-sm font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition sm:w-auto">
                                                <i class="fas fa-save"></i>
                                                حفظ التعديلات
                                            </button>
                                            <a href="{{ $itemUrl }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition sm:w-auto">
                                                <i class="fas fa-external-link-alt"></i>
                                                فتح الرابط
                                            </a>
                                            <button type="submit" form="delete-link-{{ $item->id }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition sm:w-auto" data-delete-button data-delete-confirm="هل أنت متأكد من رغبتك في حذف هذا الرابط؟">
                                                <i class="fas fa-trash"></i>
                                                حذف الرابط
                                            </button>
                                        </div>
                                    </form>

                                    <form id="delete-link-{{ $item->id }}" action="{{ route('chef.links.items.destroy', $item) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </details>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center text-gray-500 sm:px-6 sm:py-10">
                                <i class="fas fa-link text-3xl text-orange-400 mb-3"></i>
                                <p class="font-semibold text-gray-700 mb-2">لم تضف أي رابط بعد</p>
                                <p class="text-sm mb-0">ابدأ بإضافة أول رابط لك من النموذج أدناه.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="order-1 space-y-8 lg:order-2 lg:sticky lg:top-8">
                <div id="chef-links-preview" class="rounded-3xl border border-orange-100 bg-white shadow-sm overflow-hidden" style="--accent-color: {{ $accentColorValue }};" data-default-color="{{ $accentColorValue }}">
                    <div class="relative h-36" style="background: linear-gradient(135deg, var(--accent-color), rgba(15, 76, 115, 0.3));">
                        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.7), transparent 60%), radial-gradient(circle at 70% 80%, rgba(255,255,255,0.5), transparent 60%);"></div>
                        <div class="relative flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-white">
                            <div class="h-20 w-20 overflow-hidden rounded-2xl border border-white/50 shadow" style="background-color: rgba(255,255,255,0.15);">
                                <img src="{{ $heroPreviewDefault }}" alt="معاينة صورة الغلاف" class="h-full w-full object-cover" id="preview-hero-image-clone" loading="lazy" decoding="async" width="80" height="80">
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-[0.3em] text-white/70">معاينة مباشرة</p>
                                <h3 id="preview-headline" class="text-xl font-bold" data-default="عنوان صفحتك المميز">{{ $headlineValue ?: 'عنوان صفحتك المميز' }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 px-4 py-5 sm:px-6 sm:py-6">
                        <p id="preview-subheadline" class="text-sm font-medium text-gray-800" data-default="عرّف متابعيك بلمحة سريعة عنك">{{ $subheadlineValue ?: 'عرّف متابعيك بلمحة سريعة عنك' }}</p>
                        <p id="preview-bio" class="text-sm text-gray-500 leading-relaxed" data-default="شارك وصفاً قصيراً، روابطك، وأبرز إنجازاتك لتشجيع الجمهور على التفاعل." data-preview-type="multiline">{{ $bioValue ?: 'شارك وصفاً قصيراً، روابطك، وأبرز إنجازاتك لتشجيع الجمهور على التفاعل.' }}</p>
                        <a id="preview-cta" href="{{ $ctaUrlValue }}" class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition" style="background-color: var(--accent-color); box-shadow: 0 14px 28px -16px var(--accent-color);">
                            <span id="preview-cta-label" data-default="أبرز دعوة لاتخاذ إجراء">{{ $ctaLabelValue ?: 'أبرز دعوة لاتخاذ إجراء' }}</span>
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">روابطك البارزة</p>
                            <ul class="space-y-2">
                                @forelse ($items->take(3) as $previewItem)
                                    @php
                                        $previewImage = $previewItem->image_url;
                                        $previewIcon = $previewItem->icon ?: 'fas fa-link';
                                        $previewClasses = 'flex items-center justify-between rounded-2xl border border-gray-100 px-4 py-3 text-sm text-gray-600';
                                        $previewStyle = '';

                                        if ($previewImage) {
                                            $previewClasses = 'flex items-center justify-between rounded-2xl border border-transparent px-4 py-4 text-sm text-white shadow';
                                            $previewStyle = "background-image: linear-gradient(135deg, rgba(15,23,42,0.7), rgba(15,23,42,0.25)), url('{$previewImage}'); background-size: cover; background-position: center;";
                                        }
                                    @endphp
                                    <li class="{{ $previewClasses }}" @if ($previewStyle) style="{{ $previewStyle }}" @endif>
                                        <div class="flex items-center gap-3 min-w-0">
                                            @if (!$previewImage)
                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl text-lg text-white" style="background-color: var(--accent-color);">
                                                    <i class="{{ $previewIcon }}"></i>
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold {{ $previewImage ? 'text-white' : 'text-gray-800' }} truncate">{{ $previewItem->title }}</p>
                                                @if ($previewItem->subtitle)
                                                    <p class="text-xs {{ $previewImage ? 'text-white/80' : 'text-gray-500' }} truncate">{{ \Illuminate\Support\Str::limit($previewItem->subtitle, 50) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-left {{ $previewImage ? 'text-white/80' : 'text-gray-300' }}"></i>
                                    </li>
                                @empty
                                    <li class="rounded-2xl border border-dashed border-gray-200 px-4 py-3 text-center text-xs text-gray-400">
                                        ستظهر هنا أول ثلاثة روابط فعّالة بعد إضافتها.
                                    </li>
                                @endforelse
                                @if ($items->count() > 3)
                                    <li class="rounded-2xl border border-dashed border-gray-200 px-4 py-3 text-center text-xs text-gray-400">
                                        + {{ $items->count() - 3 }} روابط إضافية
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm px-4 py-5 space-y-4 sm:px-6 sm:py-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">حالة الصفحة</p>
                            <p class="text-xs text-gray-500">آخر تحديث {{ $lastUpdated }}</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $page->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="h-2 w-2 rounded-full {{ $page->is_published ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $page->is_published ? 'منشورة' : 'غير منشورة' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 text-center sm:grid-cols-2">
                        <div class="rounded-2xl bg-gray-50 px-3 py-4">
                            <p class="text-xs text-gray-500 mb-1">روابط فعّالة</p>
                            <p class="text-xl font-semibold text-gray-800">{{ $activeItemsCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-3 py-4">
                            <p class="text-xs text-gray-500 mb-1">روابط غير مفعّلة</p>
                            <p class="text-xl font-semibold text-gray-800">{{ $inactiveItemsCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-3 py-4 col-span-2">
                            <p class="text-xs text-gray-500 mb-1">إجمالي الروابط</p>
                            <p class="text-xl font-semibold text-gray-800">{{ $totalItemsCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6 sm:py-4">
                        <h3 class="text-lg font-semibold text-gray-800">إضافة رابط جديد</h3>
                        <p class="text-sm text-gray-500 mt-1">أضف روابط الشبكات الاجتماعية أو الورشات أو أي محتوى تريد الترويج له.</p>
                    </div>

                    @if (!empty($linkPresets))
                        <div class="px-4 pt-5 sm:px-6 sm:pt-6">
                            <div class="rounded-2xl border border-dashed border-orange-200 bg-orange-50/70 px-4 py-4 space-y-3">
                                <p class="text-xs font-semibold uppercase tracking-widest text-orange-600">قوالب جاهزة</p>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($linkPresets as $preset)
                                        <button type="button" class="preset-button inline-flex items-center justify-between gap-3 rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-semibold text-orange-600 hover:bg-orange-100/60 transition" data-preset='@json($preset, JSON_UNESCAPED_UNICODE)' aria-pressed="false">
                                            <span>{{ $preset['label'] }}</span>
                                            <i class="fas fa-magic text-orange-500"></i>
                                        </button>
                                    @endforeach
                                </div>
                                <p class="text-xs text-orange-600/80">انقر على قالب لملء الحقول تلقائياً، ثم عدّلها حسب رغبتك.</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('chef.links.items.store') }}" method="POST" class="px-4 py-5 space-y-4 sm:px-6 sm:py-6" data-new-link-form enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_context" value="create">

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">عنوان الرابط</label>
                            <input type="text" name="title" id="new_link_title" value="{{ $createTitle }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" required data-counter-source="new_link_title" data-counter-max="120">
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>حتى 120 حرف</span>
                                <span class="font-semibold text-gray-500" data-counter-display="new_link_title">{{ $countChars($createTitle) }} / 120</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">الوصف المختصر</label>
                            <input type="text" name="subtitle" id="new_link_subtitle" value="{{ $createSubtitle }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="اختياري" data-counter-source="new_link_subtitle" data-counter-max="160">
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>حتى 160 حرف</span>
                                <span class="font-semibold text-gray-500" data-counter-display="new_link_subtitle">{{ $countChars($createSubtitle) }} / 160</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">الرابط</label>
                            <input type="url" name="url" id="new_link_url" value="{{ $createUrl }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="https://" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">رمز الأيقونة (اختياري)</label>
                            <input type="text" name="icon" id="new_link_icon" value="{{ $createIcon }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-orange-400 focus:ring focus:ring-orange-100" placeholder="مثال: fab fa-instagram">
                            <p class="text-xs text-gray-500">يمكنك العثور على الأيقونات المناسبة من <a href="https://fontawesome.com/search" target="_blank" rel="noopener" class="text-orange-500 underline">مكتبة Font Awesome</a>.</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">الصورة الرئيسية للرابط (اختياري)</label>
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div class="h-24 w-24 overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50 flex-shrink-0">
                                    <img src="{{ $linkImagePlaceholder }}" alt="معاينة صورة الرابط" class="h-full w-full object-cover" id="new_link_image_preview" data-default="{{ $linkImagePlaceholder }}" data-placeholder="{{ $linkImagePlaceholder }}" loading="lazy" width="96" height="96">
                                </div>
                                <div class="flex-1 space-y-2">
                                    <label class="inline-flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-700 cursor-pointer hover:border-orange-400 hover:text-orange-500 transition">
                                        <i class="fas fa-upload text-base"></i>
                                        <span>رفع صورة</span>
                                        <input type="file" name="image" accept="image/*" class="sr-only link-image-input" data-preview="#new_link_image_preview">
                                    </label>
                                    <p class="text-xs text-gray-500">ستغطي هذه الصورة خلفية البطاقة. يدعم {{ $linkImageAllowedList }} حتى {{ $linkImageMaxSize }} ميجابايت.</p>
                                </div>
                            </div>
                        </div>
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400" {{ $createIsActive ? 'checked' : '' }}>
                            تفعيل الرابط فوراً
                        </label>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition">
                            <i class="fas fa-plus"></i>
                            إضافة الرابط
                        </button>
                    </form>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm p-5 space-y-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-800">نصائح سريعة</h3>
                    <ul class="space-y-3 text-sm text-gray-600 leading-relaxed">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-palette text-orange-500 mt-1"></i>
                            اختر لوناً يناسب هويتك البصرية ليظهر على الأزرار والعناصر البارزة. يمكنك مشاهدة النتيجة مباشرة في لوحة المعاينة.
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-grip-lines text-orange-500 mt-1"></i>
                            استخدم ترتيب العرض لتحديد أولويات الروابط؛ الرقم الأصغر يظهر أولاً في صفحتك العامة.
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-icons text-orange-500 mt-1"></i>
                            استعن بقوالب الروابط الجاهزة لتسريع عملية الإضافة، ثم عدّل النصوص بما يناسبك.
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-eye text-orange-500 mt-1"></i>
                            عطّل الروابط مؤقتاً دون حذفها عبر إلغاء تفعيلها، وستظل بياناتها محفوظة لوقت لاحق.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyToClipboard = async (text) => {
        if (!text) {
            return false;
        }

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const tempInput = document.createElement('textarea');
                tempInput.value = text;
                tempInput.style.position = 'fixed';
                tempInput.style.opacity = '0';
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }

            return true;
        } catch (error) {
            console.error('فشل نسخ الرابط', error);
            return false;
        }
    };

    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async function () {
            const targetSelector = this.getAttribute('data-copy-target');
            const input = document.querySelector(targetSelector);

            if (!input) {
                return;
            }

            const success = await copyToClipboard(input.value);

            if (success) {
                this.classList.add('ring-2', 'ring-emerald-300');
                const original = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
                setTimeout(() => {
                    this.classList.remove('ring-2', 'ring-emerald-300');
                    this.innerHTML = original;
                }, 2000);
            }
        });
    });

    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        button.addEventListener('click', async function () {
            const link = this.getAttribute('data-copy-link');
            const success = await copyToClipboard(link);

            if (success) {
                this.classList.add('border-emerald-200', 'text-emerald-600');
                const original = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check text-xs"></i> تم النسخ';
                setTimeout(() => {
                    this.classList.remove('border-emerald-200', 'text-emerald-600');
                    this.innerHTML = original;
                }, 2000);
            }
        });
    });

    const previewRoot = document.getElementById('chef-links-preview');
    const previewHeadline = document.getElementById('preview-headline');
    const previewSubheadline = document.getElementById('preview-subheadline');
    const previewBio = document.getElementById('preview-bio');
    const previewCta = document.getElementById('preview-cta');
    const previewCtaLabel = document.getElementById('preview-cta-label');
    const previewHeroImage = document.getElementById('preview-hero-image');
    const previewHeroImageClone = document.getElementById('preview-hero-image-clone');
    const accentSwatches = document.querySelectorAll('[data-accent-swatch]');

    const updatePreviewText = (element, value) => {
        if (!element) {
            return;
        }

        const defaultText = element.getAttribute('data-default') || element.textContent;
        const isMultiline = element.getAttribute('data-preview-type') === 'multiline';

        if (value && value.trim() !== '') {
            if (isMultiline) {
                element.innerHTML = value.trim().replace(/\\n/g, '<br>');
            } else {
                element.textContent = value.trim();
            }
        } else {
            if (isMultiline) {
                element.innerHTML = defaultText;
            } else {
                element.textContent = defaultText;
            }
        }
    };

    const normalizeHex = (value) => {
        if (!value) {
            return null;
        }

        let hex = value.trim();

        if (!hex) {
            return null;
        }

        if (hex[0] !== '#') {
            hex = '#' + hex;
        }

        return /^#[0-9a-fA-F]{3,8}$/.test(hex) ? hex : null;
    };

    const applyAccentColor = (value) => {
        if (!previewRoot) {
            return;
        }

        const fallback = previewRoot.getAttribute('data-default-color') || '#0f4c73';
        const hex = normalizeHex(value) || fallback;

        previewRoot.style.setProperty('--accent-color', hex);
        accentSwatches.forEach((swatch) => {
            swatch.style.setProperty('background-color', hex);
        });

        if (previewCta) {
            previewCta.style.setProperty('background-color', 'var(--accent-color)');
            previewCta.style.setProperty('box-shadow', '0 14px 28px -16px var(--accent-color)');
        }
    };

    const previewBindings = {
        headline: previewHeadline,
        subheadline: previewSubheadline,
        bio: previewBio,
        cta_label: previewCtaLabel
    };

    document.querySelectorAll('[data-preview-target]').forEach((field) => {
        const target = field.getAttribute('data-preview-target');

        if (!target) {
            return;
        }

        if (target === 'cta_url') {
            field.addEventListener('input', () => {
                if (!previewCta) {
                    return;
                }

                const value = field.value && field.value.trim() !== '' ? field.value.trim() : '#';
                previewCta.setAttribute('href', value);
            });

            return;
        }

        if (target === 'accent_color') {
            field.addEventListener('input', () => applyAccentColor(field.value));
            return;
        }

        if (previewBindings[target]) {
            field.addEventListener('input', () => updatePreviewText(previewBindings[target], field.value));
        }
    });

    const counters = {};
    const updateCounter = (key, display, max) => {
        const field = counters[key];

        if (!field || !display) {
            return;
        }

        const value = field.value || '';
        const count = Array.from(value).length;

        display.textContent = max ? count + ' / ' + max : String(count);
    };

    document.querySelectorAll('[data-counter-source]').forEach((field) => {
        const key = field.getAttribute('data-counter-source');
        const display = document.querySelector('[data-counter-display=\"' + key + '\"]');
        const max = parseInt(field.getAttribute('data-counter-max') || '', 10) || null;
        counters[key] = field;

        if (!display) {
            return;
        }

        const handler = () => updateCounter(key, display, max);
        field.addEventListener('input', handler);
        handler();
    });

    const heroImageInput = document.querySelector('input[name=\"hero_image\"]');
    const removeHeroCheckbox = document.querySelector('input[name=\"remove_hero_image\"]');
    const heroOriginalSrc = previewHeroImage ? (previewHeroImage.getAttribute('data-original') || previewHeroImage.src) : '';
    const heroFallbackSrc = previewHeroImage ? (previewHeroImage.getAttribute('data-fallback') || heroOriginalSrc) : '';
    let heroSelectedSrc = heroOriginalSrc;

    const renderHeroImage = (src) => {
        const finalSrc = src || heroFallbackSrc || heroOriginalSrc;

        if (previewHeroImage) {
            previewHeroImage.src = finalSrc;
        }

        if (previewHeroImageClone) {
            previewHeroImageClone.src = finalSrc;
        }
    };

    if (heroImageInput) {
        heroImageInput.addEventListener('change', () => {
            const file = heroImageInput.files && heroImageInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    if (event.target && event.target.result) {
                        heroSelectedSrc = event.target.result;
                        renderHeroImage(heroSelectedSrc);
                        if (removeHeroCheckbox) {
                            removeHeroCheckbox.checked = false;
                        }
                    }
                };
                reader.readAsDataURL(file);
            } else {
                heroSelectedSrc = heroOriginalSrc;
                renderHeroImage(heroSelectedSrc);
            }
        });
    }

    if (removeHeroCheckbox) {
        removeHeroCheckbox.addEventListener('change', () => {
            if (removeHeroCheckbox.checked) {
                renderHeroImage(heroFallbackSrc);
            } else {
                renderHeroImage(heroSelectedSrc);
            }
        });
    }

    renderHeroImage(heroOriginalSrc);

    const getPreviewFallback = (preview) => {
        if (!preview) {
            return '';
        }

        return preview.getAttribute('data-default') || preview.getAttribute('data-placeholder') || preview.src;
    };

    const getPreviewPlaceholder = (preview) => {
        if (!preview) {
            return '';
        }

        return preview.getAttribute('data-placeholder') || preview.getAttribute('data-default') || preview.src;
    };

    document.querySelectorAll('.link-image-input').forEach((input) => {
        input.addEventListener('change', () => {
            const previewSelector = input.getAttribute('data-preview');
            const preview = previewSelector ? document.querySelector(previewSelector) : null;

            if (!preview) {
                return;
            }

            const file = input.files && input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    if (event.target && typeof event.target.result === 'string') {
                        preview.src = event.target.result;
                        preview.setAttribute('data-current-upload', event.target.result);
                    }
                };
                reader.readAsDataURL(file);

                const removeSelector = input.getAttribute('data-remove-checkbox');
                if (removeSelector) {
                    const removeCheckbox = document.querySelector(removeSelector);
                    if (removeCheckbox) {
                        removeCheckbox.checked = false;
                        removeCheckbox.disabled = false;
                    }
                }
            } else {
                preview.removeAttribute('data-current-upload');
                const fallback = getPreviewFallback(preview);
                preview.src = fallback;
            }
        });
    });

    document.querySelectorAll('[data-link-image-remove]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const previewSelector = checkbox.getAttribute('data-preview');
            const preview = previewSelector ? document.querySelector(previewSelector) : null;

            if (!preview) {
                return;
            }

            if (checkbox.checked) {
                const targetInputSelector = checkbox.getAttribute('data-target-input');
                if (targetInputSelector) {
                    const targetInput = document.querySelector(targetInputSelector);
                    if (targetInput) {
                        targetInput.value = '';
                    }
                }

                preview.removeAttribute('data-current-upload');
                const placeholder = checkbox.getAttribute('data-placeholder') || getPreviewPlaceholder(preview);
                preview.src = placeholder;
            } else {
                const uploadPreview = preview.getAttribute('data-current-upload');
                if (uploadPreview) {
                    preview.src = uploadPreview;
                    return;
                }

                const fallback = getPreviewFallback(preview);
                preview.src = fallback;
            }
        });
    });

    const colorPicker = document.getElementById('accent_color_picker');
    const colorInput = document.getElementById('accent_color');
    const initialAccent = colorInput?.value || colorPicker?.value || (previewRoot ? previewRoot.getAttribute('data-default-color') : '#0f4c73');
    applyAccentColor(initialAccent);

    if (colorPicker && colorInput) {
        colorPicker.addEventListener('input', () => {
            colorInput.value = colorPicker.value;
            applyAccentColor(colorPicker.value);
        });

        colorInput.addEventListener('input', () => {
            const normalized = normalizeHex(colorInput.value);
            if (normalized) {
                colorPicker.value = normalized;
            }

            applyAccentColor(colorInput.value);
        });
    }

    updatePreviewText(previewHeadline, document.getElementById('headline')?.value);
    updatePreviewText(previewSubheadline, document.getElementById('subheadline')?.value);
    updatePreviewText(previewBio, document.getElementById('bio')?.value);
    updatePreviewText(previewCtaLabel, document.getElementById('cta_label')?.value);

    document.querySelectorAll('[data-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const preset = JSON.parse(button.getAttribute('data-preset') || '{}');
            const titleInput = document.getElementById('new_link_title');
            const subtitleInput = document.getElementById('new_link_subtitle');
            const urlInput = document.getElementById('new_link_url');
            const iconInput = document.getElementById('new_link_icon');

            if (titleInput && preset.title) {
                titleInput.value = preset.title;
                titleInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (subtitleInput) {
                subtitleInput.value = preset.subtitle || '';
                subtitleInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (urlInput && preset.url) {
                urlInput.value = preset.url;
            }

            if (iconInput) {
                iconInput.value = preset.icon || '';
            }

            document.querySelectorAll('.preset-button[aria-pressed=\"true\"]').forEach((el) => {
                el.classList.remove('bg-orange-100/80', 'border-orange-300');
                el.setAttribute('aria-pressed', 'false');
            });

            button.classList.add('bg-orange-100/80', 'border-orange-300');
            button.setAttribute('aria-pressed', 'true');
        });
    });

    document.querySelectorAll('[data-delete-button]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.getAttribute('data-delete-confirm') || 'هل أنت متأكد من رغبتك في حذف هذا الرابط؟';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endpush




