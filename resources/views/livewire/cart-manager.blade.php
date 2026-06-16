<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8">
                <h1 class="mb-2 text-3xl font-bold text-gray-900">Shopping cart</h1>
                <p class="text-gray-600">Manage your selected items</p>
                <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle ml-2 mt-1 text-blue-500"></i>
                        <div class="text-sm text-blue-700">
                            <strong>Note:</strong> The unit price stays fixed when you change the quantity. Only the total amount changes.
                        </div>
                    </div>
                </div>
            </div>

            @if($cartItems->count() > 0)
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-200 bg-gray-50 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Selected products ({{ $uniqueItems }})
                                </h2>
                            </div>

                            <div class="space-y-4 p-6">
                                @foreach($cartItems as $item)
                                    <div class="cart-item rounded-lg border border-gray-200 bg-white p-4 shadow-sm" wire:key="cart-item-{{ $item->id }}">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                            <div class="shrink-0">
                                                <img src="{{ $item->tool->image_url }}"
                                                     alt="{{ $item->tool->name }}"
                                                     class="h-24 w-24 rounded-lg object-cover"
                                                     loading="lazy"
                                                     decoding="async"
                                                     width="96"
                                                     height="96">
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <h3 class="mb-2 text-base font-semibold text-gray-900 sm:text-lg">
                                                    {{ $item->tool->name }}
                                                </h3>

                                                <div class="mb-2 flex items-center">
                                                    <div class="rating-stars flex text-sm">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= round($item->tool->rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="mr-2 text-sm text-gray-600">{{ $item->tool->rating }}</span>
                                                </div>

                                                <div class="text-sm text-gray-600">Unit price</div>
                                                <div class="text-lg font-bold text-orange-600">
                                                    {{ number_format($item->price, 2) }} JOD
                                                </div>
                                                <div class="mt-1 text-sm text-gray-700">
                                                    Line total:
                                                    <span class="font-semibold">{{ number_format($item->total_price, 2) }} JOD</span>
                                                </div>
                                            </div>

                                            <div class="flex w-full flex-col gap-3 sm:w-52 sm:items-end">
                                                <div class="w-full">
                                                    <div class="mb-1 text-xs text-gray-600">Quantity</div>
                                                    <div class="flex items-center overflow-hidden rounded-lg border border-gray-300">
                                                        <button
                                                            type="button"
                                                            class="px-3 py-2 text-gray-700 hover:bg-gray-100"
                                                            wire:click="decrement({{ $item->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="decrement,increment,updateQuantity,removeItem,clearCart"
                                                            aria-label="Decrease quantity"
                                                        >
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input
                                                            type="number"
                                                            min="1"
                                                            max="10"
                                                            class="h-10 w-full border-x border-gray-300 text-center focus:outline-none"
                                                            wire:model.lazy="quantities.{{ $item->id }}"
                                                            wire:change="updateQuantity({{ $item->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="decrement,increment,updateQuantity,removeItem,clearCart"
                                                        >
                                                        <button
                                                            type="button"
                                                            class="px-3 py-2 text-gray-700 hover:bg-gray-100"
                                                            wire:click="increment({{ $item->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="decrement,increment,updateQuantity,removeItem,clearCart"
                                                            aria-label="Increase quantity"
                                                        >
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                @if($item->amazon_url)
                                                    <a href="{{ $item->amazon_url }}"
                                                       target="_blank"
                                                       class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-blue-700 group">
                                                        <i class="fab fa-amazon ml-2 transition-transform duration-300 group-hover:scale-110"></i>
                                                        <span>Continue shopping on Amazon</span>
                                                        <i class="fas fa-external-link-alt mr-2 transition-transform duration-300 group-hover:translate-x-1"></i>
                                                    </a>
                                                @endif

                                                <button
                                                    type="button"
                                                    class="flex w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-all duration-200 hover:border-red-300 hover:bg-red-100 hover:text-red-700"
                                                    wire:click="removeItem({{ $item->id }})"
                                                    wire:confirm="Are you sure you want to remove this product?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="decrement,increment,updateQuantity,removeItem,clearCart"
                                                >
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

                    <div class="lg:col-span-1">
                        <div class="sticky top-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 sm:text-lg">Order summary</h3>

                            <div class="mb-4 space-y-2 sm:mb-6 sm:space-y-3">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Total quantity:</span>
                                    <span class="font-medium">{{ $totalQuantity }} items</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Unique items:</span>
                                    <span class="font-medium">{{ $uniqueItems }} items</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ number_format($total, 2) }} JOD</span>
                                </div>
                                <hr class="my-2 sm:my-3">
                                <div class="flex justify-between text-base font-bold sm:text-lg">
                                    <span>Total:</span>
                                    <span class="text-orange-600">{{ number_format($total, 2) }} JOD</span>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="w-full rounded-lg bg-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition-colors hover:bg-gray-300 sm:px-4 sm:text-sm"
                                wire:click="clearCart"
                                wire:confirm="Are you sure you want to clear the entire cart?"
                                wire:loading.attr="disabled"
                                wire:target="decrement,increment,updateQuantity,removeItem,clearCart"
                            >
                                <i class="fas fa-trash ml-2"></i>
                                Clear cart
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100">
                        <i class="fas fa-shopping-cart text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">Your cart is empty</h3>
                    <p class="mb-6 text-gray-600">You have not added any products yet.</p>
                    <a href="{{ route('tools') }}"
                       class="inline-flex items-center rounded-lg bg-orange-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-orange-600">
                        <i class="fas fa-arrow-right ml-2"></i>
                        Browse products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
