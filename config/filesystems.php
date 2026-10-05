<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            // NOT web-served. Laravel registers `GET /storage/{path}` for every
            // local disk with `serve => true`; on the private disk that route
            // requires a signed URL (403 in dev, 404 in production) and reads
            // from storage/app/private. Because its URI defaults to `/storage`,
            // it used to answer the storefront's media requests whenever the
            // `public/storage` symlink was missing — every product image then
            // 404'd in the panel and on the site. `/storage` belongs to the
            // media disk below (see docs/media-architecture.md → M-2).
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        // Publicly readable disk — admin-managed media (product / brand /
        // category / hero images) lives here (see MEDIA_DISK + docs/media-architecture.md).
        //
        // `url` is deliberately a RELATIVE path. A browser resolves it against
        // whatever origin it loaded the page from, so media keeps working when
        // APP_URL is wrong/empty, behind a TLS-terminating proxy, on a preview
        // domain, or on `www` vs apex. (An absolute APP_URL-based URL made both
        // <img> tags and Filament's file-upload preview fetch() fail silently.)
        // Set MEDIA_URL only to serve media from a CDN / other origin.
        //
        // `serve => true` makes Laravel itself answer `GET /storage/{path}` from
        // THIS disk (visibility public → no signature needed). With the
        // `public/storage` symlink in place the web server keeps serving those
        // files statically and this route is never reached; without the symlink
        // (fresh clone, cPanel plan B, Windows junction failed, `storage:link`
        // forgotten) images still resolve instead of 404ing. Repair/verify with
        // `php artisan media:doctor`.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim((string) (env('MEDIA_URL') ?: '/storage'), '/'),
            'visibility' => 'public',
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // Cloudinary — products + categories images (phase 1, docs/cloudinary-media.md).
        //
        // The `cloudinary` driver is registered by cloudinary-labs/cloudinary-laravel
        // (`FilesystemManager::extend('cloudinary', …)`), so this is an ordinary
        // Laravel disk: put/read/delete/url all go through Cloudinary's API.
        //
        // NOTE: `url` here is Cloudinary's *connection string*
        // (`cloudinary://API_KEY:API_SECRET@CLOUD_NAME`) — that is the package's
        // convention, not a public base URL. The storefront's delivery URLs are
        // derived (no API call) by App\Support\CloudinaryDeliveryUrl from
        // `cloud` / `url` + the asset path.
        //
        // Only collections named in config('media-library.cloudinary.collections')
        // are written here (App\Support\MediaDisk); every other collection keeps
        // using the MEDIA_DISK disk above.
        'cloudinary' => [
            'driver' => 'cloudinary',
            'url' => env('CLOUDINARY_URL') ?: null,
            'cloud' => env('CLOUDINARY_CLOUD_NAME'),
            'key' => env('CLOUDINARY_KEY') ?: env('CLOUDINARY_API_KEY'),
            'secret' => env('CLOUDINARY_SECRET') ?: env('CLOUDINARY_API_SECRET'),
            'secure' => (bool) env('CLOUDINARY_SECURE', true),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
