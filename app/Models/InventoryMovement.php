<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'product_variant_id',
    'order_id',
    'actor_id',
    'type',
    'quantity_delta',
    'balance_after',
    'idempotency_key',
    'reference_type',
    'reference_id',
    'reason',
    'metadata',
    'occurred_at',
])]
final class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPE_ORDER_CAPTURE = 'order_capture';

    public const TYPE_ORDER_CANCELLATION = 'order_cancellation';

    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    protected $casts = [
        'quantity_delta' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
