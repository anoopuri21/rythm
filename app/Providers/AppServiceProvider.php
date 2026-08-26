<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mail\OrderConfirmationMail;
use App\Models\AdminAuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\HomepageSection;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Refund;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\User;
use App\Observers\AdminAuditableObserver;
use App\Policies\AuditPolicy;
use App\Policies\CataloguePolicy;
use App\Policies\ContentPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InteractionPolicy;
use App\Policies\MarketingPolicy;
use App\Policies\OrderPolicy;
use App\Services\CartService;
use App\Services\CategoryService;
use App\Support\AdminAccess;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
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
        foreach ([
            AdminAuditLog::class => AuditPolicy::class,
            Product::class => CataloguePolicy::class,
            Category::class => CataloguePolicy::class,
            Brand::class => CataloguePolicy::class,
            Order::class => OrderPolicy::class,
            User::class => CustomerPolicy::class,
            Review::class => InteractionPolicy::class,
            ProductQuestion::class => InteractionPolicy::class,
            ContactMessage::class => InteractionPolicy::class,
            Coupon::class => MarketingPolicy::class,
            NewsletterSubscriber::class => MarketingPolicy::class,
            Page::class => ContentPolicy::class,
            Faq::class => ContentPolicy::class,
            HeroSlide::class => ContentPolicy::class,
            HomepageBlock::class => ContentPolicy::class,
            HomepageSection::class => ContentPolicy::class,
        ] as $model => $policy) {
            Gate::policy($model, $policy);
        }

        foreach ([
            Product::class,
            Order::class,
            Refund::class,
            Coupon::class,
            SiteSetting::class,
            User::class,
            Review::class,
            ProductQuestion::class,
            ContactMessage::class,
        ] as $auditedModel) {
            $auditedModel::observe(AdminAuditableObserver::class);
        }

        Gate::before(function (User $user, string $ability, array $arguments): ?bool {
            $target = $arguments[0] ?? null;
            $model = is_string($target) ? $target : (is_object($target) ? $target::class : null);
            if ($model === null) {
                return null;
            }

            $permission = AdminAccess::permissionForModelAbility($model, $ability);

            return $permission === null ? null : $user->hasAdminPermission($permission);
        });

        // Performance guard: surface any accidental lazy loading in dev/tests.
        if (! $this->app->isProduction()) {
            Model::preventLazyLoading();
        }

        // DB-driven category tree for the navbar "Shop by Category" drawer.
        View::composer('components.navbar', function ($view): void {
            $view->with('navCategories', app(CategoryService::class)->tree());
        });

        // Merge the guest session cart into the user's cart on login.
        Event::listen(
            Login::class,
            function (Login $event): void {
                $sessionId = session()->get('rythme.cart.session');

                if ($sessionId !== null) {
                    app(CartService::class)->mergeGuestCart($sessionId);
                }
            },
        );

        // Route non-blocking email jobs to the 'emails' queue.
        Queue::route(OrderConfirmationMail::class, 'emails');
    }
}
