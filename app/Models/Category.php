<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Observers\CategoryObserver;
use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('categories')]
#[Fillable(['parent_id', 'name', 'slug', 'description', 'sort_order', 'is_active', 'seo_title', 'seo_description'])]
#[ObservedBy(CategoryObserver::class)]
class Category extends Model implements HasMedia, HasResolvedMediaUrls
{
    use HasFactory;
    use InteractsWithMedia;
    use SyncsResolvedMediaUrls;

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function homepageCategoryRow(): HasOne
    {
        return $this->hasOne(HomepageCategoryRow::class);
    }

    public function productAttributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class)
            ->withPivot(['is_required', 'is_filterable', 'sort_order']);
    }

    /** Icon URL — the stored column first, Media Library for unsynced rows. */
    public function iconUrl(): ?string
    {
        return $this->icon_url ?? $this->getFirstMedia('icon')?->getUrl();
    }

    public function resolvedMediaUrls(): array
    {
        return [
            'icon_url' => $this->getFirstMedia('icon')?->getUrl(),
        ];
    }

    public function registerMediaCollections(): void
    {
        // Category icons are phase-1 Cloudinary media (docs/cloudinary-media.md).
        $this->addMediaCollection('icon')
            ->singleFile()
            ->useDisk(MediaDisk::forCollection('icon'));
    }
}
