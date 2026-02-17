<?php

use App\Http\Controllers\Thedolci\AdminDashboardController;
use App\Http\Controllers\Thedolci\AdminOrderController;
use App\Http\Controllers\Thedolci\AdminProductController;
use App\Http\Controllers\Thedolci\AdminReviewController;
use App\Http\Controllers\Thedolci\AdminStorefrontController;
use App\Http\Controllers\Thedolci\CartController;
use App\Http\Controllers\Thedolci\CheckoutController;
use App\Http\Controllers\Thedolci\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('thedolci.shop');
Route::get('/shop/{slug}', [StorefrontController::class, 'product'])->name('thedolci.product');
Route::get('/collections/seasonal', [StorefrontController::class, 'seasonal'])->name('thedolci.seasonal');
Route::get('/about', [StorefrontController::class, 'about'])
    ->middleware('admin')
    ->name('about');
Route::get('/reviews', [StorefrontController::class, 'reviews'])->name('thedolci.reviews');
Route::get('/loyalty', [StorefrontController::class, 'loyalty'])
    ->middleware(['auth', 'admin'])
    ->name('thedolci.loyalty');
Route::get('/faq', [StorefrontController::class, 'faq'])->name('thedolci.faq');
Route::get('/contact', [StorefrontController::class, 'contact'])->name('contact');
Route::post('/subscribe', [StorefrontController::class, 'subscribe'])->name('thedolci.subscribe');

Route::get('/cart', [CartController::class, 'index'])->name('thedolci.cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('thedolci.cart.add');
Route::post('/cart/{key}', [CartController::class, 'post'])->name('thedolci.cart.post');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('thedolci.cart.update');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('thedolci.cart.remove');
Route::get('/cart/coupon', fn () => redirect()->route('thedolci.cart'));
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('thedolci.cart.coupon');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('thedolci.checkout');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('thedolci.checkout.place');
Route::get('/order/success/{orderNumber?}', [CheckoutController::class, 'success'])->name('thedolci.order.success');
Route::match(['GET', 'POST'], '/track-order', [CheckoutController::class, 'track'])->name('thedolci.track-order');

Route::post('/api/coupons/validate', [CheckoutController::class, 'validateCoupon'])->name('thedolci.api.coupon');
Route::get('/api/delivery-slots', [CheckoutController::class, 'deliverySlots'])->name('thedolci.api.delivery-slots');
Route::get('/api/instagram-feed', [StorefrontController::class, 'instagramFeed'])->name('thedolci.api.instagram');
Route::get('/api/reviews/featured', [StorefrontController::class, 'featuredReviews'])->name('thedolci.api.reviews');

Route::prefix('admin/thedolci')->name('thedolci.admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/storefront', [AdminStorefrontController::class, 'edit'])->name('storefront.edit');
    Route::put('/storefront', [AdminStorefrontController::class, 'update'])->name('storefront.update');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/seed-defaults', [AdminProductController::class, 'seedDefaults'])->name('products.seed-defaults');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/create', [AdminReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [AdminReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [AdminReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
});
