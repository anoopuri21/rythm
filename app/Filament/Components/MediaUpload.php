<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Support\MediaDisk;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

/**
 * The single definition of an admin image upload (Spatie Media Library).
 *
 * Every media field in the panel (product / variant / brand / category / hero /
 * homepage block) is built here, so MIME, byte size, pixel size and count
 * limits are explicit, bounded and identical everywhere — a field cannot
 * forget them.
 *
 * The disk is decided in ONE place — `App\Support\MediaDisk::forCollection()`:
 * MEDIA_DISK (config/media-library.php + config/filament.php) for every
 * collection, Cloudinary for the collections the rollout names
 * (docs/cloudinary-media.md). The models resolve the same value, so a field can
 * never end up on the private default disk (whose files the storefront cannot
 * serve) or on a different disk than the one the storefront reads its stored
 * URL columns from. See docs/media-architecture.md.
 *
 * Visibility is still never configured per field: it comes from the disk.
 *
 * Deliberately NOT used: Filament's `->image()`, which re-writes
 * acceptedFileTypes to `image/*` and would therefore re-admit SVG (and any
 * future script-capable image mime). The explicit raster list stays.
 */
final class MediaUpload
{
    /** Raster formats accepted by every image field. SVG is never accepted (script-capable). */
    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Product and variant galleries additionally accept AVIF. */
    public const GALLERY_TYPES = [...self::IMAGE_TYPES, 'image/avif'];

    /**
     * Pixel ceiling for every image field.
     *
     * Byte-size limits alone do not bound decode cost: a highly compressible
     * 5 MB PNG can be ~30000×30000 px, which needs gigabytes in GD/Imagick and
     * would kill the queue worker (aborting every queued WebP conversion, not
     * just that file). 6000×6000 is far above any real product photo and still
     * bounded. Raise it per field only with a matching PHP memory_limit.
     */
    public const MAX_WIDTH = 6000;

    public const MAX_HEIGHT = 6000;

    /**
     * Exactly one image (logo, icon, banner, social-share image).
     * Uploading a new file replaces the current one.
     */
    public static function single(
        string $name,
        string $collection,
        int $maxSizeKb,
        int $maxWidth = self::MAX_WIDTH,
        int $maxHeight = self::MAX_HEIGHT,
    ): SpatieMediaLibraryFileUpload {
        return self::field($name, $collection, self::IMAGE_TYPES, $maxSizeKb, $maxWidth, $maxHeight)
            ->maxFiles(1);
    }

    /**
     * Up to $maxFiles images (product and variant galleries).
     *
     * Galleries are reorderable: the first image is what the storefront uses
     * as the card/hero image (`Product::thumbnailImage()` / `heroImage()`),
     * so the order decides the primary photo. Reordering persists through
     * Spatie's `order_column` (the panel plugin's reorder handler).
     */
    public static function gallery(
        string $name,
        string $collection,
        int $maxFiles,
        int $maxSizeKb = 5120,
        int $maxWidth = self::MAX_WIDTH,
        int $maxHeight = self::MAX_HEIGHT,
    ): SpatieMediaLibraryFileUpload {
        return self::field($name, $collection, self::GALLERY_TYPES, $maxSizeKb, $maxWidth, $maxHeight)
            ->multiple()
            ->maxFiles($maxFiles)
            ->reorderable();
    }

    /**
     * @param  list<string>  $mimeTypes
     */
    private static function field(string $name, string $collection, array $mimeTypes, int $maxSizeKb, int $maxWidth, int $maxHeight): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($name)
            ->collection($collection)
            // Where the saved file is written: MEDIA_DISK for every collection,
            // Cloudinary for the ones the rollout names (docs/cloudinary-media.md).
            // The models resolve the same disk through the same class
            // (App\Support\MediaDisk), so the panel and the storefront's stored
            // URL columns can never disagree.
            ->disk(MediaDisk::forCollection($collection))
            ->acceptedFileTypes($mimeTypes)
            ->maxSize($maxSizeKb)
            // Ordered after acceptedFileTypes on purpose: Laravel stops at the
            // first failing rule per file, so a non-image never reaches the
            // dimensions check (which would only say "must be an image").
            ->rule("dimensions:max_width={$maxWidth},max_height={$maxHeight}");
    }
}
