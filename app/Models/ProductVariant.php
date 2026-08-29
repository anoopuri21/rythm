<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('product_variants')]
#[Fillable(['product_id', 'name', 'options', 'sku', 'price_override', 'stock', 'is_active'])]
class ProductVariant extends Model
{
    use HasFactory;

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

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_attribute_value_product_variant',
        );
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Effective price: variant override or parent product price.
     * The parent product is passed explicitly to avoid lazy loading.
     */
    public function effectivePrice(Product $product): string
    {
        return $this->price_override ?? (string) $product->price;
    }
}
