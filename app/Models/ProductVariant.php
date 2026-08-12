<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'options',
        'sku',
        'price_override',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'price_override' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective price: variant override or parent product price. */
    public function effectivePrice(): string
    {
        return $this->price_override ?? $this->product->price;
    }
}
