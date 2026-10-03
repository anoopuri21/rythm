<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Support\ImageStore;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The single definition of an admin image upload that stores a plain file.
 *
 * Product images (and anything added later) are written to `public/uploads`
 * and the resulting URL is what gets saved on the record — see
 * App\Support\ImageStore and docs/media-architecture.md §7.
 *
 * Every image field in the panel is built here, so MIME, size and count limits
 * are explicit, bounded and identical everywhere: a field cannot forget them,
 * and SVG is never accepted (it can carry script).
 */
final class ImageUpload
{
    /** Raster formats accepted by every image field. SVG is never accepted. */
    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    /**
     * Exactly one image (main product image, social-share image).
     * Uploading a new file replaces the current one and the old file is deleted.
     */
    public static function single(string $name, string $directory, int $maxSizeKb): FileUpload
    {
        return self::field($name, $directory, $maxSizeKb)
            ->maxFiles(1);
    }

    /** Up to $maxFiles images (product gallery). */
    public static function gallery(string $name, string $directory, int $maxFiles, int $maxSizeKb = 5120): FileUpload
    {
        return self::field($name, $directory, $maxSizeKb)
            ->multiple()
            ->maxFiles($maxFiles);
    }

    private static function field(string $name, string $directory, int $maxSizeKb): FileUpload
    {
        return FileUpload::make($name)
            ->disk(ImageStore::DISK)
            ->visibility('public')
            ->image()
            ->acceptedFileTypes(self::IMAGE_TYPES)
            ->maxSize($maxSizeKb)
            // The column holds the URL of the file (not its disk path), so both
            // ends of the field — saving an upload and previewing a saved one —
            // go through the one place that knows the folder and the URL shape.
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): ?string => ImageStore::store($file, $directory))
            ->getUploadedFileUsing(fn (string $file): ?array => ImageStore::preview($file))
            ->fetchFileInformation(false);
    }
}
