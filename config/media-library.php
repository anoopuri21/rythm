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

];
