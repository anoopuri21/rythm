<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CategoryService;
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
        // DB-driven category tree for the navbar "Shop by Category" drawer.
        View::composer('components.navbar', function ($view): void {
            $view->with('navCategories', app(CategoryService::class)->tree());
        });
    }
}
