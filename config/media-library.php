<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Spatie Media Library configuration (overrides only — every other key keeps
| the package default from spatie/laravel-medialibrary's config file)
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Media disk — the ONE place that decides where admin-managed media lives
    |--------------------------------------------------------------------------
    |
    | Product / variant / brand / category / hero / homepage images, their
    | WebP conversions and responsive images all live on this disk. It MUST be
    | publicly readable (the storefront links straight to these files).
    |
    | config/filament.php reads the same MEDIA_DISK setting so Filament's panel
    | uploads can never drift onto a different (e.g. private) disk than the
    | programmatic imports and the storefront URLs. `?:` keeps a blank
    | `MEDIA_DISK=` line in .env from silently selecting the private default.
    |
    | Move already-stored files after changing it: php artisan media:relocate
    |
    */

    'disk_name' => env('MEDIA_DISK') ?: 'public',

    /*
    |--------------------------------------------------------------------------
    | Media model — the app's own Spatie Media model
    |--------------------------------------------------------------------------
    |
    | One override: media stored on Cloudinary resolve their URL as a Cloudinary
    | delivery URL (with the conversion name mapped to a delivery transformation)
    | instead of asking a disk. Rows on MEDIA_DISK behave exactly as before —
    | this is what keeps every pre-Cloudinary `/storage/...` image on the same
    | URL path. App\Providers\AppServiceProvider registers MediaUrlObserver on
    | whatever this setting points at.
    |
    | See docs/cloudinary-media.md
    |
    */

    'media_model' => App\Models\Media::class,

    /*
    |--------------------------------------------------------------------------
    | Media reuse — one file, several usages (M-10)
    |--------------------------------------------------------------------------
    |
    | A media row that reuses another row's file (`media.shared_path`) must
    | resolve to that file's path instead of its own `{id}/…` directory, and must
    | survive its own deletion / never rename a file it does not own. Both live in
    | the two classes below.
    |
    | See docs/media-reuse.md, tasks/MEDIA_REUSE_PLAN.md
    |
    */

    'path_generator' => App\Support\MediaPathGenerator::class,

    'media_observer' => App\Observers\MediaFileObserver::class,

    /*
    |--------------------------------------------------------------------------
    | Cloudinary rollout (products + categories — phase 1)
    |--------------------------------------------------------------------------
    |
    | When enabled, a NEW upload for one of `collections` is written to the
    | `cloudinary` disk (config/filesystems.php) and served from Cloudinary's
    | CDN; every other collection keeps using `disk_name` above. Existing media
    | rows are never moved — they keep their disk (and therefore their
    | `/storage/...` URL) until an admin re-uploads them.
    |
    | Decision point: App\Support\MediaDisk. Both the Filament upload field and
    | the models' media collections read it, so they cannot drift.
    |
    | Requirements: `composer require cloudinary-labs/cloudinary-laravel` plus
    | credentials in .env, then set MEDIA_CLOUDINARY=true. `php artisan
    | media:doctor` verifies the whole chain. See docs/cloudinary-media.md.
    |
    */

    'cloudinary' => [

        'enabled' => (bool) env('MEDIA_CLOUDINARY', false),

        // Comma separated in .env (MEDIA_CLOUDINARY_COLLECTIONS); defaults to
        // products (gallery + og), variant images and category icons.
        'collections' => env('MEDIA_CLOUDINARY_COLLECTIONS'),

    ],

];
