@extends('layouts.app')

@section('title', 'Shopping cart - Wasfah')

@push('styles')
<style>
    .cart-item {
        transition: all 0.3s ease;
    }
    .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* ØªØ­Ø³ÙŠÙ†Ø§Øª Ù„Ù„Ù‡ÙˆØ§ØªÙ */
    @media (max-width: 640px) {
        .cart-item {
            margin-bottom: 1rem;
        }
        
        .cart-item .p-3 {
            padding: 1rem;
        }
        
        .cart-item h3 {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        .cart-item .text-lg {
            font-size: 1rem;
        }
        
        .cart-item .text-xs {
            font-size: 0.75rem;
        }
        
        .cart-item button {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .cart-item .rating-stars {
            font-size: 0.625rem;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Shopping cart</h1>
                <p class="text-gray-600">Manage your selected items</p>
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 ml-2"></i>
                        <div class="text-sm text-blue-700">
                            <strong>Note:</strong> The unit price stays fixed when you change the quantity. Only the total amount changes.
                        </div>
                    </div>
                </div>
            </div>

            @if($cartItems->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200 bg-gray-50">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Selected products ({{ $cartItems->count() }})
                                </h2>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                @foreach($cartItems as $item)
                                    <div class="cart-item bg-white border border-gray-200 rounded-lg p-3 sm:p-6 shadow-sm" data-cart-id="{{ $item->id }}">
                                        <!-- Mobile Layout -->
                                        <div class="block sm:hidden">
                                            <!-- Product Image -->
                                            <div class="flex justify-center mb-3">
                                                <img src="{{ $item->tool->image_url }}" 
                                                     alt="{{ $item->tool->name }}" 
                                                     class="w-24 h-24 object-cover rounded-lg" loading="lazy" decoding="async" width="96" height="96">
                                            </div>
                                            
                                            <!-- Product Details -->
                                            <div class="text-center mb-4">
                                                <h3 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2">
                                                    {{ $item->tool->name }}
                                                </h3>
                                                
                                                <div class="flex items-center justify-center mb-2">
                                                    <div class="flex rating-stars text-xs">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= round($item->tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="text-xs text-gray-600 mr-2">
                                                        {{ $item->tool->rating }}
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="text-xs text-gray-600 mb-1">Unit price:</div>
                                                    <div class="text-lg font-bold text-orange-600" data-unit-price="{{ $item->price }}">
                                                        {{ number_format($item->price, 2) }} JOD
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="space-y-2">
                                                @if($item->amazon_url)
                                                <a href="{{ $item->amazon_url }}" 
                                                   target="_blank"
                                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-all duration-200 flex items-center justify-center group">
                                                    <i class="fab fa-amazon ml-2 group-hover:scale-110 transition-transform duration-300"></i>
                                                    <span>Continue shopping on Amazon</span>
                                                    <i class="fas fa-external-link-alt mr-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                                                </a>
                                                @endif
                                                
                                                <button class="remove-btn w-full bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-xs font-medium px-3 py-2 rounded-lg border border-red-200 hover:border-red-300 transition-all duration-200 flex items-center justify-center" 
                                                        data-cart-id="{{ $item->id }}"
                                                        title="Remove product from the cart">
                                                    <i class="fas fa-trash ml-2"></i>
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Desktop Layout -->
                                        <div class="hidden sm:flex items-start space-x-4 space-x-reverse">
                                            <!-- Product Image -->
                                            <div class="flex-shrink-0">
                                                <img src="{{ $item->tool->image_url }}" 
                                                     alt="{{ $item->tool->name }}" 
                                                     class="w-20 h-20 object-cover rounded-lg" loading="lazy" decoding="async" width="80" height="80">
                                            </div>
                                            
                                            <!-- Product Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                    {{ $item->tool->name }}
                                                </h3>
                                                </div>
                                                
                                                <div class="flex items-center mb-2">
                                                    <div class="flex rating-stars text-sm">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= round($item->tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="text-sm text-gray-600 mr-2">
                                                        {{ $item->tool->rating }}
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="text-sm text-gray-600 mb-1">Unit price:</div>
                                                    <div class="text-lg font-bold text-orange-600" data-unit-price="{{ $item->price }}">
                                                        {{ number_format($item->price, 2) }} JOD
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex flex-col items-end space-y-3">
                                                
                                                @if($item->amazon_url)
                                                <a href="{{ $item->amazon_url }}" 
                                                   target="_blank"
                                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition-all duration-200 flex items-center justify-center group">
                                                    <i class="fab fa-amazon ml-2 group-hover:scale-110 transition-transform duration-300"></i>
                                                    <span>Continue shopping on Amazon</span>
                                                    <i class="fas fa-external-link-alt mr-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                                                </a>
                                                @endif
                                                
                                                <button class="remove-btn bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-sm font-medium px-3 py-2 rounded-lg border border-red-200 hover:border-red-300 transition-all duration-200 flex items-center" 
                                                        data-cart-id="{{ $item->id }}"
                                                        title="Remove product from the cart">
                                                    <i class="fas fa-trash ml-2"></i>
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 sticky top-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Order summary</h3>
                            
                            <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Total quantity:</span>
                                    <span class="font-medium">{{ $cartItems->sum('quantity') }} items</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Unique items:</span>
                                    <span class="font-medium">{{ $cartItems->count() }} items</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ number_format($total, 2) }} JOD</span>
                                </div>
                                <hr class="my-2 sm:my-3">
                                <div class="flex justify-between text-base sm:text-lg font-bold">
                                    <span>Total:</span>
                                    <span class="text-orange-600">{{ number_format($total, 2) }} JOD</span>
                                </div>
                            </div>
                            
                            <div class="space-y-2 sm:space-y-3">
                                
                                <button id="clear-cart" 
                                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-3 sm:px-4 rounded-lg transition-colors text-xs sm:text-sm">
                                    <i class="fas fa-trash ml-1 sm:ml-2"></i>
                                    Clear cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
                    <p class="text-gray-600 mb-6">You have not added any products yet.</p>
                    <a href="{{ route('tools') }}" 
                       class="inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        <i class="fas fa-arrow-right ml-2"></i>
                        Browse products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Remove item
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.cartId;
            removeItem(cartId);
        });
    });
    
    // Clear cart
    document.getElementById('clear-cart')?.addEventListener('click', function() {
        console.log('Clear cart button clicked'); // Debug log
        if (confirm('Are you sure you want to clear the entire cart?')) {
            console.log('User confirmed clear cart'); // Debug log
            clearCart();
        } else {
            console.log('User cancelled clear cart'); // Debug log
        }
    });
    
    
    
    
    
    // Functions
    
    function removeItem(cartId) {
        fetch(`/cart/${cartId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-cart-id="${cartId}"]`).remove();
                updateCartUI(data);
                
                // Check if cart is empty
                if (data.cart_count === 0) {
                    location.reload();
                }
            }
        });
    }
    
    function clearCart() {
        console.log('Starting clear cart function'); // Debug log
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found');
            showToast('Security error', 'error');
            return;
        }
        
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Clear cart response:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Clear cart data:', data); // Debug log
            if (data.success) {
                showToast('Cart cleared successfully', 'success');
                // Update cart count
                if (window.loadCartCount) {
                    window.loadCartCount();
                }
                // Reload the page
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast(data.message || 'An error occurred while clearing the cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing cart:', error);
            showToast('An error occurred while clearing the cart', 'error');
        });
    }
    
    function updateCartUI(data) {
        // Update cart count in header
        if (window.loadCartCount) {
            window.loadCartCount();
        }
        
        // Update totals if provided
        if (data.cart_total) {
            // Update total display in order summary (but NOT unit prices)
            const totalEls = document.querySelectorAll('.text-orange-600');
            totalEls.forEach(el => {
                // Only update if it's NOT a unit price (unit prices have data-unit-price attribute)
                if (el.textContent.includes('JOD') && !el.hasAttribute('data-unit-price')) {
                    el.textContent = parseFloat(data.cart_total).toFixed(2) + ' JOD';
                }
            });
        }
        
        // Update item count in header
        if (data.cart_count !== undefined) {
            const itemCountEls = document.querySelectorAll('.text-lg.font-bold.text-gray-900');
            itemCountEls.forEach(el => {
                if (el.textContent.includes('items')) {
                    el.textContent = data.cart_count + ' items';
                }
            });
        }
        
        // Update product count in header
        const productCountEl = document.querySelector('h2.text-lg.font-semibold.text-gray-900');
        if (productCountEl && data.cart_count !== undefined) {
            productCountEl.textContent = `Selected products (${data.cart_count})`;
        }
        
        // Show success message
        if (data.message) {
            showToast(data.message, 'success');
        }
    }
    
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
        } else {
            toast.classList.add('bg-blue-500');
        }
        
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
});
</script>
@endpush



