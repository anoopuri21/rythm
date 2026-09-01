<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('order_items')]
#[Fillable(['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'hsn_code_snapshot', 'tax_classification_snapshot', 'tax_rate_snapshot', 'taxable_amount_snapshot', 'tax_amount_snapshot', 'tax_calculation_enabled_snapshot', 'tax_destination_region_snapshot', 'options', 'unit_price', 'qty', 'total'])]
class OrderItem extends Model
{
    use HasFactory;

    

    protected $casts = [
        'options' => 'array',
        'unit_price' => 'decimal:2',
        'tax_rate_snapshot' => 'decimal:4',
        'taxable_amount_snapshot' => 'decimal:2',
        'tax_amount_snapshot' => 'decimal:2',
        'tax_calculation_enabled_snapshot' => 'boolean',
        'qty' => 'integer',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(function (OrderItem $item): void {
            $snapshots = [
                'hsn_code_snapshot',
                'tax_classification_snapshot',
                'tax_rate_snapshot',
                'taxable_amount_snapshot',
                'tax_amount_snapshot',
                'tax_calculation_enabled_snapshot',
                'tax_destination_region_snapshot',
            ];

            if ($item->isDirty($snapshots)) {
                throw new \DomainException('Order-line tax snapshots are immutable after checkout.');
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnRequestItems(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }
}
