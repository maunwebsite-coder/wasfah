@extends('layouts.app')

@section('title', 'الورشة غير متاحة - موقع وصفة')

@section('content')
<div class="bg-gray-50 min-h-screen py-12 md:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-2xl sm:px-10">
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <div class="absolute -left-10 top-10 h-32 w-32 rounded-full bg-gradient-to-r from-amber-300 to-orange-500 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-40 w-40 rounded-full bg-gradient-to-tr from-orange-200 to-pink-400 blur-3xl"></div>
            </div>

            <div class="relative z-10 text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-50 text-3xl">
                    😕
                </div>
                <p class="mb-2 text-sm font-semibold text-orange-600">الرابط الذي حاولت فتحه</p>
                <p class="mx-auto mb-6 inline-flex items-center justify-center rounded-full bg-gray-100 px-4 py-2 font-mono text-sm text-gray-700">
                    /workshops/{{ $missingSlug }}
                </p>
                <h1 class="mb-4 text-2xl font-bold text-gray-900 sm:text-3xl">لم نعثر على هذه الورشة</h1>
                <p class="mx-auto max-w-2xl text-base text-gray-600 sm:text-lg">
                    ربما تغير اسم الورشة، انتهى وقتها أو أن الرابط يحتوي على خطأ مطبعي. لا تقلق، ما زال بإمكانك العثور على ورش مشابهة بسهولة.
                </p>
            </div>

            <form action="{{ route('workshops.search') }}" method="GET" class="mt-10">
                <label for="workshop-search" class="sr-only">ابحث عن ورشة</label>
                <div class="flex flex-col gap-3 md:flex-row">
                    <input
                        id="workshop-search"
                        type="text"
                        name="q"
                        value="{{ old('q', $missingSlug) }}"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-4 text-base shadow-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                        placeholder="جرّب البحث عن اسم الورشة أو الشيف أو التصنيف"
                    >
                    <button
                        type="submit"
                        class="flex items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 text-base font-semibold text-white shadow-lg transition hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-2"
                    >
                        <i class="fas fa-search ml-2"></i>
                        ابحث الآن
                    </button>
                </div>
            </form>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('workshops') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 text-right transition hover:border-orange-200 hover:bg-orange-50">
                    <div class="mb-2 inline-flex items-center justify-center rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-600">
                        استعرض كل الورشات
                    </div>
                    <p class="text-base text-gray-600">افتح صفحة الورشات الكاملة مع إمكانيات الفلترة حسب السعر، المكان والمستوى.</p>
                    <div class="mt-3 flex items-center text-sm font-semibold text-orange-600">
                        انتقل الآن
                        <i class="fas fa-arrow-left mr-2 transition group-hover:-translate-x-1"></i>
                    </div>
                </a>
                <a href="{{ route('contact') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 text-right transition hover:border-orange-200 hover:bg-orange-50">
                    <div class="mb-2 inline-flex items-center justify-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                        نساعدك في إيجادها
                    </div>
                    <p class="text-base text-gray-600">أخبر فريق وصفة باسم الورشة أو التجربة التي تبحث عنها وسنرشدك لأقرب خيار.</p>
                    <div class="mt-3 flex items-center text-sm font-semibold text-gray-700">
                        تواصل معنا
                        <i class="fas fa-arrow-left mr-2 transition group-hover:-translate-x-1"></i>
                    </div>
                </a>
            </div>

            @if($popularCategories->isNotEmpty())
            <div class="mt-10 rounded-3xl bg-gray-50 p-6">
                <p class="mb-4 text-sm font-semibold text-gray-500">تصنيفات شائعة</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($popularCategories as $category)
                        <a
                            href="{{ route('workshops', ['category' => $category->category]) }}"
                            class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:text-orange-600"
                        >
                            <span>{{ $category->category }}</span>
                            <span class="text-xs text-gray-400">{{ $category->total }} ورش</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="mt-12">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-orange-600">ورش مقترحة</p>
                    <h2 class="text-2xl font-bold text-gray-900">جرب واحدة من هذه التجارب القادمة</h2>
                </div>
                <a href="{{ route('workshops') }}" class="hidden text-sm font-semibold text-orange-600 hover:text-orange-700 md:inline-flex md:items-center">
                    شاهد المزيد
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>

            @if($suggestedWorkshops->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($suggestedWorkshops as $suggestedWorkshop)
                    @php
                        $imageUrl = $suggestedWorkshop->image
                            ? Storage::disk('public')->url($suggestedWorkshop->image)
                            : asset('image/wterm.webp');
                        $startsAt = $suggestedWorkshop->start_date
                            ? $suggestedWorkshop->start_date->locale('ar')->translatedFormat('d F Y • h:i a')
                            : 'سيتم تحديد الموعد';
                        $isOnline = $suggestedWorkshop->is_online;
                        $priceLabel = $suggestedWorkshop->price
                            ? ($suggestedWorkshop->formatted_price
                                ?? number_format((float) $suggestedWorkshop->price, 2) . ' ' . ($suggestedWorkshop->currency ?? config('finance.default_currency', 'USD')))
                            : 'مجاناً';
                    @endphp
                    <a href="{{ route('workshop.show', $suggestedWorkshop->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative">
                            <img src="{{ $imageUrl }}" alt="{{ $suggestedWorkshop->title }}" class="h-48 w-full object-cover" loading="lazy">
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ $isOnline ? 'أونلاين' : 'حضوري' }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="mb-2 text-lg font-bold text-gray-900 group-hover:text-orange-600">{{ $suggestedWorkshop->title }}</h3>
                            <p class="mb-4 flex-1 text-sm text-gray-600">
                                {{ \Illuminate\Support\Str::limit($suggestedWorkshop->featured_description ?: $suggestedWorkshop->description, 110) }}
                            </p>
                            <div class="mt-auto space-y-2 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-orange-500"></i>
                                    <span>{{ $startsAt }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-orange-500"></i>
                                    <span>{{ $suggestedWorkshop->instructor }}</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold text-gray-900">
                                    <i class="fas fa-wallet text-orange-500"></i>
                                    <span>{{ $priceLabel }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            @else
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center">
                <p class="text-base text-gray-600">لا توجد ورش مقترحة حالياً، لكن يمكنك تصفح كل الورشات المتاحة من خلال الزر أعلاه.</p>
            </div>
            @endif

            <a href="{{ route('workshops') }}" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl border border-gray-200 px-6 py-4 text-sm font-semibold text-gray-700 transition hover:border-orange-200 hover:bg-orange-50 md:hidden">
                شاهد جميع الورشات
            </a>
        </div>
    </div>
</div>
@endsection



