<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>اختبار حفظ الوصفات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">اختبار حفظ الوصفات</h1>
        
        @auth
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle ml-2"></i>
                أنت مسجل دخول كـ: {{ Auth::user()->name }} ({{ Auth::user()->email }})
            </div>
        @else
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-triangle ml-2"></i>
                أنت غير مسجل دخول. <a href="/login" class="underline">سجل دخول هنا</a>
            </div>
        @endauth

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(App\Models\Recipe::with('category')->limit(6)->get() as $recipe)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="{{ $recipe->image_url ?: \App\Support\BrandAssets::logoAsset('webp') }}" 
                         alt="{{ $recipe->title }}" 
                         class="w-full h-48 object-cover"
                         onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $recipe->title }}</h3>
                        <p class="text-gray-600 mb-4">{{ $recipe->category->name ?? 'حلويات' }}</p>
                        
                        <button class="save-recipe-btn w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg transition-colors"
                                data-recipe-id="{{ $recipe->recipe_id }}"
                                data-recipe-name="{{ $recipe->title }}">
                            <i class="fas fa-bookmark ml-2"></i>
                            حفظ الوصفة
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveButtons = document.querySelectorAll('.save-recipe-btn');
            
            saveButtons.forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    const recipeId = this.dataset.recipeId;
                    const recipeName = this.dataset.recipeName;
                    
                    console.log('Attempting to save recipe:', recipeId, recipeName);
                    
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
                        
                        console.log('Response status:', response.status);
                        
                        if (response.status === 401) {
                            alert('يجب تسجيل الدخول أولاً');
                            window.location.href = '/login';
                            return;
                        }
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        
                        const data = await response.json();
                        console.log('Response data:', data);
                        
                        // Update button
                        this.innerHTML = '<i class="fas fa-check ml-2"></i>تم الحفظ!';
                        this.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                        this.classList.add('bg-green-500', 'hover:bg-green-600');
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



