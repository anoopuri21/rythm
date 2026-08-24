<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CartService;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CategoryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Performance guard: surface any accidental lazy loading in dev/tests.
        if (! $this->app->isProduction()) {
            Model::preventLazyLoading();
        }

        // DB-driven category tree for the navbar "Shop by Category" drawer.
        View::composer('components.navbar', function ($view): void {
            $view->with('navCategories', app(CategoryService::class)->tree());
        });

        // Merge the guest session cart into the user's cart on login.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event): void {
                $sessionId = session()->get('rythme.cart.session');

                if ($sessionId !== null) {
                    app(CartService::class)->mergeGuestCart($sessionId);
                }
            },
        );

        // Route non-blocking email jobs to the 'emails' queue.
        \Illuminate\Support\Facades\Queue::route(\App\Mail\OrderConfirmationMail::class, 'emails');
    }
}
