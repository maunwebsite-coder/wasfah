@extends('layouts.app')

@section('title', 'ملف الشيف ' . ($chef->name ?? ''))

@section('content')
    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 space-y-8">
            <div class="bg-white rounded-3xl shadow-lg border border-orange-100 p-8 flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="w-32 h-32 rounded-full border-4 border-orange-100 overflow-hidden shadow-md flex items-center justify-center bg-orange-50 text-orange-600 text-3xl font-bold">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="صورة الشيف {{ $chef->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async" width="128" height="128">
                    @else
                        {{ mb_substr($chef->name ?? 'شيف', 0, 1) }}
                    @endif
                </div>
                <div class="flex-1 space-y-4 text-center md:text-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $chef->name }}</h1>
                        <p class="text-gray-600 mt-1">
                            {{ $chef->chef_specialty_description ?: 'شيف مبدع يشارك وصفاته مع المجتمع.' }}
                        </p>
                    </div>

                    @if ($socialLinks->isNotEmpty())
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            @foreach ($socialLinks as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-orange-200 px-4 py-2 text-sm text-orange-600 transition hover:bg-orange-50 hover:text-orange-700">
                                    <i class="{{ $link['icon'] }}"></i>
                                    <span>{{ $link['label'] }}</span>
                                    @if (!empty($link['followers']))
                                        <span class="text-orange-500">({{ number_format($link['followers']) }})</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="rounded-2xl bg-orange-50 px-4 py-3 text-center">
                            <p class="text-xs text-orange-500">عدد الوصفات</p>
                            <p class="text-2xl font-semibold text-orange-600">{{ number_format($stats['recipes_count']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-500">تم الحفظ</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ number_format($stats['total_saves']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-500">تم التجربة</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ number_format($stats['total_made']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-500">متوسط التقييم</p>
                            <p class="text-2xl font-semibold text-gray-800">
                                {{ $stats['average_rating'] ? number_format($stats['average_rating'], 1) : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($upcomingWorkshops->isNotEmpty() || $pastWorkshops->isNotEmpty())
                <div class="bg-white rounded-3xl shadow-lg border border-orange-100 p-8 space-y-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">ورشات الشيف</h2>
                            <p class="text-sm text-gray-600 mt-2">
                                برامج تدريبية يقدمها {{ $chef->name }} يمكنك حجزها أو استكشاف تفاصيل الورشات السابقة.
                            </p>
                        </div>
                        <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 text-orange-600 font-semibold hover:text-orange-700 transition">
                            استعراض كل الورشات
                            <i class="fas fa-arrow-left text-sm"></i>
                        </a>
                    </div>

                    <div class="space-y-6">
                        @if ($upcomingWorkshops->isNotEmpty())
                            <div class="space-y-4">
                                <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-calendar-check text-emerald-500"></i>
                                    ورشات قادمة
                                </h3>
                                <div class="grid gap-5 md:grid-cols-2">
                                    @foreach ($upcomingWorkshops as $workshop)
                                        @php
                                            $coverImage = $workshop->image
                                                ? asset('storage/' . ltrim($workshop->image, '/'))
                                                : 'https://placehold.co/600x400/0f4c73/FFFFFF?text=ورشة';
                                            $startDateLabel = $workshop->start_date
                                                ? $workshop->start_date->copy()->locale('ar')->translatedFormat('j F Y • h:i a')
                                                : 'سيتم التحديد لاحقاً';
                                            $locationLabel = $workshop->is_online ? 'أونلاين مباشر' : ($workshop->location ?: 'سيتم التحديد لاحقاً');
                                            $priceLabel = $workshop->formatted_price
                                                ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
                                            $deadlineLabel = $workshop->registration_deadline
                                                ? $workshop->registration_deadline->copy()->locale('ar')->translatedFormat('j F Y')
                                                : null;
                                            $isRegistrationOpen = (bool) $workshop->is_registration_open;
                                        @endphp
                                        <div class="rounded-2xl border border-orange-100 bg-white shadow-sm overflow-hidden flex flex-col">
                                            <div class="h-44 bg-orange-100">
                                                <img src="{{ $coverImage }}" alt="ورشة {{ $workshop->title }}" class="w-full h-full object-cover" loading="lazy" decoding="async" width="640" height="352">
                                            </div>
                                            <div class="p-5 space-y-3 flex-1 flex flex-col">
                                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-orange-600">
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-50 rounded-full">
                                                        <i class="fas {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                        {{ $workshop->is_online ? 'أونلاين' : 'حضوري' }}
                                                    </span>
                                                    @if ($deadlineLabel)
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 text-amber-600 rounded-full">
                                                            <i class="fas fa-hourglass-half"></i>
                                                            حتى {{ $deadlineLabel }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $workshop->title }}</h4>
                                                @if (! empty($workshop->instructor))
                                                    <p class="text-sm text-gray-500">مع {{ $workshop->instructor }}</p>
                                                @endif
                                                <ul class="space-y-2 text-sm text-gray-600">
                                                    <li>
                                                        <i class="fas fa-calendar text-orange-500 ms-2"></i>
                                                        {{ $startDateLabel }}
                                                    </li>
                                                    <li>
                                                        <i class="fas fa-location-dot text-emerald-500 ms-2"></i>
                                                        {{ $locationLabel }}
                                                    </li>
                                                    <li>
                                                        <i class="fas fa-money-bill-wave text-orange-500 ms-2"></i>
                                                        {{ $priceLabel }}
                                                    </li>
                                                </ul>
                                                <div class="mt-auto pt-3">
                                                    @if ($isRegistrationOpen)
                                                        <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-full bg-orange-500 text-white font-semibold hover:bg-orange-600 transition">
                                                            احجز مقعدك الآن
                                                            <i class="fas fa-arrow-left text-sm"></i>
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-full bg-gray-100 text-gray-500 font-semibold">
                                                            انتهى التسجيل
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($pastWorkshops->isNotEmpty())
                            <div class="space-y-4">
                                <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-clock-rotate-left text-gray-500"></i>
                                    ورشات سابقة
                                </h3>
                                <div class="grid gap-5 md:grid-cols-2">
                                    @foreach ($pastWorkshops as $workshop)
                                        @php
                                            $coverImage = $workshop->image
                                                ? asset('storage/' . ltrim($workshop->image, '/'))
                                                : 'https://placehold.co/600x400/0f4c73/FFFFFF?text=ورشة';
                                            $startDateLabel = $workshop->start_date
                                                ? $workshop->start_date->copy()->locale('ar')->translatedFormat('j F Y • h:i a')
                                                : 'موعد غير محدد';
                                            $locationLabel = $workshop->is_online ? 'أونلاين مباشر' : ($workshop->location ?: 'سيتم التحديد لاحقاً');
                                            $priceLabel = $workshop->formatted_price
                                                ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
                                        @endphp
                                        <div class="rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden flex flex-col">
                                            <div class="h-44 bg-gray-200">
                                                <img src="{{ $coverImage }}" alt="ورشة {{ $workshop->title }}" class="w-full h-full object-cover opacity-90" loading="lazy" decoding="async" width="640" height="352">
                                            </div>
                                            <div class="p-5 space-y-3 flex-1 flex flex-col">
                                                <div class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white px-3 py-1 rounded-full w-max">
                                                    <i class="fas {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                    {{ $workshop->is_online ? 'أونلاين' : 'حضوري' }}
                                                </div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $workshop->title }}</h4>
                                                @if (! empty($workshop->instructor))
                                                    <p class="text-sm text-gray-500">قدّمها {{ $workshop->instructor }}</p>
                                                @endif
                                                <ul class="space-y-2 text-sm text-gray-600">
                                                    <li>
                                                        <i class="fas fa-calendar text-gray-500 ms-2"></i>
                                                        {{ $startDateLabel }}
                                                    </li>
                                                    <li>
                                                        <i class="fas fa-location-dot text-gray-500 ms-2"></i>
                                                        {{ $locationLabel }}
                                                    </li>
                                                    <li>
                                                        <i class="fas fa-money-bill-wave text-gray-500 ms-2"></i>
                                                        {{ $priceLabel }}
                                                    </li>
                                                </ul>
                                                <div class="mt-auto pt-3">
                                                    <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-full border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition">
                                                        عرض التفاصيل
                                                        <i class="fas fa-arrow-left text-sm"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">وصفات عامة</h2>
                    <span class="text-sm text-gray-500">{{ $publicRecipes->count() }} وصفة</span>
                </div>

                @if ($publicRecipes->isEmpty())
                    <div class="rounded-2xl bg-white border border-dashed border-orange-200 p-8 text-center text-gray-500">
                        لا توجد وصفات عامة متاحة حاليًا.
                    </div>
                @else
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($publicRecipes as $recipe)
                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug ?? $recipe->recipe_id]) }}" class="group rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
                                @if (!empty($recipe->image_url))
                                    <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" class="w-full h-48 object-cover" loading="lazy" decoding="async" width="640" height="384">
                                @endif
                                <div class="p-5 space-y-2">
                                    <h3 class="font-semibold text-lg text-gray-900 group-hover:text-orange-600 transition">{{ $recipe->title }}</h3>
                                    @if (!empty($recipe->category?->name))
                                        <p class="text-sm text-gray-500">{{ $recipe->category->name }}</p>
                                    @endif
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span><i class="fas fa-bookmark text-orange-500 ms-1"></i>{{ number_format($recipe->saved_count ?? 0) }}</span>
                                        <span><i class="fas fa-utensils text-orange-500 ms-1"></i>{{ number_format($recipe->made_count ?? 0) }}</span>
                                        <span><i class="fas fa-star text-orange-500 ms-1"></i>{{ number_format((float) ($recipe->interactions_avg_rating ?? 0), 1) }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($canViewExclusive && $exclusiveRecipes->isNotEmpty())
                <div class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900">وصفات خاصة</h2>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($exclusiveRecipes as $recipe)
                            <div class="rounded-2xl bg-orange-900 text-white shadow-lg overflow-hidden">
                                @if (!empty($recipe->image_url))
                                    <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" class="w-full h-44 object-cover opacity-80" loading="lazy" decoding="async" width="640" height="352">
                                @endif
                                <div class="p-5 space-y-2">
                                    <h3 class="font-semibold text-lg">{{ $recipe->title }}</h3>
                                    <p class="text-sm text-orange-100 line-clamp-2">
                                        {{ $recipe->excerpt ?? 'وصفة حصرية لأعضاء مجتمع الشيف.' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($popularRecipes->isNotEmpty())
                <div class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900">أبرز الوصفات</h2>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($popularRecipes as $recipe)
                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug ?? $recipe->recipe_id]) }}" class="rounded-2xl bg-white border border-orange-100 shadow-sm p-5 flex flex-col gap-3 transition hover:border-orange-300 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-lg text-gray-900">{{ $recipe->title }}</h3>
                                    <span class="inline-flex items-center gap-1 text-sm text-orange-600 font-medium">
                                        <i class="fas fa-fire"></i>
                                        شائع
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span><i class="fas fa-bookmark text-orange-500 ms-1"></i>{{ number_format($recipe->saved_count ?? 0) }}</span>
                                    <span><i class="fas fa-utensils text-orange-500 ms-1"></i>{{ number_format($recipe->made_count ?? 0) }}</span>
                                    <span><i class="fas fa-star text-orange-500 ms-1"></i>{{ number_format((float) ($recipe->interactions_avg_rating ?? 0), 1) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection



