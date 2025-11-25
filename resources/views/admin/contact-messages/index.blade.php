@extends('layouts.app')

@section('title', 'إدارة رسائل التواصل - وصفة')

@php
    $statusLabels = [
        'all' => 'كل الرسائل',
        \App\Models\ContactMessage::STATUS_PENDING => 'بانتظار المراجعة',
        \App\Models\ContactMessage::STATUS_NOTIFIED => 'تم إشعار الفريق',
    ];

    $statusBadgeClasses = [
        \App\Models\ContactMessage::STATUS_PENDING => 'bg-amber-50 text-amber-700 border border-amber-200',
        \App\Models\ContactMessage::STATUS_NOTIFIED => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    ];

    $baseQuery = array_filter([
        'status' => $status !== 'all' ? $status : null,
        'subject' => $subjectFilter !== 'all' ? $subjectFilter : null,
        'search' => $searchTerm ?: null,
        'per_page' => $perPage !== 25 ? $perPage : null,
    ], function ($value) {
        return !is_null($value) && $value !== '';
    });

    $pageAwareQuery = $baseQuery;
    if ($messages->currentPage() > 1) {
        $pageAwareQuery['page'] = $messages->currentPage();
    }

    $activeFilters = [];

    if (isset($baseQuery['status'])) {
        $activeFilters[] = [
            'label' => 'الحالة',
            'value' => $statusLabels[$baseQuery['status']] ?? $baseQuery['status'],
        ];
    }

    if (isset($baseQuery['subject'])) {
        $activeFilters[] = [
            'label' => 'الموضوع',
            'value' => $subjectOptions[$baseQuery['subject']] ?? $baseQuery['subject'],
        ];
    }

    if (isset($baseQuery['search'])) {
        $activeFilters[] = [
            'label' => 'البحث',
            'value' => $baseQuery['search'],
        ];
    }

    if (isset($baseQuery['per_page'])) {
        $activeFilters[] = [
            'label' => 'عدد النتائج',
            'value' => $baseQuery['per_page'] . ' / صفحة',
        ];
    }
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 space-y-8">
    <div class="relative overflow-hidden rounded-3xl border border-slate-100 bg-gradient-to-l from-amber-50 via-white to-white px-6 py-8 shadow-sm">
        <div class="pointer-events-none absolute -right-10 top-0 h-40 w-40 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 left-12 h-32 w-32 rounded-full bg-rose-100/60 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="text-slate-400">الرئيسية</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-400">منطقة الإدمن</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">Contact Messages</span>
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/80 text-amber-500 shadow-inner shadow-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5l8.25 5.25L20.25 7.5" />
                            <rect width="18" height="13.5" x="3" y="5.25" rx="2.25" fill="none" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-slate-500">لوحة الإدارة</p>
                        <h1 class="text-3xl font-black text-slate-900 sm:text-4xl">صندوق رسائل Wasfah</h1>
                    </div>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-slate-600">
                    تابع طلبات الشراكات والاستفسارات القادمة من نموذج الاتصال العام، وراقب سير معالجتها من مكان واحد.
                </p>
                <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1 font-semibold text-slate-700 shadow-sm">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
                        إجمالي الرسائل: {{ number_format($statusTotals['all'] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1 font-semibold text-amber-700 shadow-sm">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                        بانتظار المتابعة: {{ number_format($statusTotals[\App\Models\ContactMessage::STATUS_PENDING] ?? 0) }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1 font-semibold text-emerald-700 shadow-sm">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        تم إشعار الفريق: {{ number_format($statusTotals[\App\Models\ContactMessage::STATUS_NOTIFIED] ?? 0) }}
                    </span>
                </div>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 backdrop-blur hover:border-slate-300 hover:text-slate-900">
                    لوحة التحكم
                </a>
                <a href="{{ route('admin.admin-area') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 hover:bg-black">
                    منطقة الإدمن
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-100 bg-white/95 px-5 py-5 shadow-sm ring-1 ring-black/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">إجمالي الرسائل</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($statusTotals['all'] ?? 0) }}</p>
                    <p class="text-xs text-slate-500">يشمل كل المواضيع والحالات</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-inner shadow-slate-900/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10M7 16h6" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-5 py-5 shadow-sm ring-1 ring-amber-100/60">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">بانتظار المتابعة</p>
                    <p class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($statusTotals[\App\Models\ContactMessage::STATUS_PENDING] ?? 0) }}</p>
                    <p class="text-xs text-amber-600">رسائل تحتاج تدخل إداري</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 text-amber-500 shadow-inner shadow-amber-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-5 py-5 shadow-sm ring-1 ring-emerald-100/60">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">تم إشعار الفريق</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($statusTotals[\App\Models\ContactMessage::STATUS_NOTIFIED] ?? 0) }}</p>
                    <p class="text-xs text-emerald-600">تم تسليمها عبر البريد</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 text-emerald-500 shadow-inner shadow-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white/95 px-6 py-7 shadow-sm space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">فلاتر البحث الذكية</h2>
                <p class="text-sm text-slate-500">حدد الحالة أو الموضوع أو استخدم البحث للوصول السريع لرسالة معينة.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-slate-400">{{ number_format($messages->total()) }} رسالة قابلة للتصفية</span>
                @if(!empty($baseQuery))
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">
                        إعادة تعيين الفلاتر
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-3">
            @if(!empty($activeFilters))
                <div class="flex flex-wrap gap-2 text-xs">
                    @foreach($activeFilters as $filter)
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600">
                            <span class="text-slate-400">{{ $filter['label'] }}:</span>
                            <span class="text-slate-800">{{ $filter['value'] }}</span>
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400">لا توجد فلاتر مفعّلة حالياً، سيتم إظهار كل الرسائل.</p>
            @endif
        </div>

        @if(request()->filled('message') && !$selectedMessage)
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                تعذر العثور على الرسالة المطلوبة. ربما تمت إزالتها أو تغيير معرّفها.
                <a href="{{ route('admin.contact-messages.index', $baseQuery) }}" class="font-semibold underline-offset-4 hover:underline">إخفاء التنبيه</a>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="grid gap-4 lg:grid-cols-5">
            <div class="space-y-2">
                <label for="status" class="text-sm font-semibold text-slate-700">حالة الرسائل</label>
                <div class="relative">
                    <select id="status" name="status" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white/70 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="space-y-2">
                <label for="subject" class="text-sm font-semibold text-slate-700">موضوع الرسالة</label>
                <div class="relative">
                    <select id="subject" name="subject" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white/70 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        <option value="all" @selected($subjectFilter === 'all')>كل المواضيع</option>
                        @foreach($subjectOptions as $key => $label)
                            <option value="{{ $key }}" @selected($subjectFilter === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="space-y-2 lg:col-span-2">
                <label for="search" class="text-sm font-semibold text-slate-700">كلمة البحث</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 17a6.5 6.5 0 1 1 0-13 6.5 6.5 0 0 1 0 13Z" />
                        </svg>
                    </span>
                    <input id="search" name="search" value="{{ $searchTerm }}" placeholder="ابحث بالاسم أو البريد أو الهاتف" class="w-full rounded-2xl border border-slate-200 bg-white/70 px-4 py-2.5 pl-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" />
                </div>
            </div>
            <div class="space-y-2">
                <label for="per_page" class="text-sm font-semibold text-slate-700">عدد النتائج في الصفحة</label>
                <div class="relative">
                    <select id="per_page" name="per_page" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white/70 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} / صفحة</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="lg:col-span-5 flex flex-wrap gap-3 pt-1">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:-translate-y-0.5 hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-200">
                    تطبيق الفلاتر
                </button>
                @if(!empty($baseQuery))
                    <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                        مسح
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4 rounded-3xl border border-slate-100 bg-white/95 px-6 py-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">قائمة الرسائل</h2>
                    <p class="text-sm text-slate-500">
                        {{ number_format($messages->total()) }} رسالة • الصفحة {{ $messages->currentPage() }} من {{ max(1, $messages->lastPage()) }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                        {{ $statusLabels[$status] ?? 'كل الرسائل' }}
                    </span>
                    <span class="text-[11px] text-slate-400">ترتيب حسب الأحدث</span>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-right">المرسل</th>
                                <th class="px-4 py-3 text-right">الموضوع</th>
                                <th class="px-4 py-3 text-right">الحالة</th>
                                <th class="px-4 py-3 text-right">تاريخ الإرسال</th>
                                <th class="px-4 py-3 text-right">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($messages as $message)
                                @php
                                    $isSelected = $selectedMessage && $selectedMessage->id === $message->id;
                                    $messageQuery = array_merge($pageAwareQuery, ['message' => $message->id]);
                                @endphp
                                <tr class="{{ $isSelected ? 'bg-amber-50/70' : 'hover:bg-slate-50/70' }}">
                                    <td class="px-4 py-4 align-top">
                                        <div class="font-semibold text-slate-900">{{ $message->full_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $message->email }}</div>
                                        @if($message->phone)
                                            <div class="text-xs text-slate-400">{{ $message->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm font-semibold text-slate-900">{{ $message->subject_label }}</div>
                                        <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</p>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClasses[$message->status] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ $statusLabels[$message->status] ?? $message->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 align-top text-sm text-slate-600">
                                        {{ optional($message->created_at)->locale('ar')->translatedFormat('d F Y • h:i a') }}
                                        <div class="text-xs text-slate-400">{{ optional($message->created_at)->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <a href="{{ route('admin.contact-messages.index', $messageQuery) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-amber-200 hover:text-amber-600">
                                            عرض التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                        لا توجد رسائل تطابق معايير البحث الحالية.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                @if($messages->total())
                    <p>إظهار {{ number_format($messages->firstItem() ?? 0) }}-{{ number_format($messages->lastItem() ?? 0) }} من {{ number_format($messages->total()) }} نتيجة</p>
                @else
                    <p>لا توجد نتائج لعرضها.</p>
                @endif
                {{ $messages->links() }}
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white/95 px-6 py-6 shadow-sm space-y-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-slate-900">تفاصيل الرسالة</h2>
                    <p class="text-sm text-slate-500">اختر رسالة من الجدول لعرض بياناتها كاملة.</p>
                </div>
                @if($selectedMessage)
                    <a href="{{ route('admin.contact-messages.index', $baseQuery) }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        إغلاق
                    </a>
                @endif
            </div>

            @if($selectedMessage)
                @php
                    $meta = $selectedMessage->meta ?? [];
                @endphp
                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-base font-semibold text-slate-900">{{ $selectedMessage->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $selectedMessage->email }}</div>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClasses[$selectedMessage->status] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $statusLabels[$selectedMessage->status] ?? $selectedMessage->status }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ optional($selectedMessage->created_at)->locale('ar')->translatedFormat('d F Y • h:i a') }}
                                </span>
                                <span class="text-slate-400">تم الإرسال عبر نموذج الموقع</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">موضوع الرسالة</p>
                            <p class="mt-1 text-base font-semibold text-slate-900">{{ $selectedMessage->subject_label }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">معلومات التواصل</p>
                            <ul class="mt-2 space-y-2 text-sm text-slate-600">
                                <li>
                                    البريد:
                                    <a href="mailto:{{ $selectedMessage->email }}" class="font-semibold text-amber-600 hover:text-amber-700">
                                        {{ $selectedMessage->email }}
                                    </a>
                                </li>
                                @if($selectedMessage->phone)
                                    <li>
                                        الهاتف:
                                        <a href="tel:{{ $selectedMessage->phone }}" class="font-semibold text-amber-600 hover:text-amber-700">
                                            {{ $selectedMessage->phone }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">محتوى الرسالة</p>
                        <div class="mt-2 rounded-2xl border border-slate-100 bg-white px-4 py-4 text-sm leading-relaxed text-slate-800 whitespace-pre-line">
                            {!! nl2br(e($selectedMessage->message)) !!}
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">بيانات إضافية</p>
                        @if(!empty($meta))
                            <dl class="mt-3 space-y-2 text-sm text-slate-600">
                                @if(!empty($meta['source']))
                                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                        <dt class="font-semibold text-slate-500">المصدر</dt>
                                        <dd class="text-slate-800">{{ $meta['source'] }}</dd>
                                    </div>
                                @endif
                                @if(!empty($meta['ip']))
                                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                        <dt class="font-semibold text-slate-500">عنوان IP</dt>
                                        <dd class="text-slate-800">{{ $meta['ip'] }}</dd>
                                    </div>
                                @endif
                                @if(!empty($meta['user_agent']))
                                    <div class="rounded-xl bg-slate-50 px-3 py-2">
                                        <dt class="font-semibold text-slate-500">المتصفح</dt>
                                        <dd class="text-slate-800 text-xs leading-5">{{ $meta['user_agent'] }}</dd>
                                    </div>
                                @endif
                                @if(!empty($meta['mail_error']))
                                    <div class="rounded-xl bg-red-50 px-3 py-2">
                                        <dt class="font-semibold text-red-600">خطأ البريد</dt>
                                        <dd class="text-red-700 text-xs leading-5">{{ $meta['mail_error'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @else
                            <p class="mt-2 text-xs text-slate-400">لا توجد بيانات إضافية لهذه الرسالة.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-200 px-6 py-12 text-center text-sm text-slate-500">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m9-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    اختر أي رسالة من القائمة لعرض تفاصيلها هنا.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

