@extends('layouts.app')

@section('title', 'إضافة فيديو قصير جديد - موقع وصفة')

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }
    
    .admin-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }
    
    .admin-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0f4c73, #0a8070, #dc2626);
    }
    
    .form-input {
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        font-size: 0.95rem;
        background: #fafafa;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #0f4c73;
        box-shadow: 0 0 0 4px rgba(15, 76, 115, 0.1);
        background: white;
        transform: translateY(-1px);
    }
    
    .form-input:hover {
        border-color: #d1d5db;
        background: white;
    }
    
    .form-label {
        display: block;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        position: relative;
    }
    
    .form-label::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 30px;
        height: 2px;
        background: linear-gradient(90deg, #0f4c73, #0a8070);
        border-radius: 1px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #0f4c73 0%, #0a8070 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 14px 0 rgba(15, 76, 115, 0.39);
    }
    
    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-primary:hover::before {
        left: 100%;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #0a8070 0%, #0b344f 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px 0 rgba(15, 76, 115, 0.5);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 4px 14px 0 rgba(107, 114, 128, 0.39);
    }
    
    .btn-secondary:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px 0 rgba(107, 114, 128, 0.5);
    }
    
    .ingredient-row, .step-row, .tool-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .ingredient-row:hover, .step-row:hover, .tool-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .remove-btn {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        position: relative;
        overflow: hidden;
    }
    
    .remove-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .remove-btn:hover::before {
        left: 100%;
    }
    
    .remove-btn:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }
    
    .add-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39);
        position: relative;
        overflow: hidden;
    }
    
    .add-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .add-btn:hover::before {
        left: 100%;
    }
    
    .add-btn:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px 0 rgba(16, 185, 129, 0.5);
    }
    
    .section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0f4c73, #0a8070, #dc2626);
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        color: #0f4c73;
        font-size: 1.25rem;
    }
    
    .form-section {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .form-section:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .progress-bar {
        background: #e5e7eb;
        border-radius: 1rem;
        height: 8px;
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .progress-fill {
        background: linear-gradient(90deg, #0f4c73, #0a8070);
        height: 100%;
        border-radius: 1rem;
        transition: width 0.5s ease;
        position: relative;
    }
    
    .progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .error-message {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: shake 0.5s ease-in-out;
    }
    
    .error-message::before {
        content: '⚠️';
        font-size: 1rem;
    }
    
    .form-input.error {
        border-color: #dc2626;
        background: #fef2f2;
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .field-required::after {
        content: ' *';
        color: #dc2626;
        font-weight: bold;
    }
    
    .success-message {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .success-message::before {
        content: '✅';
        font-size: 1rem;
    }
    
    .image-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        background: #fafafa;
        cursor: pointer;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .image-upload-area:hover {
        border-color: #0f4c73;
        background: #fef7ed;
    }
    
    .image-upload-area.dragover {
        border-color: #0f4c73;
        background: #fef7ed;
        transform: scale(1.02);
    }
    
    .image-upload-container {
        position: relative;
    }
    
    .image-preview {
        position: relative;
        display: inline-block;
    }
    
    .image-preview {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .image-preview:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .floating-save {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 50;
        background: linear-gradient(135deg, #0f4c73 0%, #0a8070 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 1rem 1.5rem;
        font-weight: 700;
        box-shadow: 0 8px 25px 0 rgba(15, 76, 115, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(100px);
    }
    
    .floating-save.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .floating-save:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 12px 30px 0 rgba(15, 76, 115, 0.6);
    }
    
    @media (max-width: 768px) {
        .admin-card {
            margin: 1rem;
            border-radius: 1rem;
        }
        
        .form-section {
            padding: 1.5rem;
        }
        
        .ingredient-row, .step-row, .tool-row {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-primary, .btn-secondary {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .floating-save {
            bottom: 1rem;
            right: 1rem;
            padding: 0.75rem 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-5xl">
        <!-- Header -->
        <div class="section-header">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="section-title">
                        <i class="fas fa-plus-circle"></i>
                        إضافة فيديو قصير جديد
                    </h1>
                    <p class="text-gray-600 mt-2">أضف فيديو قصير جديد إلى مكتبة المحتوى</p>
                </div>
                <a href="{{ route('admin.recipes.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-right ml-2"></i>
                    العودة للقائمة
                </a>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.recipes.store') }}" method="POST" enctype="multipart/form-data" class="admin-card p-8" id="recipe-form">
            @csrf
            
            <!-- Basic Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    معلومات الفيديو الأساسية
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label">عنوان الفيديو</label>
                        <input type="text" name="title" class="form-input" 
                               value="{{ old('title') }}" 
                               placeholder="أدخل عنوان الفيديو القصير">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">صاحب الفيديو</label>
                        <input type="text" name="author" class="form-input" 
                               value="{{ old('author') }}" 
                               placeholder="أدخل اسم صاحب الفيديو">
                        @error('author')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="form-label">رابط الفيديو القصير</label>
                        <div class="flex flex-col gap-2">
                            <input type="url" name="video_url" class="form-input" 
                                   value="{{ old('video_url') }}" 
                                   placeholder="مثال: https://www.youtube.com/shorts/xyz أو رابط Vimeo" required>
                            <div class="flex items-center gap-2">
                                <button type="button" id="fetch-video-meta" class="btn-secondary inline-flex items-center gap-2">
                                    <i class="fas fa-magic"></i>
                                    استخراج المعلومات
                                </button>
                                <p class="text-sm text-gray-500">سيتم جلب العنوان/الصورة تلقائياً لرابط يوتيوب.</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">ندعم روابط YouTube shorts وVimeo وInstagram Reels وغيرها، وسنحوّلها تلقائياً إلى صيغة يمكن تشغيلها.</p>
                        <div id="video-meta-preview" class="hidden mt-3 p-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center gap-4">
                            <div class="w-24 h-16 rounded-lg overflow-hidden bg-gray-200 relative flex-shrink-0">
                                <img id="video-thumbnail" class="w-full h-full object-cover hidden" alt="صورة الفيديو">
                                <div id="video-thumb-fallback" class="w-full h-full grid place-items-center text-[11px] text-gray-500">لا توجد صورة</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="video-title-preview" class="text-sm font-bold text-gray-800 truncate"></p>
                                <p id="video-author-preview" class="text-xs text-gray-500 truncate"></p>
                            </div>
                            <span id="video-meta-status" class="text-xs text-gray-500"></span>
                        </div>
                        @error('video_url')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">الفئة</label>
                        <input type="text"
                               name="category_name"
                               list="category-options"
                               class="form-input"
                               value="{{ old('category_name') }}"
                               placeholder="اكتب فئة جديدة أو اختر من القائمة">
                        <datalist id="category-options">
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}"></option>
                            @endforeach
                        </datalist>
                        <p class="text-sm text-gray-500 mt-2">اكتب الفئة يدويًا، وسنضيفها تلقائياً إن لم تكن موجودة.</p>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @error('category_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">مستوى الصعوبة</label>
                        <select name="difficulty" class="form-input">
                            <option value="">اختر المستوى</option>
                            <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                            <option value="medium" {{ old('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                            <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                        </select>
                        @error('difficulty')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">وقت التحضير (دقيقة)</label>
                        <input type="number" name="prep_time" class="form-input" 
                               value="{{ old('prep_time') }}" 
                               placeholder="بالدقائق" min="0">
                        @error('prep_time')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">وقت الطبخ (دقيقة)</label>
                        <input type="number" name="cook_time" class="form-input" 
                               value="{{ old('cook_time') }}" 
                               placeholder="بالدقائق" min="0">
                        @error('cook_time')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">عدد الحصص</label>
                        <input type="number" name="servings" class="form-input" 
                               value="{{ old('servings') }}" 
                               placeholder="عدد الأشخاص" min="0">
                        @error('servings')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">صور الغلاف (اختياري)</label>
                        <p class="text-sm text-gray-600 mb-4">يمكنك إضافة حتى 5 صور للفيديو القصير لاستخدامها كغلاف أو صور توضيحية</p>
                        
                        <!-- صور الفيديو -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @for($i = 1; $i <= 5; $i++)
                            <div class="image-upload-container">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    صورة {{ $i }}
                                </label>
                                
                                <div class="image-upload-area" onclick="document.getElementById('image_{{$i}}').click()">
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-sm font-medium text-gray-700 mb-1">اسحب وأفلت الصورة هنا</p>
                                        <p class="text-xs text-gray-500 mb-3">أو انقر للاختيار</p>
                                        <div class="inline-flex items-center px-3 py-1 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm">
                                            <i class="fas fa-folder-open ml-1"></i>
                                            اختر صورة
                                        </div>
                                    </div>
                                    <input type="file"
                                           name="image{{ $i == 1 ? '' : '_' . $i }}"
                                           id="image_{{$i}}"
                                           class="hidden"
                                           accept="image/*"
                                           onchange="previewImage(this, {{$i}})"
                                           data-max-size="5120"
                                           data-max-size-message="لا يمكن رفع صورة أكبر من 5 ميجابايت."
                                           data-error-target="#image_{{$i}}_error">
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-info-circle ml-1"></i>
                                        JPG, PNG, GIF - الحد الأقصى 5MB
                                    </p>
                                    <p id="image_{{$i}}_error" class="text-xs text-red-600 mt-1 hidden"></p>
                                    @error('image' . ($i == 1 ? '' : '_' . $i))
                                        <div class="error-message mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- معاينة الصورة -->
                                <div id="image-preview-{{$i}}" class="hidden mt-3">
                                    <div class="image-preview">
                                        <img id="preview-img-{{$i}}" src="" alt="معاينة الصورة {{$i}}" 
                                             class="w-full h-32 object-cover rounded-xl" loading="lazy">
                                        <button type="button" onclick="removeImage({{$i}})" 
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        
                        <!-- أو رابط -->
                        <div class="border-t pt-6 mt-6">
                            <p class="text-sm text-gray-600 mb-3 font-medium">أو أدخل رابط الصورة: <span class="text-gray-400">(اختياري)</span></p>
                            <input type="url" name="image_url" class="form-input" 
                                   value="{{ old('image_url') }}" 
                                   placeholder="رابط الصورة من Google Drive">
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-info-circle ml-1"></i>
                                رابط مباشر للصورة أو رابط Google Drive (اختياري)
                            </p>
                            @error('image_url')
                                <div class="error-message mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="form-label">وصف الفيديو</label>
                    <textarea name="description" class="form-input" rows="4" 
                              placeholder="اكتب وصفاً مختصراً وجذاباً للفيديو القصير...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Ingredients -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="section-title">
                        <i class="fas fa-carrot"></i>
                        المكونات
                    </h2>
                    <button type="button" id="add-ingredient" class="add-btn">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة مكون
                    </button>
                </div>
                
                <div id="ingredients-container">
                    <div class="ingredient-row">
                        <input type="text" name="ingredients[0][name]" class="form-input flex-1" 
                               placeholder="اسم المكون">
                        <input type="text" name="ingredients[0][amount]" class="form-input flex-1" 
                               placeholder="الكمية">
                        <button type="button" class="remove-btn" onclick="removeIngredient(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tools -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="section-title">
                        <i class="fas fa-kitchen-set"></i>
                        المعدات المستخدمة
                    </h2>
                    <button type="button" id="add-tool" class="add-btn">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة معدة
                    </button>
                </div>
                
                <div id="tools-container">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-tools text-4xl text-gray-300 mb-3"></i>
                        <p>لا توجد معدات محددة لهذا الفيديو</p>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="section-title">
                        <i class="fas fa-list-ol"></i>
                        خطوات التحضير
                    </h2>
                    <button type="button" id="add-step" class="add-btn">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة خطوة
                    </button>
                </div>
                
                <div id="steps-container">
                    <div class="step-row">
                        <textarea name="steps[0]" class="form-input flex-1" rows="3" 
                                  placeholder="اكتب خطوة التحضير بالتفصيل..."></textarea>
                        <button type="button" class="remove-btn" onclick="removeStep(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4 rtl:space-x-reverse">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save ml-2"></i>
                    حفظ الفيديو
                </button>
                <a href="{{ route('admin.recipes.index') }}" class="btn-secondary">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Floating Save Button -->
<button type="button" class="floating-save" id="floating-save" onclick="document.getElementById('recipe-form').submit()">
    <i class="fas fa-save ml-2"></i>
    حفظ
</button>

@push('scripts')
<script>
let ingredientIndex = 1;
let stepIndex = 1;
let toolIndex = 0;
let hasUnsavedChanges = false;
let autoSaveInterval;
let lastVideoMeta = null;

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    setupProgressBar();
    setupAutoSave();
    setupFloatingSave();
    setupFormValidation();
    setupYouTubeAutoFill();
});

// Initialize form functionality
function initializeForm() {
    // Add event listeners to all form inputs
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('input', markAsChanged);
        input.addEventListener('change', markAsChanged);
    });
}

// Mark form as having unsaved changes
function markAsChanged() {
    hasUnsavedChanges = true;
    showFloatingSave();
}

// Setup progress bar
function setupProgressBar() {
    updateProgress();
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });
}

// Update progress bar - now based on all fields
function updateProgress() {
    const allFields = document.querySelectorAll('input, textarea, select');
    const filledFields = Array.from(allFields).filter(field => {
        if (field.type === 'file') return field.files.length > 0;
        return field.value.trim() !== '';
    });
    
    const progress = allFields.length > 0 ? (filledFields.length / allFields.length) * 100 : 0;
    document.getElementById('progress-fill').style.width = progress + '%';
}

// Setup auto-save functionality
function setupAutoSave() {
    // Auto-save every 30 seconds if there are changes
    autoSaveInterval = setInterval(() => {
        if (hasUnsavedChanges) {
            autoSave();
        }
    }, 30000);
}

// Auto-save function
function autoSave() {
    // In a real implementation, you would send the form data to a save endpoint
    console.log('Auto-saving...');
    hasUnsavedChanges = false;
    showNotification('تم الحفظ التلقائي', 'success');
}

// Setup floating save button
function setupFloatingSave() {
    const floatingSave = document.getElementById('floating-save');
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            floatingSave.classList.add('show');
        } else {
            floatingSave.classList.remove('show');
        }
    });
}

// Show floating save button
function showFloatingSave() {
    const floatingSave = document.getElementById('floating-save');
    floatingSave.classList.add('show');
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('recipe-form');
    
    form.addEventListener('submit', function(e) {
        // All fields are now optional, so no validation needed
        console.log('Form submitted - all fields are optional');
    });
}

// Validate form - now all fields are optional
function validateForm() {
    // All fields are optional, so always return true
    return true;
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// -----------------------
// YouTube metadata helper
// -----------------------
const videoUrlInput = document.querySelector('input[name="video_url"]');
const titleInput = document.querySelector('input[name="title"]');
const authorInput = document.querySelector('input[name="author"]');
const descriptionInput = document.querySelector('textarea[name="description"]');
const fetchVideoMetaBtn = document.getElementById('fetch-video-meta');
const categoryNameInput = document.querySelector('input[name="category_name"]');
const metaPreviewBox = document.getElementById('video-meta-preview');
const metaThumb = document.getElementById('video-thumbnail');
const metaThumbFallback = document.getElementById('video-thumb-fallback');
const metaTitleEl = document.getElementById('video-title-preview');
const metaAuthorEl = document.getElementById('video-author-preview');
const metaStatusEl = document.getElementById('video-meta-status');
const videoMetaCache = new Map();

function extractYouTubeId(url) {
    try {
        const parsed = new URL(url.trim());
        const host = parsed.hostname.replace(/^www\./, '');
        if (!host.includes('youtube.com') && !host.includes('youtu.be')) return '';
        if (parsed.searchParams.get('v')) {
            return parsed.searchParams.get('v');
        }
        const parts = parsed.pathname.split('/').filter(Boolean);
        if (parts[0] === 'embed' || parts[0] === 'shorts') {
            return parts[1] || '';
        }
        if (host === 'youtu.be') {
            return parts[0] || '';
        }
        return '';
    } catch (e) {
        return '';
    }
}

function buildYouTubeWatchUrl(url) {
    const id = extractYouTubeId(url);
    return id ? `https://www.youtube.com/watch?v=${id}` : url;
}

async function fetchYouTubeMeta(rawUrl) {
    const targetUrl = buildYouTubeWatchUrl(rawUrl);
    if (videoMetaCache.has(targetUrl)) return videoMetaCache.get(targetUrl);
    const endpoint = `https://www.youtube.com/oembed?format=json&url=${encodeURIComponent(targetUrl)}`;
    const response = await fetch(endpoint);
    if (!response.ok) throw new Error('YouTube oEmbed failed');
    const data = await response.json();
    const meta = {
        title: data?.title || '',
        author: data?.author_name || '',
        thumbnail: data?.thumbnail_url || ''
    };
    videoMetaCache.set(targetUrl, meta);
    return meta;
}

function renderVideoMetaPreview(meta, statusText = '') {
    if (!metaPreviewBox) return;
    metaPreviewBox.classList.remove('hidden');
    metaTitleEl.textContent = meta?.title || '';
    metaAuthorEl.textContent = meta?.author || '';
    metaStatusEl.textContent = statusText || '';

    if (meta?.thumbnail) {
        metaThumb.src = meta.thumbnail;
        metaThumb.classList.remove('hidden');
        metaThumbFallback.classList.add('hidden');
    } else {
        metaThumb.classList.add('hidden');
        metaThumbFallback.classList.remove('hidden');
    }
}

async function fetchAndApplyVideoMeta() {
    const rawUrl = videoUrlInput?.value.trim();
    if (!rawUrl || (!rawUrl.includes('youtu.be') && !rawUrl.includes('youtube.com'))) return;
    renderVideoMetaPreview({}, 'جاري جلب بيانات الفيديو...');
    try {
        const meta = await fetchYouTubeMeta(rawUrl);
        lastVideoMeta = meta;
        if (meta.title && titleInput) {
            titleInput.value = meta.title;
            markAsChanged();
        }
        if (meta.author && authorInput && !authorInput.value.trim()) {
            authorInput.value = meta.author;
            markAsChanged();
        }
        if (meta.author && categoryNameInput && !categoryNameInput.value.trim()) {
            categoryNameInput.value = meta.author;
            markAsChanged();
        }
        if (meta.title && descriptionInput && !descriptionInput.value.trim()) {
            descriptionInput.value = `فيديو يوتيوب: ${meta.title}`;
            markAsChanged();
        }
        renderVideoMetaPreview(meta, 'تم تعبئة العنوان من يوتيوب');
    } catch (e) {
        renderVideoMetaPreview({}, 'تعذر جلب بيانات يوتيوب');
    }
}

function setupYouTubeAutoFill() {
    if (!videoUrlInput) return;
    videoUrlInput.addEventListener('change', fetchAndApplyVideoMeta);
    videoUrlInput.addEventListener('blur', fetchAndApplyVideoMeta);
    if (fetchVideoMetaBtn) {
        fetchVideoMetaBtn.addEventListener('click', fetchAndApplyVideoMeta);
    }
}

// Add Ingredient
document.getElementById('add-ingredient').addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.innerHTML = `
        <input type="text" name="ingredients[${ingredientIndex}][name]" class="form-input flex-1" 
               placeholder="اسم المكون" required>
        <input type="text" name="ingredients[${ingredientIndex}][amount]" class="form-input flex-1" 
               placeholder="الكمية" required>
        <button type="button" class="remove-btn" onclick="removeIngredient(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    ingredientIndex++;
    markAsChanged();
    updateProgress();
    
    // Add animation
    row.style.opacity = '0';
    row.style.transform = 'translateY(-20px)';
    setTimeout(() => {
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
    }, 10);
});

// Add Tool
document.getElementById('add-tool').addEventListener('click', function() {
    const container = document.getElementById('tools-container');
    
    // إزالة الرسالة الفارغة إذا كانت موجودة
    const emptyMessage = container.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    const row = document.createElement('div');
    row.className = 'tool-row';
    
    // إنشاء قائمة منسدلة مع خيارات المعدات
    let selectOptions = '<option value="">اختر معدة</option>';
    @foreach($tools as $tool)
        selectOptions += '<option value="{{ $tool->id }}">{{ $tool->name }}</option>';
    @endforeach
    
    row.innerHTML = `
        <select name="tools[${toolIndex}]" class="form-input flex-1" required>
            ${selectOptions}
        </select>
        <button type="button" class="remove-btn" onclick="removeTool(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    toolIndex++;
});

// Add Step
document.getElementById('add-step').addEventListener('click', function() {
    const container = document.getElementById('steps-container');
    const row = document.createElement('div');
    row.className = 'step-row';
    row.innerHTML = `
        <textarea name="steps[${stepIndex}]" class="form-input flex-1" rows="3" 
                  placeholder="اكتب خطوة التحضير هنا" required></textarea>
        <button type="button" class="remove-btn" onclick="removeStep(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    stepIndex++;
    markAsChanged();
    updateProgress();
    
    // Add animation
    row.style.opacity = '0';
    row.style.transform = 'translateY(-20px)';
    setTimeout(() => {
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
    }, 10);
});

// Remove Ingredient
function removeIngredient(button) {
    if (confirm('هل أنت متأكد من حذف هذا المكون؟')) {
        const row = button.parentElement;
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            row.remove();
            markAsChanged();
            updateProgress();
        }, 300);
    }
}

// Remove Tool
function removeTool(button) {
    if (confirm('هل أنت متأكد من حذف هذه المعدة؟')) {
        const container = document.getElementById('tools-container');
        const row = button.parentElement;
        
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            row.remove();
            markAsChanged();
            updateProgress();
            
            // إظهار الرسالة الفارغة إذا لم تعد هناك معدات
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-kitchen-set text-4xl text-gray-300 mb-3"></i>
                        <p>لا توجد معدات محددة لهذا الفيديو</p>
                    </div>
                `;
            }
        }, 300);
    }
}

// Remove Step
function removeStep(button) {
    if (confirm('هل أنت متأكد من حذف هذه الخطوة؟')) {
        const row = button.parentElement;
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            row.remove();
            markAsChanged();
            updateProgress();
        }, 300);
    }
}

