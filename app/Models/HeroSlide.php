<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\HomepageDataObserver;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Table('hero_slides')]
#[ObservedBy(HomepageDataObserver::class)]
#[Fillable(['eyebrow', 'title', 'accent', 'copy', 'cta_label', 'cta_href', 'sort_order', 'is_active'])]
class HeroSlide extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop_image')->singleFile();
        $this->addMediaCollection('mobile_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('hero-desktop-webp')
            ->width(1920)
            ->height(1080)
            ->format('webp')
            ->quality(84)
            ->queued()
            ->performOnCollections('desktop_image');

        $this->addMediaConversion('hero-mobile-webp')
            ->width(768)
            ->height(1024)
            ->format('webp')
            ->quality(82)
            ->queued()
            ->performOnCollections('mobile_image');
    }

    public function desktopImageUrl(): ?string
    {
        return $this->convertedUrl('desktop_image', 'hero-desktop-webp');
    }

    public function mobileImageUrl(): ?string
    {
        return $this->convertedUrl('mobile_image', 'hero-mobile-webp');
    }

    private function convertedUrl(string $collection, string $conversion): ?string
    {
        $media = $this->getFirstMedia($collection);

        if ($media === null) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }
}
