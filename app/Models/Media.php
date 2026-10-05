<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CloudinaryDeliveryUrl;
use App\Support\MediaDisk;
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
 * @see docs/media-architecture.md → M-4, M-9
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
}
