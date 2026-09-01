<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_id',
    'product_variant_id',
    'target_key',
    'consent_at',
    'notified_at',
    'cancelled_at',
])]
final class BackInStockSubscription extends Model
{
    protected $casts = [
        'consent_at' => 'datetime',
        'notified_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('notified_at')->whereNull('cancelled_at');
    }

    public static function targetKey(int $productId, ?int $variantId = null): string
    {
        return 'product:'.$productId.':variant:'.($variantId ?? 0);
    }
}
