<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BrandObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('brands')]
#[Fillable(['name', 'slug', 'description', 'sort_order', 'is_active'])]
#[ObservedBy(BrandObserver::class)]
class Brand extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }
}
