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
        // Desktop large banner + mobile-optimized portrait banner
        $this->addMediaCollection('desktop_image')->singleFile();
        $this->addMediaCollection('mobile_image')->singleFile();
    }
}
