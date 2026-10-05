<?php

declare(strict_types=1);

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

/**
 * Lets several media rows share ONE stored file (docs/media-architecture.md → M-10).
 *
 * Spatie gives every media row its own directory: `{media-id}/{file_name}`,
 * `{media-id}/conversions/…`, `{media-id}/responsive-images/…`. That is why
 * uploading the same image twice stores it twice.
 *
 * A "shared" row carries `shared_path` — the base path of the file it reuses —
 * and this generator returns that base instead of the row's own id. Everything
 * else (originals, conversions, responsive images, `getPathRelativeToRoot()`,
 * file removal, doctor/relocate paths, Cloudinary public ids) keeps using
 * Spatie's own logic through the parent class, so nothing is re-implemented.
 *
 * The base path is stored on the row (not looked up from the owner) so a shared
 * image keeps resolving after the original row is deleted.
 *
 * Registered as `media-library.path_generator` in config/media-library.php.
 *
 * @see app/Services/MediaReuseService.php
 */
final class MediaPathGenerator extends DefaultPathGenerator
{
    /** The base path a row resolves to (shared rows: the reused file's base). */
    public function getBasePath(Media $media): string
    {
        $shared = trim((string) ($media->shared_path ?? ''));

        return $shared !== '' ? $shared : parent::getBasePath($media);
    }

    /** Static shortcut for the code that needs a base path without a full generator. */
    public static function basePath(Media $media): string
    {
        return (new self)->getBasePath($media);
    }

    /**
     * The media id a base path belongs to, if it can be told apart
     * (`{prefix}/12` → 12; a custom path generator would return null).
     */
    public static function ownerKeyFromBasePath(string $basePath): ?int
    {
        $basePath = trim($basePath, '/');
        $prefix = trim((string) config('media-library.prefix', ''), '/');

        if ($prefix !== '' && str_starts_with($basePath, $prefix.'/')) {
            $basePath = substr($basePath, strlen($prefix) + 1);
        }

        return ctype_digit($basePath) ? (int) $basePath : null;
    }
}
