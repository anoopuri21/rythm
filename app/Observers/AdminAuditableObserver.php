<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\HomepageCategoryRow;
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
use App\Services\AdminAuditService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class AdminAuditableObserver
{
    /** @var array<class-string, list<string>> */
    private const TRACKED = [
        Product::class => ['category_id', 'brand_id', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'is_trending'],
        Order::class => ['status', 'payment_status', 'shipping_fee', 'tax', 'total'],
        Refund::class => ['amount', 'currency', 'status', 'gateway_refund_id'],
        Coupon::class => ['type', 'value', 'min_order', 'max_discount', 'starts_at', 'expires_at', 'max_uses', 'is_active'],
        SiteSetting::class => ['value'],
        User::class => ['role'],
        Review::class => ['status', 'is_approved'],
        ProductQuestion::class => ['status', 'answered_at'],
        ContactMessage::class => ['status'],
        Category::class => ['name', 'slug', 'parent_id', 'is_active', 'sort_order'],
        Brand::class => ['name', 'slug', 'is_active'],
        Page::class => ['title', 'slug', 'is_active', 'published_at'],
        Faq::class => ['question', 'is_active', 'sort_order'],
        HeroSlide::class => ['title', 'is_active', 'sort_order'],
        HomepageBlock::class => ['title', 'type', 'is_active', 'sort_order'],
        HomepageCategoryRow::class => ['title', 'is_active', 'sort_order'],
        HomepageSection::class => ['title', 'is_active', 'sort_order'],
        NewsletterSubscriber::class => ['status'],
    ];

    public function created(Model $model): void
    {
        $actor = $this->actor();
        $tracked = self::TRACKED[$model::class] ?? [];
        if ($actor === null || $tracked === []) {
            return;
        }

        $after = Arr::only($model->getAttributes(), $tracked);
        if ($model instanceof SiteSetting && $this->sensitiveSetting($model->key)) {
            $after['value'] = '[REDACTED]';
        }

        app(AdminAuditService::class)->record(
            $actor,
            Str::snake(class_basename($model)).'.created',
            $model,
            [],
            $after,
            request()->input('audit_reason'),
        );
    }

    public function updated(Model $model): void
    {
        $actor = $this->actor();
        if ($actor === null) {
            return;
        }

        $tracked = self::TRACKED[$model::class] ?? [];
        $changed = array_values(array_intersect($tracked, array_keys($model->getChanges())));
        if ($changed === []) {
            return;
        }

        $before = Arr::only($model->getOriginal(), $changed);
        $after = Arr::only($model->getAttributes(), $changed);
        if ($model instanceof SiteSetting && $this->sensitiveSetting($model->key)) {
            $before['value'] = '[REDACTED]';
            $after['value'] = '[REDACTED]';
        }

        app(AdminAuditService::class)->record(
            $actor,
            Str::snake(class_basename($model)).'.updated',
            $model,
            $before,
            $after,
            request()->input('audit_reason'),
        );
    }

    public function deleted(Model $model): void
    {
        $actor = $this->actor();
        $tracked = self::TRACKED[$model::class] ?? [];
        if ($actor === null || $tracked === []) {
            return;
        }

        $before = Arr::only($model->getOriginal(), $tracked);
        if ($model instanceof SiteSetting && $this->sensitiveSetting($model->key)) {
            $before['value'] = '[REDACTED]';
        }

        app(AdminAuditService::class)->record(
            $actor,
            Str::snake(class_basename($model)).'.deleted',
            $model,
            $before,
            [],
            request()->input('audit_reason'),
        );
    }

    private function actor(): ?User
    {
        $actor = auth()->user();

        return $actor instanceof User && $actor->canAccessPanel(Filament::getPanel('admin')) ? $actor : null;
    }

    private function sensitiveSetting(string $key): bool
    {
        return Str::contains(Str::lower($key), ['secret', 'password', 'token', 'signature', 'credential', 'api_key']);
    }
}
