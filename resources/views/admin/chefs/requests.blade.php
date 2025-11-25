@extends('layouts.app')

@section('title', 'طلبات انضمام الشيفات')

@php
    use App\Models\User;

    $tabs = [
        'all' => [
            'label' => 'جميع الطلبات',
            'value' => 'all',
            'description' => 'عرض شامل لكل الطلبات المرسلة',
            'badge_class' => 'bg-gray-200 text-gray-700',
        ],
        'pending' => [
            'label' => 'طلبات جديدة',
            'value' => User::CHEF_STATUS_PENDING,
            'description' => 'طلبات تنتظر اعتماد الإدارة',
            'badge_class' => 'bg-orange-100 text-orange-700',
        ],
        'needs_profile' => [
            'label' => 'تحتاج استكمال البيانات',
            'value' => User::CHEF_STATUS_NEEDS_PROFILE,
            'description' => 'شيفات لم يكملوا نموذج الانضمام',
            'badge_class' => 'bg-yellow-100 text-yellow-700',
        ],
        'approved' => [
            'label' => 'شيفات معتمدون',
            'value' => User::CHEF_STATUS_APPROVED,
            'description' => 'قائمة الشيفات المعتمدين في المنصة',
            'badge_class' => 'bg-emerald-100 text-emerald-700',
        ],
        'rejected' => [
            'label' => 'طلبات مرفوضة',
            'value' => User::CHEF_STATUS_REJECTED,
            'description' => 'طلبات تم رفضها من الإدارة',
            'badge_class' => 'bg-red-100 text-red-700',
        ],
    ];

    $statusMeta = [
        User::CHEF_STATUS_PENDING => ['label' => 'قيد المراجعة', 'classes' => 'bg-orange-100 text-orange-700'],
        User::CHEF_STATUS_NEEDS_PROFILE => ['label' => 'بانتظار استكمال البيانات', 'classes' => 'bg-yellow-100 text-yellow-700'],
        User::CHEF_STATUS_APPROVED => ['label' => 'معتمد', 'classes' => 'bg-emerald-100 text-emerald-700'],
        User::CHEF_STATUS_REJECTED => ['label' => 'مرفوض', 'classes' => 'bg-red-100 text-red-700'],
    ];
