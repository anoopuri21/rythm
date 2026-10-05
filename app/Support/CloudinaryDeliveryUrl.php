<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds Cloudinary delivery URLs without an API call.
 *
 * `Storage::disk('cloudinary')->url($path)` (the package's adapter) resolves the
 * URL through Cloudinary's Admin API — one HTTP round trip per media row, which
 * would run on every upload, delete, reorder and `media:sync-urls` pass. The
 * delivered URL is fully derivable from the cloud name, the file path (which is
 * the asset's public_id, minus the extension) and the transformation, so this
 * class derives it instead.
 *
 * Shape: https://res.cloudinary.com/{cloud}/image/upload/{transformations}/{path}
 * The file extension stays on the URL: Cloudinary keeps the asset under the
 * extension-less public_id (`{media-id}/{filename}`) and uses the URL's
 * extension as the delivery format.
 *
 * @see docs/cloudinary-media.md
 */
final class CloudinaryDeliveryUrl
{
    /** Products + categories are images only (MediaUpload accepts raster mime types). */
    private const RESOURCE_TYPE = 'image';

    /**
     * @param  string  $path  Host-relative disk path, e.g. `12/front.jpg`.
     * @param  string  $transformations  Cloudinary transformation, e.g. `c_fit,w_480,h_480,f_auto,q_auto:good` ('' = deliver the original).
     */
    public static function for(string $path, string $transformations = ''): string
    {
        $cloud = MediaDisk::cloudName();
        $path = ltrim(trim($path), '/');

        // Without a cloud name there is no delivery host to build. The callers
        // treat an empty string as "no URL" exactly like a missing conversion.
        if ($cloud === null || $path === '') {
            return '';
        }

        $base = 'https://res.cloudinary.com/'.rawurlencode($cloud).'/'.self::RESOURCE_TYPE.'/upload';

        $transformations = trim($transformations, " \t\n\r\0\x0B/");

        return $transformations === ''
            ? $base.'/'.$path
            : $base.'/'.$transformations.'/'.$path;
    }
}
