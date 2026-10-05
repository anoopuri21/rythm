<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The ONE place that decides which disk a new upload for a collection is
 * written to (docs/cloudinary-media.md).
 *
 * Default behaviour is unchanged: every collection follows
 * `config('media-library.disk_name')` ← `MEDIA_DISK`. When the Cloudinary
 * rollout is switched on (`MEDIA_CLOUDINARY=true` + credentials), the
 * collections named in `media-library.cloudinary.collections` — products
 * (gallery + og), variant images and category icons — are written to the
 * `cloudinary` disk instead, and served straight from its CDN.
 *
 * Existing files are NOT touched: a media row keeps the disk it was stored on,
 * so every image uploaded before the switch keeps rendering from the exact same
 * `/storage/...` URL it used before (M-2), while new uploads never touch this
 * server's disk.
 *
 * Both sides of the upload read this class, so the admin form and the model
 * collections can never disagree:
 *  - `App\Filament\Components\MediaUpload` → `->disk(MediaDisk::forCollection($collection))`
 *  - the media-bearing models → `->useDisk(MediaDisk::forCollection($collection))`
 *
 * @see docs/cloudinary-media.md
 * @see docs/media-architecture.md → M-1, M-9
 */
final class MediaDisk
{
    /** The Laravel filesystem disk provided by cloudinary-labs/cloudinary-laravel. */
    public const CLOUDINARY = 'cloudinary';

    /** Collections that follow Cloudinary when the rollout is enabled (phase 1). */
    public const DEFAULT_CLOUD_COLLECTIONS = ['gallery', 'og', 'variant_gallery', 'icon'];

    /**
     * Is the Cloudinary rollout active?
     *
     * Requires BOTH the explicit switch and resolvable credentials, so a
     * half-configured environment can never route uploads into a disk that
     * cannot accept them — it just keeps storing on MEDIA_DISK.
     */
    public static function enabled(): bool
    {
        return (bool) config('media-library.cloudinary.enabled', false)
            && self::cloudName() !== null;
    }

    /** The disk a new upload for $collection must be written to. */
    public static function forCollection(string $collection): string
    {
        return self::isCloudCollection($collection) ? self::CLOUDINARY : self::mediaDisk();
    }

    /** Does a new upload for $collection go to Cloudinary? */
    public static function isCloudCollection(string $collection): bool
    {
        return self::enabled() && in_array($collection, self::cloudCollections(), true);
    }

    /** Is this stored media row on Cloudinary (i.e. intentionally off MEDIA_DISK)? */
    public static function isCloudinary(?string $disk): bool
    {
        return $disk === self::CLOUDINARY;
    }

    /** The configured media disk (MEDIA_DISK) — the home of every other collection. */
    public static function mediaDisk(): string
    {
        $disk = (string) config('media-library.disk_name', '');

        return $disk !== '' ? $disk : 'public';
    }

    /**
     * The collections that follow Cloudinary.
     *
     * Defaults to products + categories (phase 1); `MEDIA_CLOUDINARY_COLLECTIONS`
     * (comma separated) overrides the list without a code change, so the next
     * phase (brands, hero slides, homepage blocks) is one env edit.
     *
     * @return list<string>
     */
    public static function cloudCollections(): array
    {
        $configured = config('media-library.cloudinary.collections');

        if (is_string($configured) && trim($configured) !== '') {
            $configured = explode(',', $configured);
        }

        if (! is_array($configured) || $configured === []) {
            $configured = self::DEFAULT_CLOUD_COLLECTIONS;
        }

        $collections = [];

        foreach ($configured as $collection) {
            if (! is_string($collection)) {
                continue;
            }

            $collection = trim($collection);

            if ($collection !== '') {
                $collections[] = $collection;
            }
        }

        return array_values(array_unique($collections));
    }

    /**
     * The Cloudinary cloud name, from `CLOUDINARY_CLOUD_NAME` or from the
     * connection URL (`CLOUDINARY_URL=cloudinary://key:secret@cloud_name`).
     */
    public static function cloudName(): ?string
    {
        $cloud = trim((string) config('filesystems.disks.cloudinary.cloud', ''));

        if ($cloud !== '') {
            return $cloud;
        }

        $url = trim((string) config('filesystems.disks.cloudinary.url', ''));

        if ($url !== '' && preg_match('~@([^/@:]+)/?$~', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
