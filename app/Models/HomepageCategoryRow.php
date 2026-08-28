<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\HomepageDataObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('homepage_category_rows')]
#[Fillable(['category_id', 'title', 'product_limit', 'sort_order', 'is_active'])]
#[ObservedBy(HomepageDataObserver::class)]
class HomepageCategoryRow extends Model
{
    use HasFactory;

    protected $casts = [
        'product_limit' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function boundedProductLimit(): int
    {
        return min(8, max(4, $this->product_limit));
    }
}
