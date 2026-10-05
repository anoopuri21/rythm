<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CloudinaryDeliveryUrl;
use App\Support\MediaDisk;
use App\Support\MediaPathGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * The application's media model — registered as `config('media-library.media_model')`.
 *
 * Behaviour is unchanged for files on the local media disk (every row created
 * before the Cloudinary rollout): `getUrl()` / `getAvailableUrl()` fall straight
 * through to Spatie, so `/storage/...` URLs — original, `thumb-webp` or
 * `gallery-webp` — keep resolving exactly as before.
 *
 * Files stored on Cloudinary are different in one important way: Spatie
 * conversions are never generated for them (see the models'
 * `registerMediaConversions()` guard). Cloudinary resizes and optimises from the
 * original at delivery time, so the conversion names below become **delivery
 * transformations** instead of files on a disk. That is what makes the promise
 * "new uploads are stored on Cloudinary and served from Cloudinary — nothing on
 * this server" true without a queue worker, without re-uploading a WebP copy and
 * without ever reading the original back to the server.
 *
 * @see docs/cloudinary-media.md
 * @see docs/media-architecture.md → M-4, M-9, M-10
 */
class Media extends SpatieMedia
{
    /** Spatie conversion name ⇒ Cloudinary delivery transformation. */
    public const CLOUDINARY_TRANSFORMATIONS = [
        'thumb-webp' => 'c_fit,w_480,h_480,f_auto,q_auto:good',
        'gallery-webp' => 'c_fit,w_1200,h_1200,f_auto,q_auto:good',
        'variant-thumb-webp' => 'c_fit,w_240,h_240,f_auto,q_auto:good',
        'variant-gallery-webp' => 'c_fit,w_1200,h_1200,f_auto,q_auto:good',
    ];

    /**
     * What a plain `getUrl()` returns: the original, optimised and converted to
     * the best format the requesting browser accepts (WebP/AVIF where possible).
     */
    private const CLOUDINARY_DEFAULT_TRANSFORMATION = 'f_auto,q_auto:good';

    /** Is this media row stored on Cloudinary (i.e. deliberately off MEDIA_DISK)? */
    public function isStoredOnCloudinary(): bool
    {
        return MediaDisk::isCloudinary($this->disk);
    }

    /** The delivery transformation for a conversion name ('' = deliver the original). */
    public function cloudinaryTransformation(string $conversionName = ''): string
    {
        if ($conversionName === '') {
            return self::CLOUDINARY_DEFAULT_TRANSFORMATION;
        }

        return self::CLOUDINARY_TRANSFORMATIONS[$conversionName]
            ?? self::CLOUDINARY_DEFAULT_TRANSFORMATION;
    }

    public function getUrl(string $conversionName = ''): string
    {
        if (! $this->isStoredOnCloudinary()) {
            return parent::getUrl($conversionName);
        }

        return CloudinaryDeliveryUrl::for(
            $this->getPathRelativeToRoot(),
            $this->cloudinaryTransformation($conversionName),
        );
    }

    /**
     * Same contract as Spatie's (`thumb-webp` when it exists, else the
     * original) — except that on Cloudinary every named conversion exists by
     * definition, because it is generated at delivery time.
     */
    public function getAvailableUrl(array $conversionNames): string
    {
        if (! $this->isStoredOnCloudinary()) {
            return parent::getAvailableUrl($conversionNames);
        }

        foreach ($conversionNames as $conversionName) {
            if (isset(self::CLOUDINARY_TRANSFORMATIONS[$conversionName])) {
                return $this->getUrl($conversionName);
            }
        }

        return $this->getUrl();
    }

    // ── Media reuse: one file, several usages (M-10) ────────────────────────
    //
    // An upload owns its file (`shared_path` NULL, path `{id}/{file_name}`).
    // Every further usage is a "shared" row that carries the owner's base path
    // in `shared_path`, so it resolves — URL, conversions, responsive images —
    // to the very same stored file. See App\Support\MediaPathGenerator and
    // App\Services\MediaReuseService.

    /** Does this row reuse another row's stored file instead of owning one? */
    public function isShared(): bool
    {
        return trim((string) ($this->shared_path ?? '')) !== '';
    }

    /** The base path this row resolves to (its own, or the reused file's). */
    public function sharingBasePath(): string
    {
        return MediaPathGenerator::basePath($this);
    }

    /** The row this one was reused from (null for file owners). */
    public function sharedOwner(): BelongsTo
    {
        return $this->belongsTo(static::class, 'source_media_id');
    }

    /** Every row that reuses this row's file. */
    public function usages(): HasMany
    {
        return $this->hasMany(static::class, 'source_media_id');
    }

    /** Rows that own a file (dedupe/doctor work on these, not on usages). */
    public function scopeFileOwners(Builder $query): Builder
    {
        return $query->whereNull('shared_path');
    }

    /**
     * Every row that resolves to the same stored file as $basePath — the owner
     * row plus its shared rows — optionally excluding one id (e.g. the row
     * being deleted).
     *
     * @return Builder<Media>
     */
    public static function rowsResolvingTo(string $basePath, ?int $exceptId = null): Builder
    {
        return static::query()
            ->resolvingTo($basePath)
            ->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId));
    }

    /**
     * Scope: rows stored in the file that lives at $basePath (the owner row —
     * whose id the base path is derived from — plus every shared row).
     *
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    public function scopeResolvingTo(Builder $query, string $basePath): Builder
    {
        $ownerKey = MediaPathGenerator::ownerKeyFromBasePath($basePath);

        return $query->where(function (Builder $inner) use ($basePath, $ownerKey): void {
            $inner->where('shared_path', $basePath);

            if ($ownerKey !== null) {
                // The owner row is the one whose id the base path is derived
                // from — it carries no `shared_path` of its own. There is no
                // `orWhereKey()` on the Eloquent/query builder (only
                // `whereKey()` / `whereKeyNot()`), so the OR is built as a
                // nested where on the qualified primary key.
                $inner->orWhere(fn (Builder $byKey): Builder => $byKey->whereKey($ownerKey));
            }
        });
    }
}
