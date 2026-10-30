<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\CatalogController; // New controller
use Illuminate\Support\Facades\Route;

// Home route
Route::get('/', [ProductController::class, 'index'])->name('home');
// Catalog route
Route::get('/products', [CatalogController::class, 'index'])->name('catalog.index');
// New public pages routes
Route::get('/about', function () { return view('pages.about'); })->name('pages.about');
Route::get('/contact', [ContactController::class, 'create'])->name('pages.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1')->name('newsletter.store');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/legal', function () { return view('pages.legal'); })->name('pages.legal');
Route::post('/language', [LanguageController::class, 'update'])->name('language.update');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
// Product detail
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::put('/dashboard', [CustomerDashboardController::class, 'update'])->name('dashboard.update');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->middleware('throttle:30,1')->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->middleware('throttle:30,1')->name('wishlist.destroy');
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->middleware('throttle:5,1')->name('products.reviews.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->middleware('throttle:30,1')->name('add');
    Route::put('/quantity/{product}', [CartController::class, 'updateQuantity'])->middleware('throttle:30,1')->name('update_quantity');
    Route::post('/remove/{product}', [CartController::class, 'remove'])->middleware('throttle:30,1')->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->middleware('throttle:10,1')->name('clear');
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:10,1')->name('apply_coupon');
    Route::delete('/remove-coupon', [CartController::class, 'removeCoupon'])->name('remove_coupon');
});

// Checkout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
    Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
});

// Admin routes
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('products', AdminProductController::class)->only(["index","create","store","edit","update","destroy"]);
    Route::resource('categories', AdminCategoryController::class)->only(["index","create","store","edit","update"]);
    
    // Orders management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    
    // Coupons management (NOUVEAU)
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['show', 'edit', 'update']);
    
    // Settings routes
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-mail', [App\Http\Controllers\Admin\SettingController::class, 'testMail'])->name('settings.test-mail');
    Route::get('/administrators', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('admin-users.index');
    Route::post('/administrators', [App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('admin-users.store');
    Route::get('/messages', [App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('messages.index');
    Route::delete('/messages/{contactMessage}', [App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/reviews', [App\Http\Controllers\Admin\ProductReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}', [App\Http\Controllers\Admin\ProductReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ProductReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/newsletter', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('newsletter.index');
    Route::delete('/newsletter/{subscriber}', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('newsletter.destroy');
});

require __DIR__.'/auth.php';
