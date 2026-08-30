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
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Shipment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AdminAuditService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class AdminAuditableObserver
{
    /** @var list<string> */
    private const HASHED_FIELDS = ['description', 'short_description', 'content', 'answer', 'copy', 'email', 'customer_guidance'];

    /** @var array<class-string, list<string>> */
    private const TRACKED = [
        Product::class => ['name', 'slug', 'sku', 'hsn_code', 'tax_classification', 'tax_rate', 'category_id', 'brand_id', 'price', 'compare_at_price', 'stock', 'low_stock_threshold', 'is_active', 'is_featured', 'is_trending', 'short_description', 'description'],
        Category::class => ['parent_id', 'name', 'slug', 'sort_order', 'is_active', 'description'],
        Brand::class => ['name', 'slug', 'sort_order', 'is_active', 'description'],
        Page::class => ['slug', 'title', 'template', 'content', 'sort_order', 'is_active'],
        Faq::class => ['question', 'answer', 'sort_order', 'is_active'],
        HeroSlide::class => ['eyebrow', 'title', 'accent', 'copy', 'cta_label', 'cta_href', 'sort_order', 'is_active'],
        HomepageBlock::class => ['section_key', 'title', 'subtitle', 'content', 'sort_order', 'is_active'],
        HomepageCategoryRow::class => ['title', 'subtitle', 'category_ids', 'sort_order', 'is_active'],
        HomepageSection::class => ['section_key', 'kicker', 'title', 'title_accent', 'content', 'sort_order', 'is_active'],
        NewsletterSubscriber::class => ['email', 'status'],
        Order::class => ['status', 'payment_status', 'shipping_fee', 'tax', 'total'],
        Refund::class => ['amount', 'currency', 'status', 'gateway_refund_id'],
        ReturnReason::class => ['name', 'customer_guidance', 'is_active', 'sort_order'],
        ReturnRequest::class => ['status', 'refund_id', 'approved_at', 'received_at', 'closed_at'],
        Shipment::class => ['order_id', 'idempotency_key', 'status', 'carrier', 'awb', 'tracking_url', 'note', 'created_by', 'dispatched_at', 'delivered_at'],
        Coupon::class => ['type', 'value', 'min_order', 'max_discount', 'starts_at', 'expires_at', 'max_uses', 'is_active'],
        SiteSetting::class => ['value'],
        User::class => ['role'],
        Review::class => ['status', 'is_approved'],
        ProductQuestion::class => ['status', 'answered_at'],
        ContactMessage::class => ['status'],
    ];

    public function created(Model $model): void
    {
        $actor = $this->actor();
        $tracked = self::TRACKED[$model::class] ?? [];
        if ($actor === null || $tracked === []) {
            return;
        }

        $after = $this->protectedValues($model, Arr::only($model->getAttributes(), $tracked));

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

        $before = $this->protectedValues($model, Arr::only($model->getOriginal(), $changed));
        $after = $this->protectedValues($model, Arr::only($model->getAttributes(), $changed));

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

        app(AdminAuditService::class)->record(
            $actor,
            Str::snake(class_basename($model)).'.deleted',
            $model,
            $this->protectedValues($model, Arr::only($model->getOriginal(), $tracked)),
            [],
            request()->input('audit_reason'),
        );
    }

    /** @param array<string, mixed> $values
     *  @return array<string, mixed>
     */
    private function protectedValues(Model $model, array $values): array
    {
        foreach ($values as $field => $value) {
            if (in_array($field, self::HASHED_FIELDS, true) && $value !== null) {
                $serialized = is_scalar($value)
                    ? (string) $value
                    : (string) json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
                $values[$field] = 'sha256:'.hash('sha256', $serialized);
            }
        }

        if ($model instanceof SiteSetting && $this->sensitiveSetting($model->key) && array_key_exists('value', $values)) {
            $values['value'] = '[REDACTED]';
        }

        return $values;
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
