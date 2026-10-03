<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

/**
 * The single definition of an admin image upload (Spatie Media Library).
 *
 * Every media field in the panel (product / variant / brand / category / hero /
 * homepage block) is built here, so MIME, size and count limits are explicit,
 * bounded and identical everywhere — a field cannot forget them.
 *
 * Disk and visibility are intentionally NOT configured per field. They come
 * from the one media-disk setting (MEDIA_DISK → config/media-library.php and
 * config/filament.php), so no field can end up on the private default disk
 * (whose files the storefront cannot serve). See docs/media-architecture.md.
 */
final class MediaUpload
{
    /** Raster formats accepted by every image field. SVG is never accepted (script-capable). */
    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Product and variant galleries additionally accept AVIF. */
    public const GALLERY_TYPES = [...self::IMAGE_TYPES, 'image/avif'];

    /**
     * Exactly one image (logo, icon, banner, social-share image).
     * Uploading a new file replaces the current one.
     */
    public static function single(string $name, string $collection, int $maxSizeKb): SpatieMediaLibraryFileUpload
    {
        return self::field($name, $collection, self::IMAGE_TYPES, $maxSizeKb)
            ->maxFiles(1);
    }

    /**
     * Up to $maxFiles images (product and variant galleries).
     */
    public static function gallery(string $name, string $collection, int $maxFiles, int $maxSizeKb = 5120): SpatieMediaLibraryFileUpload
    {
        return self::field($name, $collection, self::GALLERY_TYPES, $maxSizeKb)
            ->multiple()
            ->maxFiles($maxFiles);
    }

    /**
     * @param  list<string>  $mimeTypes
     */
    private static function field(string $name, string $collection, array $mimeTypes, int $maxSizeKb): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($name)
            ->collection($collection)
            ->acceptedFileTypes($mimeTypes)
            ->maxSize($maxSizeKb);
    }
}
