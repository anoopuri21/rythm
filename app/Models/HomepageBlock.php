<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Observers\HomepageDataObserver;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('homepage_blocks')]
#[ObservedBy(HomepageDataObserver::class)]
#[Fillable(['section_key', 'title', 'subtitle', 'content', 'sort_order', 'is_active'])]
class HomepageBlock extends Model implements HasMedia, HasResolvedMediaUrls
{
    use HasFactory;
    use InteractsWithMedia;
    use SyncsResolvedMediaUrls;

    public const SECTIONS = [
        'promo' => 'Promo banners',
        'usp' => 'Why Rythme (USPs)',
        'number' => 'Stats / numbers',
        'testimonial' => 'Testimonials',
        'story' => 'Stories',
        'ugc' => 'UGC gallery',
        'comparison' => 'Comparison rows',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /** Block image URL — the stored column first, Media Library for unsynced rows. */
    public function imageUrl(): ?string
    {
        return $this->image_url ?? $this->getFirstMedia('image')?->getUrl();
    }

    public function resolvedMediaUrls(): array
    {
        return [
            'image_url' => $this->getFirstMedia('image')?->getUrl(),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function scopeSection($query, string $key): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('section_key', $key)->where('is_active', true)->orderBy('sort_order');
    }
}
