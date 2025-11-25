@extends('layouts.app')

@section('title', __('saved.title'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <section class="bg-white py-8 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-2">
                            {{ __('saved.title') }}
                        </h1>
                        <p class="text-gray-600 text-center">
                            {{ __('saved.subtitle') }}
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                @if($savedTools->count() > 0)
                    <!-- Saved Tools Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                        @foreach($savedTools as $savedTool)
                            <div class="tool-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full" data-category="{{ $savedTool->tool->category }}">
                                <!-- Image Section -->
                                <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-3 sm:p-4 flex-shrink-0" style="height: 140px;">
                                    <img src="{{ $savedTool->tool->image_url }}" 
                                         alt="{{ $savedTool->tool->name }}" 
                                         class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                        onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt=@json(__('saved.placeholder_alt'));"
                                    
                                    <!-- Category Badge -->
                                    <div class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded-full" loading="lazy">
                                        {{ $savedTool->tool->category }}
                                    </div>
                                </div>

                                <!-- Content Section -->
                                <div class="p-3 sm:p-4 flex flex-col flex-grow">
                                    <!-- Brand/Title -->
                                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 mb-2 line-clamp-4 leading-tight min-h-[3rem] sm:min-h-[3.5rem]">
                                        {{ $savedTool->tool->name }}
                                    </h3>

                                    <!-- Rating -->
                                    <div class="flex items-center mb-2 sm:mb-3">
                                        <div class="flex rating-stars text-xs">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($savedTool->tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-600 mr-2 font-medium">
                                            {{ $savedTool->tool->rating }}
                                        </span>
                                        <span class="text-xs text-gray-500 hidden sm:inline">
                                            ({{ rand(10, 2000) }})
                                        </span>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-sm sm:text-lg font-bold text-orange-600 mb-2 sm:mb-3">
                                        {{ number_format($savedTool->tool->price, 2) }} {{ __('saved.price_currency') }}
                                    </div>

                                    <!-- Action Buttons - Always at bottom -->
                                    <div class="w-full mt-auto space-y-2">
                                        @if($savedTool->tool->amazon_url)
                                        <a href="{{ $savedTool->tool->amazon_url }}" 
                                           target="_blank"
                                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center justify-center transition-all duration-300 hover:shadow-lg active:scale-95 group">
                                            <i class="fab fa-amazon ml-1 sm:ml-2 group-hover:scale-110 transition-transform duration-300"></i>
                                            <span>{{ __('saved.buttons.amazon') }}</span>
                                            <i class="fas fa-external-link-alt mr-1 sm:mr-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                                        </a>
                                        @endif
                                        
                                        <button class="remove-from-saved-btn w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center justify-center transition-all duration-300 hover:shadow-lg active:scale-95"
                                                data-tool-id="{{ $savedTool->tool->id }}"
                                                data-saved-id="{{ $savedTool->id }}">
                                            <i class="fas fa-trash ml-1 sm:ml-2"></i>
                                            <span class="btn-text">{{ __('saved.buttons.remove') }}</span>
                                            <i class="fas fa-spinner fa-spin ml-1 sm:ml-2 hidden loading-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-bookmark text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('saved.states.empty_title') }}</h3>
                        <p class="text-gray-600 mb-6">{{ __('saved.states.empty_description') }}</p>
                        <a href="{{ route('tools') }}" 
                           class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-shopping-bag ml-2"></i>
                            {{ __('saved.buttons.browse_tools') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .tool-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        max-width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .tool-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-4px);
    }
    
    .line-clamp-4 {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .rating-stars {
        display: flex;
        gap: 1px;
    }
    
    .rating-stars i {
        font-size: 0.75rem;
    }
    
    /* Notifications counter styling */
    #notifications-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ef4444;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        border-radius: 50%;
        min-width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        animation: pulse-red 2s infinite;
    }
    
    #notifications-count.hidden {
        display: none !important;
    }
    
    /* Pulse animation for red notifications */
    @keyframes pulse-red {
        0% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.5);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }
    }
    
    /* تحسينات للهواتف */
    @media (max-width: 640px) {
        .tool-card {
            margin-bottom: 0.75rem;
            min-height: 280px;
        }
        
        .tool-card .p-3 {
            padding: 0.75rem;
        }
        
        .tool-card h3 {
            font-size: 0.75rem;
            line-height: 1.2rem;
            min-height: 3rem;
        }
        
        .tool-card .text-sm {
            font-size: 0.875rem;
        }
        
        .tool-card .text-lg {
            font-size: 1rem;
        }
        
        .tool-card button {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .tool-card .rating-stars {
            font-size: 0.625rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load saved count on page load
    if (window.loadSavedCount) {
        window.loadSavedCount();
    }
    
    
    // تحديث فوري للإشعارات عند وصول إشعار جديد - استخدام NotificationManager المركزي
    function updateNotificationCountOnly() {
        if (window.NotificationManager) {
            window.NotificationManager.getNotifications((data, error) => {
                if (error) {
                    console.error('Error updating notification count:', error);
                    return;
                }
                
                const countElement = document.getElementById('notifications-count');
                if (countElement) {
                    const oldCount = parseInt(countElement.textContent) || 0;
                    const newCount = data?.unreadCount || 0;
                    
                    if (newCount !== oldCount) {
                        countElement.textContent = newCount;
                        
                        if (newCount > 0) {
                            countElement.classList.remove('hidden');
                            
                            // إضافة تأثير بصري عند ظهور إشعارات جديدة
                            if (newCount > oldCount) {
                                countElement.classList.add('animate-bounce');
                                setTimeout(() => {
                                    countElement.classList.remove('animate-bounce');
                                }, 2000);
                            }
                        } else {
                            countElement.classList.add('hidden');
                        }
                    }
                }
            });
        } else {
            console.warn('NotificationManager not available, falling back to direct fetch');
            // Fallback to direct fetch
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/notifications/api', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    const countElement = document.getElementById('notifications-count');
                    if (countElement) {
                        const oldCount = parseInt(countElement.textContent) || 0;
                        const newCount = data.unreadCount || 0;
                        
                        if (newCount !== oldCount) {
                            countElement.textContent = newCount;
                            
                            if (newCount > 0) {
                                countElement.classList.remove('hidden');
                                
                                if (newCount > oldCount) {
                                    countElement.classList.add('animate-bounce');
                                    setTimeout(() => {
                                        countElement.classList.remove('animate-bounce');
                                    }, 2000);
                                }
                            } else {
                                countElement.classList.add('hidden');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating notification count:', error);
                });
        }
    }
    
    // Remove from saved functionality
    document.querySelectorAll('.remove-from-saved-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const toolId = this.dataset.toolId;
            const savedId = this.dataset.savedId;
            
            // Show loading state
            this.disabled = true;
            this.querySelector('.btn-text').textContent = @json(__('saved.messages.removing'));
            this.querySelector('.loading-icon').classList.remove('hidden');
            
            // Remove from saved
            fetch('/saved/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    tool_id: toolId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the card from DOM
                    this.closest('.tool-card').remove();
                    
                    // Show success message
                    showToast(@json(__('saved.messages.remove_success')), 'success');
                    
                    // Check if no more items
                    const remainingCards = document.querySelectorAll('.tool-card');
                    if (remainingCards.length === 0) {
                        location.reload();
                    }
                } else {
                    throw new Error(data.message || @json(__('saved.messages.remove_error')));
                }
            })
            .catch(error => {
                console.error('Error removing from saved:', error);
                this.disabled = false;
                this.querySelector('.btn-text').textContent = @json(__('saved.buttons.remove'));
                this.querySelector('.loading-icon').classList.add('hidden');
                showToast(@json(__('saved.messages.remove_error_saved')), 'error');
            });
        });
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
    
    // Set color based on type
    if (type === 'success') {
        toast.classList.add('bg-green-500');
    } else if (type === 'error') {
        toast.classList.add('bg-red-500');
    } else if (type === 'warning') {
        toast.classList.add('bg-yellow-500');
    } else {
        toast.classList.add('bg-blue-500');
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

</script>
@endpush