// معاينة الصورة
function previewImage(input, imageNumber = 1) {
    const preview = document.getElementById(`image-preview-${imageNumber}`);
    const previewImg = document.getElementById(`preview-img-${imageNumber}`);
    
    if (input.files && input.files[0]) {
        // Check file size (5MB limit)
        if (input.files[0].size > 5 * 1024 * 1024) {
            showNotification('حجم الصورة يجب أن يكون أقل من 5 ميجابايت', 'error');
            input.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(input.files[0].type)) {
            showNotification('نوع الملف غير مدعوم. يرجى اختيار صورة JPG, PNG أو GIF', 'error');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
            markAsChanged();
            showNotification(`تم تحميل الصورة ${imageNumber} بنجاح`, 'success');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}

// حذف الصورة
function removeImage(imageNumber) {
    const input = document.getElementById(`image_${imageNumber}`);
    const preview = document.getElementById(`image-preview-${imageNumber}`);
    
    input.value = '';
    preview.classList.add('hidden');
    markAsChanged();
    showNotification(`تم حذف الصورة ${imageNumber}`, 'success');
}

// Drag and drop functionality
function setupDragAndDrop() {
    const uploadAreas = document.querySelectorAll('.image-upload-area');
    
    uploadAreas.forEach((uploadArea, index) => {
        const imageNumber = index + 1;
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById(`image_${imageNumber}`);
                fileInput.files = files;
                previewImage(fileInput, imageNumber);
            }
        });
    });
}

// Initialize drag and drop
setupDragAndDrop();
</script>
@endpush
@endsection




