@extends('layouts.app')

@section('title', 'Ø¥Ø¶Ø§ÙØ© Ø£Ø¯Ø§Ø© Ø´ÙŠÙ Ø¬Ø¯ÙŠØ¯Ø© - Ù„ÙˆØ­Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©')

@push('styles')
<style>
    .admin-card {
        background: linear-gradient(135deg, #a56970 0%, #8a4348 100%);
    }
    .form-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }
    .feature-input {
        transition: all 0.3s ease;
    }
    .feature-input:focus {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="admin-card text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex items-center">
                <a href="{{ route('admin.tools.index') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white p-3 rounded-lg transition-all duration-300 mr-4">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold mb-2">Ø¥Ø¶Ø§ÙØ© Ø£Ø¯Ø§Ø© Ø´ÙŠÙ Ø¬Ø¯ÙŠØ¯Ø©</h1>
                    <p class="text-blue-100">Ø£Ø¶Ù Ø£Ø¯Ø§Ø© Ø´ÙŠÙ Ø§Ø­ØªØ±Ø§ÙÙŠØ© Ø¬Ø¯ÙŠØ¯Ø© Ù„ØµÙ†Ø§Ø¹Ø© Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="form-section rounded-xl shadow-lg p-8">
                <form action="{{ route('admin.tools.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="text-red-800 font-semibold mb-2">ÙŠØ±Ø¬Ù‰ ØªØµØ­ÙŠØ­ Ø§Ù„Ø£Ø®Ø·Ø§Ø¡ Ø§Ù„ØªØ§Ù„ÙŠØ©:</h4>
                            <ul class="text-red-700 text-sm space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>â€¢ {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-700">{{ session('error') }}</p>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Basic Information -->
                        <div class="space-y-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Ø§Ù„Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©</h3>
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø§Ø³Ù… Ø§Ù„Ø£Ø¯Ø§Ø© <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø§Ù„ÙˆØµÙ <span class="text-red-500">*</span>
                                </label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø§Ù„ÙØ¦Ø© <span class="text-red-500">*</span>
                                </label>
                                <select id="category" 
                                        name="category" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                        required>
                                    <option value="">Ø§Ø®ØªØ± Ø§Ù„ÙØ¦Ø©</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø§Ù„Ø³Ø¹Ø± (Ø¯Ø±Ù‡Ù…) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           id="price" 
                                           name="price" 
                                           step="0.01"
                                           min="0"
                                           value="{{ old('price') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">JOD</span>
                                    </div>
                                </div>
                                <div id="price-conversion" class="mt-2 hidden"></div>
                                @error('price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Rating -->
                            <div>
                                <label for="rating" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø§Ù„ØªÙ‚ÙŠÙŠÙ… (0-5)
                                </label>
                                <input type="number" 
                                       id="rating" 
                                       name="rating" 
                                       step="0.1"
                                       min="0"
                                       max="5"
                                       value="{{ old('rating') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                                @error('rating')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø¥Ø¶Ø§ÙÙŠØ©</h3>
                            
                            <!-- Image -->
                            <div>
                                <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                                    ØµÙˆØ±Ø© Ø§Ù„Ø£Ø¯Ø§Ø©
                                </label>
                                <input type="file" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                       data-max-size="25600"
                                       data-max-size-message="Ù„Ø§ ÙŠÙ…ÙƒÙ† Ø±ÙØ¹ ØµÙˆØ±Ø© Ø£ÙƒØ¨Ø± Ù…Ù† 25 Ù…ÙŠØ¬Ø§Ø¨Ø§ÙŠØª."
                                       data-error-target="#tool_image_error">
                                <p class="text-sm text-gray-500 mt-2">Ø§Ù„Ø­Ø¯ Ø§Ù„Ø£Ù‚ØµÙ‰ Ù„Ø­Ø¬Ù… Ø§Ù„ØµÙˆØ±Ø© Ù‡Ùˆ 25 Ù…ÙŠØ¬Ø§Ø¨Ø§ÙŠØª.</p>
                                <p id="tool_image_error" class="text-sm text-red-600 mt-1 hidden"></p>
                                @error('image')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                
                                <!-- Hidden field for extracted image URL -->
                                <input type="hidden"
                                       id="extracted-image-url"
                                       name="extracted_image_url"
                                       value="">
                                <input type="hidden"
                                       id="extracted-gallery-images"
                                       name="extracted_gallery_images"
                                       value="">
                            </div>

                            <!-- Amazon URL -->
                            <div>
                                <label for="amazon_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø±Ø§Ø¨Ø· Amazon <span class="text-blue-500 text-sm">(Ø³ÙŠØªÙ… Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª ØªÙ„Ù‚Ø§Ø¦ÙŠØ§Ù‹)</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="url" 
                                           id="amazon_url" 
                                           name="amazon_url" 
                                           value="{{ old('amazon_url') }}"
                                           placeholder="https://amazon.com/..."
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                                    <button type="button" 
                                            id="extract-amazon-data" 
                                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors flex items-center">
                                        <i class="fas fa-download ml-2"></i>
                                        Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
                                    </button>
                                </div>
                                <div id="extraction-status" class="mt-2 text-sm hidden"></div>
                                @error('amazon_url')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Affiliate URL -->
                            <div>
                                <label for="affiliate_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ø±Ø§Ø¨Ø· Ø§Ù„Ø´Ø±Ø§Ø¡ Ø§Ù„Ù…Ø­Ù„ÙŠ
                                </label>
                                <input type="url" 
                                       id="affiliate_url" 
                                       name="affiliate_url" 
                                       value="{{ old('affiliate_url') }}"
                                       placeholder="https://affiliatemarketing.com/..."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                                @error('affiliate_url')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                    ØªØ±ØªÙŠØ¨ Ø§Ù„Ø¹Ø±Ø¶
                                </label>
                                <input type="number" 
                                       id="sort_order" 
                                       name="sort_order" 
                                       min="0"
                                       value="{{ old('sort_order', 0) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                                @error('sort_order')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="mr-3 text-sm font-semibold text-gray-700">ØªÙØ¹ÙŠÙ„ Ø§Ù„Ø£Ø¯Ø§Ø©</span>
                                </label>
                                @error('is_active')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="mt-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Ø§Ù„Ù…Ù…ÙŠØ²Ø§Øª</h3>
                        <div id="features-container">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse mb-3">
                                <input type="text" 
                                       name="features[]" 
                                       placeholder="Ø£Ø¯Ø®Ù„ Ù…ÙŠØ²Ø© Ø¬Ø¯ÙŠØ¯Ø©..."
                                       class="flex-1 feature-input px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
                                       onblur="validateFeature(this)">
                                <button type="button" 
                                        onclick="addFeature()" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-all">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        @error('features')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 rtl:space-x-reverse mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.tools.index') }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all">
                            Ø¥Ù„ØºØ§Ø¡
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all">
                            <i class="fas fa-save ml-2"></i>
                            Ø­ÙØ¸ Ø§Ù„Ø£Ø¯Ø§Ø©
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const newFeature = document.createElement('div');
    newFeature.className = 'flex items-center space-x-2 rtl:space-x-reverse mb-3';
    newFeature.innerHTML = `
        <input type="text" 
               name="features[]" 
               placeholder="Ø£Ø¯Ø®Ù„ Ù…ÙŠØ²Ø© Ø¬Ø¯ÙŠØ¯Ø©..."
               class="flex-1 feature-input px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all"
               onblur="validateFeature(this)">
        <button type="button" 
                onclick="removeFeature(this)" 
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg transition-all">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newFeature);
}

function validateFeature(input) {
    // Remove extra whitespace and ensure it's not empty
    const value = input.value.trim();
    if (value === '') {
        input.value = '';
    } else {
        input.value = value;
    }
}

function removeFeature(button) {
    button.parentElement.remove();
}

// Add existing features if any
@if(old('features'))
    @foreach(old('features') as $feature)
        if ('{{ $feature }}') {
            addFeature();
            const inputs = document.querySelectorAll('input[name="features[]"]');
            inputs[inputs.length - 1].value = '{{ $feature }}';
        }
    @endforeach
@endif

// Amazon Data Extraction
document.getElementById('extract-amazon-data').addEventListener('click', async function() {
    const amazonUrl = document.getElementById('amazon_url').value;
    const statusDiv = document.getElementById('extraction-status');
    const button = this;
    
    if (!amazonUrl) {
        showStatus('ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ø§Ø¨Ø· Amazon Ø£ÙˆÙ„Ø§Ù‹', 'error');
        return;
    }
    
    if (!amazonUrl.includes('amazon.')) {
        showStatus('ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ø§Ø¨Ø· Amazon ØµØ­ÙŠØ­', 'error');
        return;
    }
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>Ø¬Ø§Ø±ÙŠ Ø§Ù„Ø§Ø³ØªØ®Ø±Ø§Ø¬...';
    showStatus('Ø¬Ø§Ø±ÙŠ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ù…Ù† Amazon...', 'loading');
    
    try {
        const response = await fetch('/admin/tools/extract-amazon-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ url: amazonUrl })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fill form fields with extracted data
            if (data.data.name) {
                document.getElementById('name').value = data.data.name;
            }
            if (data.data.description) {
                document.getElementById('description').value = data.data.description;
            }
            if (data.data.price) {
                document.getElementById('price').value = data.data.price;
                // Show conversion info
                if (data.data.original_price_usd) {
                    showPriceConversion(data.data.original_price_usd, data.data.price);
                }
            }
            if (data.data.rating) {
                document.getElementById('rating').value = data.data.rating;
            }
            const galleryImages = Array.isArray(data.data.gallery_images) ? data.data.gallery_images : [];
            const galleryInput = document.getElementById('extracted-gallery-images');
            const primaryInput = document.getElementById('extracted-image-url');

            if (galleryImages.length) {
                galleryInput.value = JSON.stringify(galleryImages);
            } else {
                galleryInput.value = '';
            }

            if (data.data.image) {
                primaryInput.value = data.data.image;
            } else if (galleryImages.length) {
                primaryInput.value = galleryImages[0];
            } else {
                primaryInput.value = '';
            }

            updateImagePreview(primaryInput.value, galleryImages);
            
            showStatus('ØªÙ… Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ø¨Ù†Ø¬Ø§Ø­!', 'success');
        } else {
            showStatus(data.message || 'ÙØ´Ù„ ÙÙŠ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showStatus('Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª', 'error');
    } finally {
        // Reset button state
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-download ml-2"></i>Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª';
    }
});

function showStatus(message, type) {
    const statusDiv = document.getElementById('extraction-status');
    statusDiv.className = 'mt-2 text-sm';
    statusDiv.classList.remove('hidden');
    
    if (type === 'success') {
        statusDiv.classList.add('text-green-600');
    } else if (type === 'error') {
        statusDiv.classList.add('text-red-600');
    } else if (type === 'loading') {
        statusDiv.classList.add('text-blue-600');
    }
    
    statusDiv.textContent = message;
    
    if (type === 'success') {
        setTimeout(() => {
            statusDiv.classList.add('hidden');
        }, 5000);
    }
}

function updateImagePreview(primaryUrl, galleryUrls = []) {
    let previewDiv = document.getElementById('image-preview');
    if (!previewDiv) {
        previewDiv = document.createElement('div');
        previewDiv.id = 'image-preview';
        previewDiv.className = 'mt-4 p-4 border border-gray-300 rounded-lg bg-gray-50';
        document.querySelector('input[name="image"]').parentNode.appendChild(previewDiv);
    }

    const images = [];
    if (Array.isArray(galleryUrls)) {
        galleryUrls.forEach(url => {
            if (url && !images.includes(url)) {
                images.push(url);
            }
        });
    }
    if (primaryUrl) {
        if (images.includes(primaryUrl)) {
            images.splice(images.indexOf(primaryUrl), 1);
        }
        images.unshift(primaryUrl);
    }

    if (!images.length) {
        previewDiv.innerHTML = '';
        previewDiv.classList.add('hidden');
        return;
    }

    previewDiv.classList.remove('hidden');
    const slides = images.map(url => `
        <div class="w-24 h-24 rounded-lg overflow-hidden border flex-shrink-0 bg-white">
            <img src="${url}" alt="Ù…Ø¹Ø§ÙŠÙ†Ø© Ø§Ù„ØµÙˆØ±Ø©" class="w-full h-full object-cover" loading="lazy">
        </div>
    `).join('');

    previewDiv.innerHTML = `
        <h4 class="font-semibold text-gray-700 mb-2">Ù…Ø¹Ø§ÙŠÙ†Ø© Ø§Ù„ØµÙˆØ± Ø§Ù„Ù…Ø³ØªØ®Ø±Ø¬Ø©:</h4>
        <div class="flex gap-3 overflow-x-auto pb-2">${slides}</div>
        <p class="text-sm text-gray-600 mt-2">ÙŠÙ…ÙƒÙ†Ùƒ ØªØ­Ù…ÙŠÙ„ Ø£ÙŠ Ù…Ù† Ù‡Ø°Ù‡ Ø§Ù„ØµÙˆØ± Ø£Ùˆ Ø§Ø®ØªÙŠØ§Ø± ØµÙˆØ±Ø© Ø£Ø®Ø±Ù‰.</p>
    `;
}

function showPriceConversion(usdPrice, jodPrice) {
    // Create price conversion info if it doesn't exist
    let conversionDiv = document.getElementById('price-conversion');
    if (!conversionDiv) {
        conversionDiv = document.createElement('div');
        conversionDiv.id = 'price-conversion';
        conversionDiv.className = 'mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg';
        document.querySelector('input[name="price"]').parentNode.appendChild(conversionDiv);
    }
    
    conversionDiv.innerHTML = `
        <div class="flex items-center text-sm text-blue-700">
            <i class="fas fa-exchange-alt ml-2"></i>
            <span>ØªÙ… ØªØ­ÙˆÙŠÙ„ Ø§Ù„Ø³Ø¹Ø±: ${usdPrice} USD â†’ ${jodPrice} JOD</span>
        </div>
        <div class="text-xs text-blue-600 mt-1">
            Ù…Ø¹Ø¯Ù„ Ø§Ù„ØªØ­ÙˆÙŠÙ„: 1 USD = 0.709 JOD
        </div>
    `;
}
</script>
@endpush
@endsection



