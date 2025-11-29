@extends('layouts.app')

@section('title', $recipe->title . ' - إدارة الفيديوهات القصيرة')

@php
    use App\Models\Recipe;
    use App\Support\VideoEmbed;

    $statusMeta = [
        Recipe::STATUS_DRAFT => ['label' => 'مسودة', 'classes' => 'bg-gray-100 text-gray-700'],
        Recipe::STATUS_PENDING => ['label' => 'قيد المراجعة', 'classes' => 'bg-orange-100 text-orange-700'],
        Recipe::STATUS_APPROVED => ['label' => 'معتمدة', 'classes' => 'bg-emerald-100 text-emerald-700'],
        Recipe::STATUS_REJECTED => ['label' => 'مرفوضة', 'classes' => 'bg-red-100 text-red-700'],
    ];

    $difficultyLabels = [
        'easy' => 'سهل',
        'medium' => 'متوسط',
        'hard' => 'صعب',
    ];

    $embedUrl = VideoEmbed::embedUrl($recipe->video_url);
    $canPlayInline = VideoEmbed::inlinePlayable($recipe->video_url);
@endphp

@push('styles')
<style>
    .admin-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #f3f4f6;
    }
    .btn-primary {
        background: linear-gradient(135deg, #0819ff 0%, #0f78ad 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #0f78ad 0%, #0b344f 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(8, 25, 255, 0.4);
    }
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-secondary:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
    }
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        padding: 0.35rem 0.875rem;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-badge .dot {
        width: 0.6rem;
        height: 0.6rem;
        border-radius: 9999px;
        background-color: currentColor;
    }
    .status-note {
        font-size: 0.85rem;
    }
    .info-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-icon {
        width: 1.5rem;
        color: #0819ff;
        margin-left: 1rem;
    }
    .step-number {
        background: #0819ff;
        color: white;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-left: 1rem;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        @php
            $status = $statusMeta[$recipe->status] ?? $statusMeta[Recipe::STATUS_DRAFT];
            $difficultyLabel = $recipe->difficulty ? ($difficultyLabels[$recipe->difficulty] ?? 'غير محدد') : 'غير محدد';
        @endphp
        <!-- Header -->
        <div class="admin-card p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $recipe->title }}</h1>
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="text-gray-600">عرض تفاصيل الفيديو القصير</p>
                        <span class="status-badge {{ $status['classes'] }}">
                            <span class="dot"></span>
                            {{ $status['label'] }}
                        </span>
                    </div>
                    @if($recipe->status === Recipe::STATUS_PENDING)
                        <p class="status-note text-orange-600 mt-2">هذا الفيديو بانتظار موافقة الإدارة قبل النشر للجمهور.</p>
                    @elseif($recipe->status === Recipe::STATUS_REJECTED)
                        <p class="status-note text-red-600 mt-2">تم رفض الفيديو. يرجى التواصل مع الشيف لتوضيح التعديلات المطلوبة.</p>
                    @elseif($recipe->status === Recipe::STATUS_APPROVED && $recipe->approved_at)
                        <p class="status-note text-emerald-600 mt-2">تم اعتماد الفيديو بتاريخ {{ $recipe->approved_at->locale('ar')->translatedFormat('d F Y - h:i a') }}.</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-4 md:mt-0">
                    @if($recipe->status !== Recipe::STATUS_APPROVED)
                        <form method="POST" action="{{ route('admin.recipes.approve', $recipe) }}" onsubmit="return confirm('هل تريد اعتماد هذا الفيديو ونشره الآن؟');">
                            @csrf
                            <button type="submit" class="btn-success">
                                <i class="fas fa-check ml-2"></i>
                                اعتماد الفيديو
                            </button>
                        </form>
                    @endif
                    @if($recipe->status === Recipe::STATUS_PENDING)
                        <form method="POST" action="{{ route('admin.recipes.reject', $recipe) }}" onsubmit="return confirm('هل تريد رفض هذا الفيديو وإعادته للشيف؟');">
                            @csrf
                            <button type="submit" class="btn-danger">
                                <i class="fas fa-times ml-2"></i>
                                رفض الفيديو
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.recipes.edit', $recipe) }}" class="btn-primary">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل
                    </a>
                    <a href="{{ route('admin.recipes.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Video Preview and Basic Info -->
            <div class="lg:col-span-2">
                <div class="admin-card overflow-hidden mb-8">
                    @if($recipe->video_url)
                        <div class="relative w-full bg-black" style="padding-top: 56.25%;">
                            @if($embedUrl)
                                <iframe src="{{ $embedUrl }}" class="absolute inset-0 w-full h-full"
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen></iframe>
                            @elseif($canPlayInline)
                                <video controls playsinline class="absolute inset-0 w-full h-full object-cover">
                                    <source src="{{ $recipe->video_url }}">
                                    متصفحك لا يدعم تشغيل الفيديو.
                                </video>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center px-6 text-center text-gray-100">
                                    <div>
                                        <p class="font-semibold mb-2">لا يمكن تضمين الرابط تلقائياً</p>
                                        <a href="{{ $recipe->video_url }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-700">
                                            <i class="fas fa-external-link-alt ml-1"></i>
                                            فتح الرابط في تبويب جديد
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-start gap-2">
                            <i class="fas fa-link text-emerald-700 mt-1"></i>
                            <a href="{{ $recipe->video_url }}" target="_blank" rel="noopener"
                               class="text-sm text-emerald-700 hover:text-emerald-900 break-all">{{ $recipe->video_url }}</a>
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-500">
                            لا يوجد رابط فيديو بعد.
                        </div>
                    @endif
                </div>

                <div class="admin-card overflow-hidden mb-8">
                    <img src="{{ $recipe->image_url_display ?? $recipe->image_url }}" alt="{{ $recipe->title }}" 
                         class="w-full h-64 md:h-80 object-cover"
                        onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='صورة افتراضية';" loading="lazy">
                </div>

                <!-- Description -->
                <div class="admin-card p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">وصف الفيديو</h2>
                    <p class="text-gray-700 leading-relaxed">{{ $recipe->description }}</p>
                </div>

                <!-- Steps -->
                <div class="admin-card p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">خطوات أو ملاحظات إضافية</h2>
                    <div class="space-y-6">
                        @php $steps = (array) ($recipe->steps ?? []); @endphp
                        @forelse($steps as $index => $step)
                            <div class="flex items-start">
                                <div class="step-number">{{ $index + 1 }}</div>
                                <p class="text-gray-700 leading-relaxed flex-1">{{ $step }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">لا توجد خطوات تحضير متاحة.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Recipe Info -->
                <div class="admin-card p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">معلومات الفيديو</h3>
                    
                    <div class="space-y-0">
                        <div class="info-item">
                            <i class="fas fa-user info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">صاحب الفيديو</span>
                                <p class="font-semibold text-gray-900">{{ $recipe->chef?->name ?? $recipe->author }}</p>
                                @if($recipe->chef)
                                    <p class="text-xs text-gray-500">{{ $recipe->chef->email }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-link info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">رابط الفيديو</span>
                                @if($recipe->video_url)
                                    <a href="{{ $recipe->video_url }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-900 break-all">
                                        {{ \Illuminate\Support\Str::limit($recipe->video_url, 70) }}
                                    </a>
                                @else
                                    <p class="font-semibold text-gray-900">غير متوفر</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-tag info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">الفئة</span>
                                <p class="font-semibold text-gray-900">{{ $recipe->category->name ?? 'غير محدد' }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-signal info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">مستوى الصعوبة</span>
                                <p class="font-semibold text-gray-900">{{ $difficultyLabel }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-clock info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">وقت التحضير</span>
                                <p class="font-semibold text-gray-900">{{ (int) ($recipe->prep_time ?? 0) > 0 ? (int) $recipe->prep_time . ' دقيقة' : 'غير محدد' }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-fire info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">وقت الطبخ</span>
                                <p class="font-semibold text-gray-900">{{ (int) ($recipe->cook_time ?? 0) > 0 ? (int) $recipe->cook_time . ' دقيقة' : 'غير محدد' }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-users info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">عدد الحصص</span>
                                <p class="font-semibold text-gray-900">{{ (int) ($recipe->servings ?? 0) > 0 ? (int) $recipe->servings . ' حصة' : 'غير محدد' }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-calendar info-icon"></i>
                            <div>
                                <span class="text-gray-500 text-sm">تاريخ الإنشاء</span>
                                <p class="font-semibold text-gray-900">{{ $recipe->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="admin-card p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">المكونات</h3>
                    
                    <div class="space-y-3">
                        @foreach($recipe->ingredients as $ingredient)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-900">{{ $ingredient->name }}</span>
                                <span class="text-orange-600 font-semibold">{{ $ingredient->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="admin-card p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">الإجراءات</h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('admin.recipes.edit', $recipe) }}" class="btn-primary w-full text-center">
                            <i class="fas fa-edit ml-2"></i>
                            تعديل الفيديو
                        </a>
                        
                        <form action="{{ route('admin.recipes.destroy', $recipe) }}" method="POST" 
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الفيديو؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger w-full">
                                <i class="fas fa-trash ml-2"></i>
                                حذف الفيديو
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.recipes.index') }}" class="btn-secondary w-full text-center">
                            <i class="fas fa-arrow-right ml-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








