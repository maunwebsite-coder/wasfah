@extends('layouts.app')

@section('title', 'نشاطات الملف الشخصي - موقع وصفة')

@php
    use Carbon\Carbon;

@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            @include('profile.partials.hero')

            <div class="mt-6">
                @include('profile.partials.nav', ['active' => 'activity'])
            </div>

            <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">موجز النشاطات الأخيرة</h2>
                        <p class="text-sm text-gray-500">ترتيب زمني يعرض آخر ما قمت به على المنصة.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-600">
                        <i class="fas fa-clock"></i>
                        {{ $stats['last_activity_at'] ? Carbon::parse($stats['last_activity_at'])->diffForHumans() : 'لم يتم تسجيل نشاط بعد' }}
                    </span>
                </header>

                <div class="mt-6 space-y-4">
                    @forelse ($activityFeed as $activity)
                        @php
                            $timestamp = $activity['timestamp'] instanceof Carbon
                                ? $activity['timestamp']
                                : Carbon::parse($activity['timestamp']);
                        @endphp
                        <article class="flex flex-col gap-2 rounded-2xl border border-gray-100 bg-gray-50 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700">
                                    @switch($activity['type'])
                                        @case('saved_recipe')
                                            <i class="fas fa-bookmark text-orange-500"></i>
                                            حفظ وصفة
                                            @break
                                        @case('made_recipe')
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            تجربة وصفة
                                            @break
                                        @case('workshop_booking')
                                            <i class="fas fa-calendar-check text-indigo-500"></i>
                                            حجز ورشة
                                            @break
                                        @default
                                            <i class="fas fa-bolt text-orange-500"></i>
                                            نشاط جديد
                                    @endswitch
                                </span>
                                <span class="text-xs text-gray-500">{{ $timestamp->locale('ar')->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">{{ $activity['title'] }}</div>
                            @if (!empty($activity['meta']['category']))
                                <div class="text-xs text-gray-500 flex flex-wrap items-center gap-1">
                                    <span>التصنيف:</span>
                                    <span>{{ $activity['meta']['category'] }}</span>
                                </div>
                            @endif
                            @if ($activity['type'] === 'workshop_booking')
                                @php
                                    $status = $activity['meta']['status'] ?? '';
                                    $statusClasses = [
                                        'confirmed' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <div class="text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 font-medium {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $status === 'confirmed' ? 'مؤكد' : ($status === 'pending' ? 'بانتظار' : ($status === 'cancelled' ? 'ملغي' : $status)) }}
                                    </span>
                                    @if (!empty($activity['meta']['start_date']))
                                        <span class="ml-2 flex flex-wrap items-center gap-1">
                                            <span>يبدأ بتاريخ</span>
                                            <span>{{ Carbon::parse($activity['meta']['start_date'])->locale('ar')->translatedFormat('d F Y • h:i a') }}</span>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                            لم يتم تسجيل أي نشاط حتى الآن. بمجرد حفظ وصفة أو حجز ورشة ستظهر هنا.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">الوصفات المحفوظة</h2>
                    @if(auth()->user()?->isAdmin())
                        <a href="{{ route('saved.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            إدارة المكتبة
                        </a>
                    @endif
                </div>
                    <div class="mt-4 space-y-4">
                        @forelse ($savedRecipes->take(5) as $recipe)
                            <div class="flex gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-3 shadow-sm">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-2xl bg-gray-100">
                                    <img
                                        src="{{ $recipe->image_url ?? $recipe->image ?? asset('image/brownies.webp') }}"
                                        alt="{{ $recipe->title }}"
                                        class="h-full w-full object-cover" loading="lazy"
                                    >
                                </div>
                                <div class="flex-1">
                                    <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="text-sm font-semibold text-gray-800 hover:text-orange-600">
                                        {{ $recipe->title }}
                                    </a>
                                    @if ($recipe->category?->name)
                                        <div class="text-xs text-gray-500 flex flex-wrap items-center gap-1">
                                            <span>التصنيف:</span>
                                            <span>{{ $recipe->category->name }}</span>
                                        </div>
                                    @endif
                                    <div class="mt-1 text-xs text-gray-400 flex flex-wrap items-center gap-1">
                                        <span>تمت الإضافة</span>
                                        <span>{{ optional($recipe->userInteraction->updated_at ?? $recipe->userInteraction->created_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                                لم تحفظ أي وصفة حتى الآن.
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">الوصفات التي جربتها</h2>
                        <a href="{{ route('profile.statistics') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            عرض الإحصاءات
                        </a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @forelse ($madeRecipes->take(5) as $recipe)
                            <div class="flex gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-3 shadow-sm">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-2xl bg-gray-100">
                                    <img
                                        src="{{ $recipe->image_url ?? $recipe->image ?? asset('image/brownies.webp') }}"
                                        alt="{{ $recipe->title }}"
                                        class="h-full w-full object-cover" loading="lazy"
                                    >
                                </div>
                                <div class="flex-1">
                                    <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="text-sm font-semibold text-gray-800 hover:text-orange-600">
                                        {{ $recipe->title }}
                                    </a>
                                    @if ($recipe->category?->name)
                                        <div class="text-xs text-gray-500 flex flex-wrap items-center gap-1">
                                            <span>التصنيف:</span>
                                            <span>{{ $recipe->category->name }}</span>
                                        </div>
                                    @endif
                                    <div class="mt-1 text-xs text-gray-400 flex flex-wrap items-center gap-1">
                                        <span>تمت التجربة</span>
                                        <span>{{ optional($recipe->userInteraction->updated_at ?? $recipe->userInteraction->created_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                                لم تجرّب أي وصفة بعد. اختر وصفة من قائمتك المفضلة وشاركنا تجربتك.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            @if (auth()->check())
                <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">الحجوزات</h2>
                        <a href="{{ route('bookings.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            إدارة الحجوزات
                        </a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @forelse ($bookedWorkshops->take(6) as $booking)
                            @php
                                $workshop = $booking->workshop;
                                $start = optional($workshop?->start_date);
                                $status = $booking->status;
                                $statusClasses = [
                                    'confirmed' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <article class="rounded-2xl border border-gray-100 bg-gray-50 p-4 shadow-sm">
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h3 class="text-md font-semibold text-gray-800">
                                            {{ $workshop->title ?? 'ورشة بدون عنوان' }}
                                        </h3>
                                        <div class="mt-1 text-sm text-gray-500">
                                            @if ($start)
                                                <span class="ml-3">
                                                    <i class="fas fa-clock text-orange-500"></i>
                                                    {{ $start->locale('ar')->translatedFormat('d F Y • h:i a') }}
                                                </span>
                                            @endif
                                            <span>
                                                <i class="fas fa-map-marker-alt ml-1 text-orange-500"></i>
                                                {{ $workshop->is_online ? 'أونلاين' : ($workshop->location ?? 'سيتم تحديده') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $status === 'confirmed' ? 'مؤكد' : ($status === 'pending' ? 'بانتظار' : ($status === 'cancelled' ? 'ملغي' : $status)) }}
                                        </span>
                                        <a href="{{ route('bookings.show', ['booking' => $booking->id]) }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                                            التفاصيل
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                                لا توجد حجوزات مسجلة حتى الآن.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection



