<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Optional admin override for a homepage banner slot.
 *
 * The built-in copy/image for every slot lives in
 * {@see \App\Services\HeroBannerService::DEFAULTS} — a row here only replaces
 * it. No row (or an inactive row) means the built-in default is shown, so the
 * homepage is complete on a fresh install.
 */
#[Table('hero_banners')]
#[Fillable(['slot', 'title', 'subtitle', 'cta_label', 'href', 'image_url', 'alt', 'is_active'])]
class HeroBanner extends Model implements HasMedia, HasResolvedMediaUrls
{
    use HasFactory;
    use InteractsWithMedia;
    use SyncsResolvedMediaUrls;

    /** Slot => admin-facing label. Order is the order the slots render in. */
    public const SLOTS = [
        'hero-tall' => 'Hero — tall banner (Stage Pianos)',
        'hero-small-1' => 'Hero — small banner 1 (Tabla Sets)',
        'hero-small-2' => 'Hero — small banner 2 (Studio Gear)',
        'launch-banner' => 'Recently launched — tall banner',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Banner image URL — the stored column first, Media Library for unsynced rows. */
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
}
