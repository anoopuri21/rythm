<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('order_items')]
#[Fillable(['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'options', 'unit_price', 'qty', 'total'])]
class OrderItem extends Model
{
    use HasFactory;

    

    protected $casts = [
        'options' => 'array',
        'unit_price' => 'decimal:2',
        'qty' => 'integer',
        'total' => 'decimal:2',
    ];

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
}
