<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Static pages
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// Wishlist — auth only
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
});

// Cart (guest + user)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// Storefront auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', LogoutController::class)->name('logout');

// Checkout — LOGIN FORCED (guest cart auto-merges on login)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])
        ->middleware('signed')
        ->name('checkout.success');
});

// Payment callbacks — Razorpay posts here (CSRF excluded in bootstrap/app.php)
Route::post('/payment/razorpay/callback', [RazorpayController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('payment.razorpay.callback');
Route::post('/payment/razorpay/webhook', [RazorpayController::class, 'webhook'])
    ->middleware('throttle:30,1')
    ->name('payment.razorpay.webhook');

Route::post('/newsletter', NewsletterSubscriptionController::class)
    ->middleware('throttle:6,1')
    ->name('newsletter.store');
