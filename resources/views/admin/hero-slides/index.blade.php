@extends('layouts.app')

@section('title', 'إدارة سلايدر الهيرو')

@push('styles')
<style>
    .hero-admin-card {
        background: linear-gradient(135deg, #0f4c73, #0fb39a);
    }
    .hero-slide-row:hover {
        background: rgba(15, 76, 115, 0.05);
    }
    .status-pill {
        padding: 0.25rem 0.85rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-pill.active {
        background: rgba(16, 185, 129, 0.15);
        color: #047857;
    }
    .status-pill.inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #b91c1c;
    }
</style>
@endpush

@section('content')
@php
    $schemaStatus = $schemaStatus ?? ['ready' => true, 'missing_columns' => [], 'media_mode' => 'dual'];
    $schemaReady = $schemaStatus['ready'] ?? true;
    $missingColumns = $schemaStatus['missing_columns'] ?? [];
    $mediaMode = $schemaStatus['media_mode'] ?? 'dual';
@endphp
<div class="min-h-screen bg-gray-50 pb-12">
    <div class="hero-admin-card text-white py-8">
        <div class="container mx-auto px-4 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">التحكم بسلايدر الهيرو</h1>
                <p class="text-white/90">أنشئ الشرائح، عدل محتواها، واضبط ترتيب ظهورها في الصفحة الرئيسية.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($schemaReady)
                    @if($slides->isEmpty())
                        <form method="POST" action="{{ route('admin.hero-slides.initialize-defaults') }}" onsubmit="return confirm('سيتم إنشاء الشرائح الافتراضية الحالية لتعديلها. هل أنت متأكد؟');">
                            @csrf
                            <button type="submit" class="bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-3 rounded-xl flex items-center gap-2 transition">
                                <i class="fas fa-download"></i>
                                استيراد الشرائح الحالية
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.hero-slides.create') }}" class="bg-white text-orange-600 hover:bg-gray-100 font-semibold px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fas fa-plus"></i>
                        شريحة جديدة
                    </a>
                @else
                    <div class="bg-white/20 text-white font-semibold px-5 py-3 rounded-xl flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        الترقية مطلوبة قبل إدارة الشرائح
                    </div>
                @endif
                <a href="{{ route('home') }}" target="_blank" class="bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-3 rounded-xl flex items-center gap-2 transition">
                    <i class="fas fa-eye"></i>
                    عرض الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-10">
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-xl">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-xl">
                <p class="font-semibold mb-2">حدثت بعض الأخطاء:</p>
                <ul class="space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($schemaReady && $mediaMode === 'legacy')
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-sm leading-6">
                <p class="font-semibold text-amber-900">وضع التوافق مفعل.</p>
                <p>تُخزن صور سطح المكتب والجوال في نفس الحقل لذلك ستظهر نفس الصورة على كل الأجهزة. لتفعيل الرفع المزدوج شغّل أوامر التهجير بعد آخر تحديث (<code class="bg-white/80 px-2 py-0.5 rounded text-xs text-amber-900">php artisan migrate --force</code>).</p>
            </div>
        @endif

        @unless($schemaReady)
            <div class="mb-6 p-5 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl leading-7">
                <p class="font-semibold text-amber-900 mb-2">تم تعطيل إدارة سلايدر الهيرو مؤقتاً.</p>
                <p class="text-sm mb-2">
                    لتفعيل هذه الصفحة يرجى تشغيل أوامر التهجير على الخادم (مثال:
                    <code class="bg-white/90 px-2 py-0.5 rounded text-xs text-amber-900">php artisan migrate --force</code>)
                    ثم إعادة تحميل الصفحة.
                </p>
                @if(!empty($missingColumns))
                    <p class="text-sm">
                        <span class="font-semibold">المكونات الناقصة:</span>
                        {{ implode('، ', $missingColumns) }}
                    </p>
                @endif
            </div>
        @endunless

        @if($schemaReady)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow p-5">
                <p class="text-sm text-gray-500 mb-1">إجمالي الشرائح</p>
                <p class="text-3xl font-bold text-gray-900">{{ $slides->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5">
                <p class="text-sm text-gray-500 mb-1">الشرائح النشطة</p>
                <p class="text-3xl font-bold text-emerald-600">{{ $activeCount }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5">
                <p class="text-sm text-gray-500 mb-1">آخر تحديث</p>
                <p class="text-3xl font-bold text-gray-900">{{ optional($slides->first())->updated_at?->diffForHumans() ?? '—' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <form method="POST" action="{{ route('admin.hero-slides.reorder') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-gray-600 text-sm">
                            <tr>
                                <th class="px-6 py-4 text-right">الصورة</th>
                                <th class="px-6 py-4 text-right">التفاصيل</th>
                                <th class="px-6 py-4 text-right">الحالة</th>
                                <th class="px-6 py-4 text-right">الترتيب</th>
                                <th class="px-6 py-4 text-right">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($slides as $slide)
                                <tr class="hero-slide-row">
                                    <td class="px-6 py-4">
                                        <div class="w-28 h-20 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center">
                                            @if($slide->desktop_image_url)
                                                @php
                                                    $isVideoPreview = \Illuminate\Support\Str::endsWith($slide->desktop_image_url, '.webm');
                                                @endphp
                                                @if($isVideoPreview)
                                                    <video class="w-full h-full object-cover" autoplay muted loop playsinline preload="metadata">
                                                        <source src="{{ $slide->desktop_image_url }}" type="video/webm">
                                                        {{ __('المتصفح لا يدعم عرض الفيديو.') }}
                                                    </video>
                                                @else
                                                    <img src="{{ $slide->desktop_image_url }}" alt="{{ $slide->title }}" class="w-full h-full object-cover" loading="lazy">
                                                @endif
                                            @else
                                                <span class="text-gray-400 text-xs">لا توجد صورة</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-gray-900 font-semibold">{{ $slide->title }}</div>
                                            @if($slide->badge)
                                                <span class="text-xs text-orange-600 font-bold">{{ $slide->badge }}</span>
                                            @endif
                                            <p class="text-gray-500 line-clamp-2">{{ \Illuminate\Support\Str::limit($slide->description, 120) }}</p>
                                            <div class="text-xs text-gray-400 flex flex-wrap gap-2">
                                                <span><i class="fas fa-layer-group ml-1"></i>{{ count($slide->features ?? []) }} مميزات</span>
                                                <span><i class="fas fa-link ml-1"></i>{{ count($slide->actions ?? []) }} أزرار</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="status-pill {{ $slide->is_active ? 'active' : 'inactive' }}">
                                            {{ $slide->is_active ? 'نشط' : 'متوقف' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="order[{{ $slide->id }}]" value="{{ old('order.' . $slide->id, $slide->sort_order) }}" class="w-20 px-3 py-2 border border-gray-200 rounded-lg focus:ring-orange-200 focus:border-orange-400">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.hero-slides.toggle', $slide) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-2 rounded-lg {{ $slide->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}" title="تبديل الحالة">
                                                    <i class="fas {{ $slide->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الشريحة؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        لا توجد شرائح بعد. ابدأ بإضافة أول شريحة لعرضها في الهيرو.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-black transition">
                        حفظ ترتيب الشرائح
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection




