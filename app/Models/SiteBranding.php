<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\SyncsResolvedMediaUrls;
use App\Models\Contracts\HasResolvedMediaUrls;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The one storefront branding row: navbar logo and footer logo.
 *
 * URL columns are a derived cache (M-7). They are not fillable; MediaUrlObserver
 * writes them when a logo is uploaded or removed.
 */
#[Table('site_branding')]
class SiteBranding extends Model implements HasMedia, HasResolvedMediaUrls
{
    use InteractsWithMedia;
    use SyncsResolvedMediaUrls;

    public static function current(): self
    {
        $existing = static::query()->first();

        if ($existing instanceof self) {
            return $existing;
        }

        return static::query()->create();
    }

    /** Navbar logo — stored column first, Media Library when the column is not synced yet. */
    public function navbarLogoUrl(): ?string
    {
        return $this->navbar_logo_url ?? $this->getFirstMedia('navbar_logo')?->getUrl();
    }

    /** Footer logo — stored column first, Media Library when the column is not synced yet. */
    public function footerLogoUrl(): ?string
    {
        return $this->footer_logo_url ?? $this->getFirstMedia('footer_logo')?->getUrl();
    }

    public function resolvedMediaUrls(): array
    {
        return [
            'navbar_logo_url' => $this->getFirstMedia('navbar_logo')?->getUrl(),
            'footer_logo_url' => $this->getFirstMedia('footer_logo')?->getUrl(),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('navbar_logo')->singleFile();
        $this->addMediaCollection('footer_logo')->singleFile();
    }
}
