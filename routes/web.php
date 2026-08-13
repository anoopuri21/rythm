<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Contact form submission (page itself is dynamic — see catch-all below)
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

    // Password reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.store');
});

Route::post('/logout', LogoutController::class)->name('logout');

// Email verification (Laravel built-in)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', \App\Http\Controllers\Auth\VerificationNoticeController::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request): \Illuminate\Http\RedirectResponse {
        $request->fulfill();

        return redirect()->route('account.index')->with('status', 'Email verified!');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Checkout — LOGIN FORCED (guest cart auto-merges on login)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])
        ->middleware('signed')
        ->name('checkout.success');

    // Account dashboard — profile, password, addresses, orders
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::patch('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
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

// Order detail + tracking — owner, signed link, or guest lookup result
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

// Guest order lookup (no login needed)
Route::get('/track-order', [OrderController::class, 'lookup'])->name('orders.lookup');
Route::post('/track-order', [OrderController::class, 'lookupPost'])
    ->middleware('throttle:5,1')
    ->name('orders.lookup.post');

// SEO: sitemap + robots (before catch-all)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Dynamic pages (admin-managed URL slugs) — catch-all, must be LAST.
// Reserved route slugs are blocked at the admin level (Page::RESERVED_SLUGS).
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');
