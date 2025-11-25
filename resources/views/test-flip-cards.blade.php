<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>اختبار الكروت القابلة للقلب</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Flip Card Styles */
        .card-container {
            perspective: 1000px;
            width: 280px;
            height: 400px;
            cursor: pointer;
            margin: 0;
        }

        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .card-container.is-flipped .card-inner {
            transform: rotateY(180deg);
        }

        .card-front,
        .card-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
        }

        .card-front { 
            background: #fff; 
        }
        
        .card-back { 
            background: #fff; 
            transform: rotateY(180deg); 
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Ensure proper card flip behavior */
        .card-container {
            position: relative;
            user-select: none;
        }

        .card-container .card-inner {
            width: 100%;
            height: 100%;
        }

        /* Smooth transitions for better UX */
        .card-container * {
            pointer-events: none;
        }

        .card-container a,
        .card-container button {
            pointer-events: auto;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">اختبار الكروت القابلة للقلب</h1>
        <p class="text-gray-600 mb-8 text-center">اضغط على أي كارت لقلبه ورؤية الأزرار في الخلف</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center">
            @foreach(App\Models\Recipe::with('category')->limit(8)->get() as $recipe)
                <div class="card-container" data-recipe-id="{{ $recipe->recipe_id }}">
                    <div class="card-inner">
                        <!-- Front of the card -->
                        <div class="card-front">
                            <div class="relative h-full">
                                <img src="{{ $recipe->image_url ?: \App\Support\BrandAssets::logoAsset('webp') }}" 
                                     alt="{{ $recipe->title }}" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='صورة افتراضية';" loading="lazy">
                                
                                <!-- Overlay with recipe info -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-end p-6">
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
                                            <span>{{ $recipe->author ?? 'غير محدد' }}</span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-clock ml-2 text-orange-500"></i>
                                            <span>{{ $recipe->prep_time }} دقيقة تحضير</span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-fire ml-2 text-orange-500"></i>
                                            <span>{{ $recipe->cook_time ?? 0 }} دقيقة طبخ</span>
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
                                        <a href="{{ route('recipe.show', $recipe->recipe_id) }}" 
                                           class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg text-center text-sm transition-all duration-300 hover:shadow-lg">
                                            <i class="fas fa-eye ml-1"></i>
                                            عرض الوصفة
                                        </a>
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
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize flip cards
            const cardContainers = document.querySelectorAll('.card-container');
            
            cardContainers.forEach((card) => {
                card.addEventListener('click', function(e) {
                    // Don't flip if clicking on save button or links
                    if (e.target.closest('.save-recipe-btn') || e.target.closest('a')) {
                        return;
                    }
                    
                    this.classList.toggle('is-flipped');
                    console.log('Card flipped!');
                });
            });

            // Initialize save buttons
            const saveButtons = document.querySelectorAll('.save-recipe-btn');
            
            saveButtons.forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent card flip
                    e.stopImmediatePropagation(); // Prevent other event handlers
                    
                    const recipeId = this.dataset.recipeId;
                    const recipeName = this.dataset.recipeName;
                    
                    console.log('Saving recipe:', recipeId, recipeName);
                    
                    try {
                        // Get CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        // Make API call
                        const response = await fetch('/api/interactions', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'include',
                            body: JSON.stringify({
                                recipe_id: recipeId,
                                is_saved: true
                            })
                        });
                        
                        if (response.status === 401) {
                            alert('يجب تسجيل الدخول أولاً');
                            window.location.href = '/login';
                            return;
                        }
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        
                        const data = await response.json();
                        console.log('Recipe saved:', data);
                        
                        // Update button
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        this.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
                        this.classList.add('bg-green-500', 'hover:bg-green-600', 'text-white');
                        this.disabled = true;
                        
                        alert('تم حفظ الوصفة بنجاح!');
                        
                    } catch (error) {
                        console.error('Error saving recipe:', error);
                        alert('حدث خطأ أثناء حفظ الوصفة: ' + error.message);
                    }
                });
            });
        });
    </script>
</body>
</html>



