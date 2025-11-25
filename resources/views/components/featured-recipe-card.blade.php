@props(['recipe'])

<div class="card-container" data-recipe-id="{{ $recipe->recipe_id }}">
    <div class="card-inner">
        <!-- Front of the card -->
        <div class="card-front">
            <div class="relative h-full">
                <img src="{{ $recipe->image_url ?: \App\Support\BrandAssets::logoAsset('webp') }}" 
                     alt="{{ $recipe->title }}" 
                     class="w-full h-full object-cover"
                     width="600"
                     height="800"
                     onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='صورة افتراضية';" loading="lazy">
                
                <!-- Overlay with recipe info -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-between p-6">
                    <!-- Save button at top -->
                    <div class="flex justify-end">
     
                    </div>
                    
                    <!-- Recipe info at bottom -->
                    <div class="text-white">
                        <div class="flex items-center justify-between mb-2">
                            <span class="bg-orange-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                {{ $recipe->category->name ?? 'حلويات' }}
                            </span>
                            <div class="flex items-center space-x-1 rtl:space-x-reverse">
                                @if($recipe->interactions_avg_rating)
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-sm font-medium">{{ number_format($recipe->interactions_avg_rating, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        <h3 class="text-lg font-bold mb-2 line-clamp-2">{{ $recipe->title }}</h3>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center">
                                <i class="fas fa-clock ml-1"></i>
                                {{ $recipe->prep_time }} دقيقة
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-users ml-1"></i>
                                {{ $recipe->servings }} حصة
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back of the card -->
        <div class="card-back">
            <div class="p-6 h-full flex flex-col">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $recipe->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($recipe->description, 120) }}</p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-user ml-2 text-orange-500"></i>
                            <span>{{ $recipe->author }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-clock ml-2 text-orange-500"></i>
                            <span>{{ $recipe->prep_time }} دقيقة تحضير</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-fire ml-2 text-orange-500"></i>
                            <span>{{ $recipe->cook_time }} دقيقة طبخ</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users ml-2 text-orange-500"></i>
                            <span>{{ $recipe->servings }} حصة</span>
                        </div>
                        @if($recipe->interactions_avg_rating)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-star ml-2 text-yellow-500"></i>
                                <span>{{ number_format($recipe->interactions_avg_rating, 1) }} تقييم</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-auto">
                        <div class="flex space-x-2 rtl:space-x-reverse">
                        @if($recipe->is_registration_closed)
                            <button class="flex-1 bg-yellow-400 text-yellow-800 font-semibold py-2 px-4 rounded-lg text-center text-sm cursor-not-allowed flex items-center justify-center">
                                <i class="fas fa-clock ml-1"></i>
                                انتهت مهلة الحجز
                            </button>
                        @else
                            <a href="{{ route('recipe.show', $recipe->slug) }}" 
                               class="view-recipe-btn-link flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg text-center text-sm transition-all duration-300 hover:shadow-lg">
                                <i class="fas fa-eye ml-1"></i>
                                عرض الوصفة
                            </a>
                        @endif
                        <button class="save-recipe-btn font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg flex items-center justify-center {{ $recipe->is_saved ? 'bg-green-500 text-white hover:bg-green-600' : 'bg-orange-500 text-white hover:bg-orange-600' }}"
                                data-recipe-id="{{ $recipe->recipe_id }}"
                                data-recipe-name="{{ $recipe->title }}"
                                data-saved="{{ $recipe->is_saved ? 'true' : 'false' }}"
                                title="{{ $recipe->is_saved ? 'إلغاء الحفظ' : 'حفظ الوصفة' }}">
                            <i class="fas fa-bookmark ml-1"></i>
                            <span class="text-sm">{{ $recipe->is_saved ? 'محفوظة' : 'حفظ' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



