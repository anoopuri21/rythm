<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'source_product_id',
    'target_product_id',
    'rule_type',
    'priority',
    'is_active',
    'starts_at',
    'ends_at',
])]
final class ProductMerchandisingRule extends Model
{
    use HasFactory;

    public const TYPE_RELATED = 'related';

    public const TYPE_COMPLEMENTARY = 'complementary';

    public const TYPE_FREQUENTLY_BOUGHT_TOGETHER = 'frequently_bought_together';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_RELATED,
        self::TYPE_COMPLEMENTARY,
        self::TYPE_FREQUENTLY_BOUGHT_TOGETHER,
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductMerchandisingRule $rule): void {
            if ($rule->source_product_id === $rule->target_product_id) {
                throw new \DomainException('A product cannot recommend itself.');
            }

            if (! in_array($rule->rule_type, self::TYPES, true)) {
                throw new \DomainException('Unsupported merchandising rule type.');
            }
        });
    }

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function scopeActiveNow(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $active): Builder => $active
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $active): Builder => $active
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $now));
    }
}
