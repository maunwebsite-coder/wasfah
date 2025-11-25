@extends('layouts.app')

@section('title', 'إدارة الورشات - لوحة الإدارة')

@section('content')
@php
    $filters = $filters ?? [
        'search' => request('search', ''),
        'status' => request('status', 'all'),
        'mode' => request('mode', 'all'),
        'featured' => request('featured', 'all'),
        'time' => request('time', 'all'),
    ];

    $hasActiveFilters = $hasActiveFilters ?? (
        ($filters['search'] ?? '') !== ''
        || collect($filters)->except('search')->contains(fn ($value) => $value !== 'all')
    );

    $statusOptions = [
        'all' => 'كل الحالات',
        'active' => 'نشطة',
        'inactive' => 'غير نشطة',
    ];

    $modeOptions = [
        'all' => 'كل الأنماط',
        'online' => 'أونلاين',
        'offline' => 'حضوري',
    ];

    $featuredOptions = [
        'all' => 'الكل',
        'featured' => 'مميزة فقط',
        'regular' => 'غير مميزة',
    ];

    $timeOptions = [
        'all' => 'كل الأوقات',
        'upcoming' => 'قادمة',
        'past' => 'منتهية',
    ];
    $currencyOptions = \App\Support\Currency::all();
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-3xl border border-orange-100 bg-white shadow-xl mb-10">
            <div class="absolute inset-0 bg-gradient-to-l from-orange-50 via-white to-white opacity-90"></div>
            <div class="absolute -top-16 -right-20 h-40 w-40 rounded-full bg-amber-200 opacity-30"></div>
            <div class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-orange-300 opacity-20"></div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-8 p-8 md:p-10">
                <div class="flex-1">
                    <span class="inline-flex items-center px-4 py-1 rounded-full bg-orange-100 text-orange-700 font-semibold text-xs tracking-wide">
                        <i class="fas fa-chalkboard-teacher ml-2"></i>
                        لوحة إدارة الورشات
                    </span>
                    <h1 class="mt-4 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
                        نظرة شاملة على الورشات مع تصميم أكثر وضوحاً وأناقة
                    </h1>
                    <p class="mt-3 text-base md:text-lg text-gray-600 max-w-3xl leading-relaxed">
                        تمت إعادة ترتيب الصفحة لتمنحك تجربة استخدام أفضل: مسارات بصرية واضحة، بطاقات معلومات متناسقة،
                        وإبراز فوري للورشة القادمة لضمان سرعة اتخاذ القرار.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center text-sm text-gray-500 gap-3">
                        <span class="flex items-center">
                            <span class="ml-2 inline-block h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                            تحديث الإحصائيات يتم مباشرة من قاعدة البيانات
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-magic ml-2 text-orange-400"></i>
                            تأثيرات مرئية خفيفة دون التأثير على الأداء
                        </span>
                    </div>
                </div>

                <div class="w-full md:w-80 flex flex-col gap-4">
                    <a href="{{ route('admin.workshops.create') }}"
                       class="group flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-orange-500 via-orange-400 to-amber-400 px-6 py-4 text-white font-semibold shadow-lg transition transform hover:-translate-y-1 hover:shadow-2xl">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white bg-opacity-20 text-xl transition group-hover:bg-opacity-30">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text-lg">إضافة ورشة جديدة</span>
                    </a>

                    @if(isset($featuredWorkshop) && $featuredWorkshop)
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400 text-white shadow-xl">
                            <div class="absolute -top-10 -right-12 h-32 w-32 rounded-full bg-white opacity-20 blur-lg"></div>
                            <div class="absolute bottom-0 left-0 h-24 w-24 rounded-full bg-white opacity-10 blur-lg"></div>

                            <div class="relative flex items-start gap-4 p-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white bg-opacity-20 shadow-inner">
                                    <i class="fas fa-crown text-xl featured-badge animate-pulse"></i>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white text-opacity-80">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="inline-block h-2 w-2 rounded-full bg-white animate-ping"></span>
                                            الورشة القادمة
                                        </span>
                                        <span class="rounded-full bg-white bg-opacity-20 px-2 py-0.5">مميزة</span>
                                    </div>
                                    <h2 class="text-lg font-bold leading-tight">{{ $featuredWorkshop->title }}</h2>
                                    @if($featuredWorkshop->start_date)
                                        <p class="text-sm text-white text-opacity-80">
                                            {{ $featuredWorkshop->start_date->format('d/m/Y - h:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-white bg-opacity-80 p-6 text-center shadow-sm">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full border border-gray-200 bg-gray-50 text-gray-500">
                                <i class="fas fa-crown text-lg"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">لا توجد ورشة مميزة حتى الآن</p>
                            <p class="mt-1 text-xs text-gray-500">تمييز ورشة ما يعرضها هنا لتكون في عين الفريق أولاً.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-8">
            <form method="GET" action="{{ route('admin.workshops.index') }}"
                  class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 p-4 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1" for="search">
                                بحث عن ورشة أو مدرب
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-orange-500">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="مثال: ورشة المخبوزات أو اسم المدرب"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pr-10 pl-4 text-sm text-gray-700 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100 transition"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1" for="status">
                                حالة الورشة
                            </label>
                            <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-700 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1" for="mode">
                                نوع الحضور
                            </label>
                            <select id="mode" name="mode" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-700 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                                @foreach($modeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['mode'] ?? 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1" for="featured">
                                حالة التمييز
                            </label>
                            <select id="featured" name="featured" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-700 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                                @foreach($featuredOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['featured'] ?? 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1" for="time">
                                الإطار الزمني
                            </label>
                            <select id="time" name="time" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-700 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                                @foreach($timeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['time'] ?? 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        @if($hasActiveFilters)
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-orange-600 font-semibold">
                                    <i class="fas fa-filter"></i>
                                    عوامل تصفية مفعّلة
                                </span>
                                @if(($filters['search'] ?? '') !== '')
                                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-gray-600">
                                        <i class="fas fa-search"></i>
                                        بحث: "{{ $filters['search'] }}"
                                    </span>
                                @endif
                                @foreach(['status' => $statusOptions, 'mode' => $modeOptions, 'featured' => $featuredOptions, 'time' => $timeOptions] as $key => $options)
                                    @php
                                        $value = $filters[$key] ?? 'all';
                                    @endphp
                                    @if($value !== 'all')
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-gray-600">
                                            <i class="fas fa-check-circle text-gray-400"></i>
                                            {{ $options[$value] ?? $value }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">
                                استخدم البحث والمرشحات للحصول على الورشة المطلوبة بسرعة.
                            </p>
                        @endif

                        <div class="flex items-center gap-3">
                            @if($hasActiveFilters)
                                <a href="{{ route('admin.workshops.index') }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                                    <i class="fas fa-undo-alt"></i>
                                    إعادة الضبط
                                </a>
                            @endif
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-400 px-5 py-2 text-sm font-semibold text-white shadow-md transition transform hover:-translate-y-0.5 hover:shadow-lg">
                                <i class="fas fa-check"></i>
                                تطبيق المرشحات
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">
            <div class="group relative overflow-hidden rounded-2xl border border-orange-100 bg-white p-6 shadow-sm transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-white opacity-0 transition-opacity group-hover:opacity-100"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">إجمالي الورشات</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total'] ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">جميع الورشات النشطة والمؤرشفة</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-500 shadow-inner">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl border border-green-100 bg-white p-6 shadow-sm transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-white opacity-0 transition-opacity group-hover:opacity-100"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">ورشات نشطة</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['active'] ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">متاحة حالياً للحجز أو العرض</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-500 shadow-inner">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-6 shadow-sm transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 transition-opacity group-hover:opacity-100"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">إجمالي الحجوزات المؤكدة</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['confirmed_bookings'] ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">حجوزات تم تأكيدها حتى الآن</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-500 shadow-inner">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl border border-purple-100 bg-white p-6 shadow-sm transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-white opacity-0 transition-opacity group-hover:opacity-100"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">ورشات أونلاين</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['online'] ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">جلسات يمكن الانضمام لها عن بُعد</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-500 shadow-inner">
                        <i class="fas fa-laptop text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workshops Table -->
        <div class="rounded-3xl border border-gray-100 bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-orange-50 to-white px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">قائمة الورشات</h2>
                    <p class="text-sm text-gray-500 mt-1">استعرض تفاصيل الورشات وحالة الحجز والتمييز لكل ورشة.</p>
                    <p class="text-xs text-gray-400 mt-2">{{ number_format($workshops->total()) }} نتيجة</p>
                </div>
                <div class="hidden md:flex items-center gap-3 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        نشطة
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                        غير نشطة
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-right">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">الورشة</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">المدرب</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">التاريخ</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">السعر</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">الحجوزات</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">الحالة</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">الورشة القادمة</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-gray-500">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workshops as $workshop)
                        @php
                            $rowClasses = $workshop->is_featured ? 'featured-row bg-orange-50' : '';
                            $isOnline = $workshop->is_online;
                            $isUpcoming = $workshop->start_date?->isFuture();
                        @endphp
                        <tr class="group transition duration-200 hover:bg-gray-50 {{ $rowClasses }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="{{ $workshop->image ? asset('storage/' . $workshop->image) : 'https://placehold.co/60x60/f87171/FFFFFF?text=ورشة' }}"
                                         alt="{{ $workshop->title }}"
                                         class="w-12 h-12 rounded-xl object-cover ml-4 border border-white shadow-sm" loading="lazy">
                                    <div class="space-y-1">
                                        <h3 class="font-semibold text-gray-800">{{ $workshop->title }}</h3>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 {{ $isOnline ? 'bg-purple-50 text-purple-600' : 'bg-green-50 text-green-600' }}">
                                                <i class="fas {{ $isOnline ? 'fa-wifi' : 'fa-map-marker-alt' }}"></i>
                                                {{ $isOnline ? 'أونلاين' : 'حضوري' }}
                                            </span>
                                            @if($workshop->category)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-gray-600">
                                                    <i class="fas fa-folder-open text-xs"></i>
                                                    {{ $workshop->category }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 max-w-xs">
                                    <span class="font-medium text-gray-800 truncate">{{ $workshop->instructor }}</span>
                                    @if($workshop->instructor_bio)
                                        <span class="text-xs text-gray-400 truncate">{{ \Illuminate\Support\Str::limit($workshop->instructor_bio, 70) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm space-y-1">
                                    <p class="font-semibold text-gray-800">{{ $workshop->start_date->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $workshop->start_date->format('H:i') }}</p>
                                    <p class="text-xs {{ $isUpcoming ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $workshop->start_date->diffForHumans() }}
                                    </p>
                                    @if($workshop->end_date)
                                        <p class="text-xs text-gray-400">حتى {{ $workshop->end_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-800">
                                        {{ number_format($workshop->price, 2) }}
                                        {{ $currencyOptions[$workshop->currency]['symbol'] ?? $workshop->currency }}
                                        <span class="text-xs text-gray-500">({{ $workshop->currency }})</span>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $currencyOptions[$workshop->currency]['label'] ?? $workshop->currency }}
                                    </span>
                                    @if($workshop->duration)
                                        <span class="text-xs text-gray-400">{{ $workshop->duration }} دقيقة</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-semibold text-gray-800">{{ $workshop->bookings_count }}</span>
                                    <span class="text-gray-400">/ {{ $workshop->max_participants }}</span>
                                    @if($workshop->total_bookings > $workshop->bookings_count)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs text-orange-600">
                                            <i class="fas fa-clock"></i>
                                            {{ $workshop->total_bookings - $workshop->bookings_count }} معلق
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $workshop->is_active ? 'bg-green-50 text-green-600 border-green-200' : 'bg-rose-50 text-rose-600 border-rose-200' }}">
                                        {{ $workshop->is_active ? 'نشطة' : 'غير نشطة' }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        آخر تحديث {{ $workshop->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($workshop->is_featured)
                                    <div class="flex items-center gap-3 text-sm">
                                        <div class="relative inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-4 py-2 text-white font-semibold shadow-lg">
                                            <i class="fas fa-crown featured-badge"></i>
                                            الورشة القادمة
                                            <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-white animate-ping"></span>
                                            <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-white"></span>
                                        </div>
                                        <span class="text-xs text-amber-600 font-bold flex items-center gap-1">
                                            <i class="fas fa-star text-yellow-500"></i>
                                            مميزة
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <i class="fas fa-circle text-xs"></i>
                                        عادية
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.workshops.show', $workshop->id) }}"
                                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 transition transform hover:-translate-y-0.5 hover:bg-blue-100"
                                       title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.workshops.edit', $workshop->id) }}"
                                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-orange-100 bg-orange-50 text-orange-600 transition transform hover:-translate-y-0.5 hover:bg-orange-100"
                                       title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$workshop->is_featured)
                                    <form action="{{ route('admin.workshops.toggle-featured', $workshop->id) }}"
                                          method="POST" class="inline-block"
                                          onsubmit="return confirmFeaturedWorkshop('{{ $workshop->title }}')">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-3 text-sm font-semibold text-white shadow-md transition transform hover:-translate-y-0.5 hover:from-amber-600 hover:to-orange-600"
                                                title="جعلها الورشة القادمة">
                                            <i class="fas fa-crown"></i>
                                            <span>تمييز</span>
                                        </button>
                                    </form>
                                    @else
                                    <div class="flex items-center gap-2">
                                        <div class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-2 text-white shadow-md">
                                            <i class="fas fa-crown"></i>
                                            <span class="text-sm font-bold">مميزة</span>
                                        </div>
                                        <form action="{{ route('admin.workshops.toggle-featured', $workshop->id) }}"
                                              method="POST" class="inline-block"
                                              onsubmit="return confirmRemoveFeatured('{{ $workshop->title }}')">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-500 text-white shadow-md transition transform hover:-translate-y-0.5 hover:bg-red-600"
                                                    title="إلغاء التمييز">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                    <form action="{{ route('admin.workshops.toggle-status', $workshop->id) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-purple-100 bg-purple-50 text-purple-600 transition transform hover:-translate-y-0.5 hover:bg-purple-100"
                                                title="{{ $workshop->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}">
                                            <i class="fas {{ $workshop->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.workshops.destroy', $workshop->id) }}"
                                          method="POST" class="inline delete-workshop-form"
                                          data-workshop-title="{{ $workshop->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-100 bg-rose-50 text-rose-600 transition transform hover:-translate-y-0.5 hover:bg-rose-100"
                                                title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-orange-50 text-orange-400">
                                    <i class="fas fa-chalkboard-teacher text-3xl"></i>
                                </div>
                                <p class="text-lg font-semibold text-gray-700">لا توجد ورشات حالياً</p>
                                <p class="text-sm text-gray-500 mt-1">ابدأ بإضافة ورشة جديدة ليظهر محتواها هنا.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($workshops->hasPages())
        <div class="mt-8">
            {{ $workshops->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@include('admin.workshops.partials.delete-confirm-script')

@push('scripts')
    <script>
    function confirmFeaturedWorkshop(workshopTitle) {
        return Swal.fire({
            title: 'تأكيد التمييز',
            html: `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-crown text-6xl text-amber-500 mb-4"></i>
                    </div>
                    <p class="text-lg mb-2">هل تريد جعل هذه الورشة هي الورشة المميزة؟</p>
                    <p class="text-xl font-bold text-amber-600">"${workshopTitle}"</p>
                    <div class="mt-4 p-3 bg-amber-50 rounded-lg">
                        <p class="text-sm text-amber-700">
                            <i class="fas fa-info-circle ml-1"></i>
                            سيتم إلغاء تمييز الورشة المميزة الحالية (إن وجدت)
                        </p>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d8b76',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-crown ml-2"></i>نعم، اجعلها مميزة',
            cancelButtonText: '<i class="fas fa-times ml-2"></i>إلغاء',
            customClass: {
                popup: 'swal2-popup-arabic',
                title: 'swal2-title-arabic',
                content: 'swal2-content-arabic'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'تم التمييز بنجاح!',
                    text: `تم جعل "${workshopTitle}" هي الورشة المميزة`,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'ممتاز!'
                });
            }
            return result.isConfirmed;
        });
    }

    function confirmRemoveFeatured(workshopTitle) {
        return Swal.fire({
            title: 'إلغاء التمييز',
            html: `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-crown text-6xl text-red-500 mb-4"></i>
                    </div>
                    <p class="text-lg mb-2">هل تريد إلغاء تمييز هذه الورشة؟</p>
                    <p class="text-xl font-bold text-red-600">"${workshopTitle}"</p>
                    <div class="mt-4 p-3 bg-red-50 rounded-lg">
                        <p class="text-sm text-red-700">
                            <i class="fas fa-exclamation-triangle ml-1"></i>
                            لن تكون هناك ورشة مميزة بعد إلغاء التمييز
                        </p>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-times ml-2"></i>نعم، ألغِ التمييز',
            cancelButtonText: '<i class="fas fa-arrow-right ml-2"></i>إلغاء',
            customClass: {
                popup: 'swal2-popup-arabic',
                title: 'swal2-title-arabic',
                content: 'swal2-content-arabic'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'تم إلغاء التمييز!',
                    text: `تم إلغاء تمييز "${workshopTitle}"`,
                    icon: 'info',
                    confirmButtonColor: '#6b7280',
                    confirmButtonText: 'حسناً'
                });
            }
            return result.isConfirmed;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const featuredRows = document.querySelectorAll('tr.featured-row');
        featuredRows.forEach(function(row) {
            row.classList.add('border-r-4', 'border-amber-400');
        });

        const featuredBadges = document.querySelectorAll('.featured-badge');
        featuredBadges.forEach(function(badge) {
            badge.addEventListener('mouseenter', function() {
                this.classList.add('animate-bounce');
            });
            badge.addEventListener('mouseleave', function() {
                this.classList.remove('animate-bounce');
            });
        });
    });
    </script>
@endpush

@push('styles')
    @include('admin.workshops.partials.swal-styles')
@endpush




