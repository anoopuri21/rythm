<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

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
 * The rollout also counts as off when the `cloudinary` disk cannot actually be
 * resolved (see `diskResolvable()`): its driver is provided by a package, not by
 * the framework, so credentials without that package must never be able to turn
 * an admin upload into a 500. Uploads then stay on MEDIA_DISK and
 * `php artisan media:doctor` reports the disk as a FAIL.
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
     *
     * The disk check comes last: a switched-off rollout must never touch the
     * filesystem (or a driver that may not even exist).
     */
    public static function enabled(): bool
    {
        return (bool) config('media-library.cloudinary.enabled', false)
            && self::cloudName() !== null
            && self::diskResolvable();
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
     * The Cloudinary cloud name: from the connection URL
     * (`CLOUDINARY_URL=cloudinary://key:secret@cloud_name`) or from
     * `CLOUDINARY_CLOUD_NAME`.
     *
     * The URL is checked first because the package's disk driver does exactly
     * that (`isset($config['url'])` → `new Cloudinary($url)`), so uploads and
     * the delivery URLs this app derives can never point at different clouds.
     */
    public static function cloudName(): ?string
    {
        $url = trim((string) config('filesystems.disks.cloudinary.url', ''));

        if ($url !== '' && preg_match('~@([^/@:]+)/?$~', $url, $matches) === 1) {
            return $matches[1];
        }

        $cloud = trim((string) config('filesystems.disks.cloudinary.cloud', ''));

        return $cloud !== '' ? $cloud : null;
    }

    /**
     * Can the [cloudinary] disk actually be resolved on this machine?
     *
     * `cloudinary` is NOT one of Laravel's built-in drivers: it is registered by
     * cloudinary-labs/cloudinary-laravel through `Storage::extend('cloudinary', …)`
     * (or by any `extend()` an app adds later). Having the disk in
     * config/filesystems.php therefore proves nothing — with the package absent,
     * `Storage::disk('cloudinary')` throws
     * "Driver [cloudinary] is not supported" from FilesystemManager::resolve(),
     * in the middle of an admin upload. Resolving the disk only builds the
     * adapter (no HTTP call), so asking on every disk decision is cheap.
     */
    public static function diskResolvable(): bool
    {
        return self::diskError() === null;
    }

    /**
     * Why the [cloudinary] disk cannot be resolved (null when it can).
     *
     * `php artisan media:doctor` prints this next to the fix, because the two
     * usual causes need different actions: the package is missing
     * (`composer require cloudinary-labs/cloudinary-laravel`) or the
     * credentials are unusable.
     */
    public static function diskError(): ?string
    {
        try {
            Storage::disk(self::CLOUDINARY);
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }

        return null;
    }
}
