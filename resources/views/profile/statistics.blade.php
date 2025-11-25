@extends('layouts.app')

@section('title', 'إحصاءات الملف الشخصي - موقع وصفة')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            @include('profile.partials.hero')

            <div class="mt-6">
                @include('profile.partials.nav', ['active' => 'statistics'])
            </div>

            <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">إجمالي الوصفات المحفوظة</p>
                    <p class="mt-3 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['saved_recipes_count']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">يشمل كل الوصفات التي أضفتها إلى مكتبتك.</p>
                </article>
                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">الوصفات التي جربتها</p>
                    <p class="mt-3 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['made_recipes_count']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">يشمل الوصفات التي أشرت إليها كمجرّبة.</p>
                </article>
                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">إجمالي الحجوزات</p>
                    <p class="mt-3 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['booked_workshops_count']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">يشمل كل الورش التي قمت بحجزها عبر المنصة.</p>
                </article>
                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">نقاط التفاعل</p>
                    <p class="mt-3 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['engagement_score']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">محسوبة بناءً على الحفظ، التجربة، وحضور الورش.</p>
                </article>
            </section>

            @php
                $savedCount = max(1, $stats['saved_recipes_count']);
                $madePercent = $stats['saved_recipes_count'] > 0
                    ? round(($stats['made_recipes_count'] / $savedCount) * 100)
                    : ($stats['made_recipes_count'] > 0 ? 100 : 0);

                $bookingsTotal = max(1, $stats['booked_workshops_count']);
                $confirmedPercent = round(($stats['confirmed_workshops_count'] / $bookingsTotal) * 100);
                $pendingPercent = round(($stats['pending_workshops_count'] / $bookingsTotal) * 100);
                $cancelledPercent = round(($stats['cancelled_workshops_count'] / $bookingsTotal) * 100);
            @endphp

            <section class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <header class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">تفاعل الوصفات</h2>
                            <p class="mt-1 text-sm text-gray-500">يعرض العلاقة بين الحفظ والتجربة.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-600">
                            <i class="fas fa-balance-scale-left"></i>
                            <span>معدل التحويل</span>
                            <span>{{ $madePercent }}%</span>
                        </span>
                    </header>

                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>الوصفات المحفوظة</span>
                                <span>{{ number_format($stats['saved_recipes_count']) }}</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-orange-200" style="width: 100%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>الوصفات المجربة</span>
                                <span>{{ number_format($stats['made_recipes_count']) }}</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ min(100, $madePercent) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-dashed border-orange-200 bg-orange-50/50 p-4 text-sm text-gray-600">
                        <p>
                            كلما اقترب معدل التحويل من 100٪ دلّ ذلك على أنك تطبق معظم الوصفات التي تحفظها. إذا كان المعدل منخفضاً، جرّب تنظيم مكتبتك أو إعداد قوائم للوصفات التي ترغب في تجربتها قريباً.
                        </p>
                    </div>
                </article>

                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <header class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">إحصاءات الورش والحجوزات</h2>
                            <p class="mt-1 text-sm text-gray-500">توزيع حالات الحجوزات التي قمت بها.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-600">
                            <i class="fas fa-business-time"></i>
                            <span>{{ number_format($stats['booked_workshops_count']) }}</span>
                            <span>حجز</span>
                        </span>
                    </header>

                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>حجوزات مؤكدة</span>
                                <span>{{ number_format($stats['confirmed_workshops_count']) }} ({{ $confirmedPercent }}%)</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-green-500" style="width: {{ min(100, $confirmedPercent) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>بانتظار التأكيد</span>
                                <span>{{ number_format($stats['pending_workshops_count']) }} ({{ $pendingPercent }}%)</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-yellow-500" style="width: {{ min(100, $pendingPercent) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>حجوزات ملغاة</span>
                                <span>{{ number_format($stats['cancelled_workshops_count']) }} ({{ $cancelledPercent }}%)</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-red-500" style="width: {{ min(100, $cancelledPercent) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-dashed border-orange-200 bg-orange-50/50 p-4 text-sm text-gray-600">
                        <p>
                            راقب الحجوزات المعلقة لتأكيدها في الوقت المناسب. إذا زادت الحجوزات الملغاة، تحقق من طرق الدفع أو التواصل مع فريق الدعم لمساعدتك.
                        </p>
                    </div>
                </article>
            </section>

            <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">نقاط التفاعل وتاريخ النشاط</h2>
                        <p class="text-sm text-gray-500">
                            حساب النقاط يعتمد على نشاطك أثناء آخر 90 يوماً.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-600">
                        <i class="fas fa-history"></i>
                        <span>آخر نشاط</span>
                        <span>{{ $stats['last_activity_at'] ? Carbon::parse($stats['last_activity_at'])->diffForHumans() : 'لم يتم تسجيل نشاط بعد' }}</span>
                    </span>
                </header>

        <div class="mt-6 grid gap-6 md:grid-cols-3">
                    <article class="rounded-2xl border border-orange-100 bg-orange-50/50 p-5">
                        <h3 class="text-sm font-semibold text-gray-700">بناء النقاط</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li><i class="fas fa-check text-xs text-orange-500 ml-2"></i> +2 نقطة لكل وصفة محفوظة</li>
                            <li><i class="fas fa-check text-xs text-orange-500 ml-2"></i> +3 نقاط لكل وصفة مجربة</li>
                            <li><i class="fas fa-check text-xs text-orange-500 ml-2"></i> +4 نقاط لكل ورشة محجوزة</li>
                        </ul>
                        <p class="mt-4 text-sm font-medium text-gray-700 flex flex-wrap items-center gap-1">
                            <span>نقاطك الحالية:</span>
                            <span>{{ number_format($stats['engagement_score']) }}</span>
                        </p>
                    </article>
                    <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-700">أقرب ورشة</h3>
                        @if ($nextWorkshop)
                            @php
                                $workshopModel = optional($nextWorkshop)->workshop;
                                $start = optional($workshopModel?->start_date);
                            @endphp
                            <div class="mt-3 space-y-2 text-sm text-gray-600">
                                <p class="font-semibold text-gray-800">
                                    {{ $workshopModel?->title ?? 'ورشة بدون عنوان' }}
                                </p>
                                @if ($start)
                                    <p><i class="fas fa-clock ml-2 text-orange-500"></i>{{ $start->locale('ar')->translatedFormat('d F Y • h:i a') }}</p>
                                @endif
                                <p><i class="fas fa-map-marker-alt ml-2 text-orange-500"></i>{{ $workshopModel?->is_online ? 'أونلاين عبر Google Meet' : ($workshopModel?->location ?? 'سيتم تحديد الموقع لاحقاً') }}</p>
                            </div>
                        @else
                            <p class="mt-3 text-sm text-gray-500">لا توجد ورش قادمة حالياً.</p>
                        @endif
                    </article>
                    <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-700">المراجعات والتقييمات</h3>
                        <p class="mt-3 text-3xl font-extrabold text-gray-800">
                            {{ number_format($stats['reviews_count']) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">عدد المراجعات التي أرسلتها بعد الورش.</p>
                        @if ($workshopReviews->isNotEmpty())
                            <ul class="mt-3 space-y-2 text-sm text-gray-600">
                                @foreach ($workshopReviews->take(3) as $review)
                                    <li class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                                        <span class="font-medium text-gray-700">{{ $review->workshop->title ?? 'ورشة بدون عنوان' }}</span>
                                        @if (!is_null($review->rating))
                                            <span class="ml-2 text-xs text-orange-500">
                                                <i class="fas fa-star text-xs"></i>
                                                {{ number_format($review->rating, 1) }}/5
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                </div>
            </section>

            @if ($upcomingWorkshops->isNotEmpty())
                <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">جدول الورش القادمة</h2>
                        <a href="{{ route('bookings.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            إدارة الحجوزات
                        </a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-right text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="px-4 py-2 font-semibold">الورشة</th>
                                    <th class="px-4 py-2 font-semibold">التاريخ</th>
                                    <th class="px-4 py-2 font-semibold">الحالة</th>
                                    <th class="px-4 py-2 font-semibold">الوضع</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($upcomingWorkshops as $booking)
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
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700">
                                        {{ optional($workshop)->title ?? 'ورشة بدون عنوان' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $start ? $start->locale('ar')->translatedFormat('d F Y • h:i a') : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ $status === 'confirmed' ? 'مؤكد' : ($status === 'pending' ? 'بانتظار' : ($status === 'cancelled' ? 'ملغي' : $status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ optional($workshop)->is_online ? 'أونلاين' : 'حضوري' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if ($user->isChef() && $chefOverview)
                <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">لوحة إحصاءات الشيف</h2>
                            <p class="text-sm text-gray-500">
                                ملخص أداء وصفاتك وحضورك كمنشئ محتوى على المنصة.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $chefOverview['dashboard_url'] }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-medium text-orange-600 hover:border-orange-300 hover:bg-orange-50">
                                <i class="fas fa-chart-line text-xs"></i>
                                لوحة التحكم
                            </a>
                            @if ($chefOverview['link_page']['public_url'] ?? false)
                                <a href="{{ $chefOverview['link_page']['public_url'] }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50">
                                    <i class="fas fa-link text-xs"></i>
                                    صفحة الروابط
                                </a>
                            @endif
                        </div>
                    </header>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">إجمالي الوصفات</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['recipes_total']) }}</p>
                        </article>
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">الوصفات العامة</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['public_recipes']) }}</p>
                        </article>
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">إجمالي مرات الحفظ</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['total_saves']) }}</p>
                        </article>
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">متوسط التقييم</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">
                                {{ $chefOverview['average_rating'] ? number_format($chefOverview['average_rating'], 1) : '—' }}
                            </p>
                        </article>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-right text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="px-4 py-2 font-semibold">الحالة</th>
                                    <th class="px-4 py-2 font-semibold">عدد الوصفات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-4 py-3 text-gray-600">معتمد</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($chefOverview['status_counts']['approved'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-gray-600">بانتظار المراجعة</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($chefOverview['status_counts']['pending'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-gray-600">مسودة</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($chefOverview['status_counts']['draft'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-gray-600">مرفوض</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ number_format($chefOverview['status_counts']['rejected'] ?? 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if (!empty($chefOverview['popular_recipes']))
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800">أعلى ثلاث وصفات أداءً</h3>
                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                @foreach ($chefOverview['popular_recipes'] as $recipe)
                                    <article class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                                        <h4 class="font-semibold text-gray-800">{{ $recipe->title }}</h4>
                                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-600">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-bookmark ml-1 text-orange-500"></i>
                                                <span>{{ number_format($recipe->saved_count) }}</span>
                                                <span>حفظ</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-utensils ml-1 text-orange-500"></i>
                                                <span>{{ number_format($recipe->made_count) }}</span>
                                                <span>تجربة</span>
                                            </span>
                                            <span><i class="fas fa-star ml-1 text-orange-500"></i>{{ number_format($recipe->interactions_avg_rating ?? 0, 1) }} ({{ number_format($recipe->rating_count ?? 0) }})</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>
@endsection