@endphp

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
            <div class="px-8 pt-8 pb-6 border-b border-gray-100 bg-gradient-to-l from-orange-50 to-white rounded-t-2xl">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-800 mb-2">
                            <i class="fas fa-user-tie text-orange-500 ml-2"></i>
                            طلبات انضمام الشيفات
                        </h1>
                        <p class="text-gray-600">
                            راجع بيانات الشيفات الجدد واعتمدهم لبدء إضافة وصفاتهم على المنصة
                        </p>
                    </div>
                    <div class="bg-white border border-orange-100 rounded-xl px-4 py-3 text-center shadow-sm">
                        <div class="text-xs text-gray-500">طلبات تحتاج متابعة</div>
                        <div class="text-2xl font-bold text-orange-600">
                            {{ number_format(($statusCounts['pending'] ?? 0) + ($statusCounts['needs_profile'] ?? 0)) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 pt-6 pb-4">
                <div class="flex flex-wrap gap-3">
                    @foreach($tabs as $key => $tab)
                        @php
                            $isActive = ($status === $tab['value']) || ($status === 'all' && $tab['value'] === 'all');
                            $count = $statusCounts[$key] ?? 0;
                            $routeParams = $tab['value'] === 'all' ? [] : ['status' => $tab['value']];
                            $url = empty($routeParams)
                                ? route('admin.chefs.requests')
                                : route('admin.chefs.requests', $routeParams);
                        @endphp
                        <a href="{{ $url }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl border transition-all
                           {{ $isActive ? 'border-orange-300 bg-orange-50 shadow-sm' : 'border-gray-200 hover:border-orange-200 hover:bg-orange-50/50' }}">
                            <div class="flex flex-col">
                                <span class="font-semibold text-sm {{ $isActive ? 'text-orange-700' : 'text-gray-700' }}">
                                    {{ $tab['label'] }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $tab['description'] }}
                                </span>
                            </div>
                            <span class="ml-2 inline-flex items-center justify-center min-w-[2.5rem] px-2 py-1 text-xs font-semibold rounded-full {{ $tab['badge_class'] }}">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="px-6 pb-8">
                @forelse($requests as $chef)
                    @php
                        $meta = $statusMeta[$chef->chef_status] ?? ['label' => 'غير محدد', 'classes' => 'bg-gray-100 text-gray-700'];
                        $canApprove = in_array($chef->chef_status, [
                            User::CHEF_STATUS_PENDING,
                            User::CHEF_STATUS_NEEDS_PROFILE,
                            User::CHEF_STATUS_REJECTED,
                        ], true);
                        $canReject = in_array($chef->chef_status, [
                            User::CHEF_STATUS_PENDING,
                            User::CHEF_STATUS_NEEDS_PROFILE,
                        ], true);
                    @endphp
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 mb-5">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                            <div class="flex-1 space-y-4">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800">{{ $chef->name }}</h2>
                                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                                            <span>
                                                <i class="fas fa-envelope ml-1 text-orange-400"></i>
                                                {{ $chef->email }}
                                            </span>
                                            @if($chef->phone)
                                                <span>
                                                    <i class="fas fa-phone ml-1 text-orange-400"></i>
                                                    {{ $chef->phone }}
                                                </span>
                                            @endif
                                            <span>
                                                <i class="fas fa-calendar-alt ml-1 text-orange-400"></i>
                                                {{ $chef->created_at?->locale('ar')->translatedFormat('d F Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $meta['classes'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                    @if($chef->instagram_url)
                                        <div class="flex items-center gap-2">
                                            <i class="fab fa-instagram text-pink-500 text-lg"></i>
                                            <div>
                                                <a href="{{ $chef->instagram_url }}" target="_blank" rel="noopener" class="font-semibold text-orange-600 hover:underline">
                                                    حساب إنستغرام
                                                </a>
                                                @if($chef->instagram_followers)
                                                    <div class="text-xs text-gray-500">
                                                        {{ number_format($chef->instagram_followers) }} متابع
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($chef->youtube_url)
                                        <div class="flex items-center gap-2">
                                            <i class="fab fa-youtube text-red-500 text-lg"></i>
                                            <div>
                                                <a href="{{ $chef->youtube_url }}" target="_blank" rel="noopener" class="font-semibold text-orange-600 hover:underline">
                                                    قناة يوتيوب
                                                </a>
                                                @if($chef->youtube_followers)
                                                    <div class="text-xs text-gray-500">
                                                        {{ number_format($chef->youtube_followers) }} مشترك
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($chef->chef_specialty_area)
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-utensils text-orange-500"></i>
                                            <span class="font-semibold text-gray-700">
                                                مجال التخصص: {{ $chef->chef_specialty_area === 'food' ? 'الطعام والطبخ' : $chef->chef_specialty_area }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($chef->chef_approved_at)
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                            <span class="text-xs text-emerald-600">
                                                معتمد منذ {{ $chef->chef_approved_at->locale('ar')->diffForHumans() }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if($chef->chef_specialty_description)
                                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-sm text-gray-700 leading-6">
                                        <div class="flex items-center gap-2 mb-2 text-orange-600 font-semibold">
                                            <i class="fas fa-info-circle"></i>
                                            نبذة عن الخبرة
                                        </div>
                                        {!! nl2br(e($chef->chef_specialty_description)) !!}
                                    </div>
                                @endif
                            </div>

                            <div class="md:w-52 flex-shrink-0 space-y-3">
                                @if($canApprove)
                                    <form method="POST"
                                          action="{{ route('admin.chefs.approve', $chef) }}"
                                          onsubmit="return confirm('هل تريد اعتماد هذا الشيف؟ سيتمكن من إضافة وصفاته بعد الاعتماد.');">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-semibold transition">
                                            <i class="fas fa-check"></i>
                                            اعتماد الشيف
                                        </button>
                                    </form>
                                @endif

                                @if($canReject)
                                    <form method="POST"
                                          action="{{ route('admin.chefs.reject', $chef) }}"
                                          onsubmit="return confirm('هل تريد رفض هذا الطلب؟ يمكنك التواصل مع الشيف لإبلاغه بالملاحظات.');">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold transition">
                                            <i class="fas fa-times"></i>
                                            رفض الطلب
                                        </button>
                                    </form>
                                @endif

                                @if(!$canApprove && !$canReject)
                                    <div class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-3 text-center">
                                        تمت معالجة هذا الطلب بالفعل.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-dashed border-orange-200 rounded-2xl p-10 text-center">
                        <i class="fas fa-smile-wink text-4xl text-orange-400 mb-3"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">لا توجد طلبات حالياً</h3>
                        <p class="text-gray-500">
                            سيتم عرض أي طلبات انضمام جديدة هنا فور وصولها.
                        </p>
                    </div>
                @endforelse
            </div>

            @if($requests->hasPages())
                <div class="px-6 pb-6 border-t border-gray-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

