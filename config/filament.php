<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Filament configuration (overrides only — everything else keeps the
| package defaults from filament/support's config/filament.php)
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The package default is env('FILESYSTEM_DISK', 'local'), i.e. the PRIVATE
    | disk. Filament's SpatieMediaLibraryFileUpload passes this disk to
    | Spatie's toMediaCollection(), so with the default every admin upload
    | silently went to storage/app/private: previews fell back to signed,
    | request-host-bound URLs (stuck "loading" in FilePond) and the storefront
    | got unsigned /storage/... URLs that the private disk answers with 403.
    |
    | Admin-managed media must follow the single media disk setting that the
    | media library itself uses (config('media-library.disk_name') ← MEDIA_DISK),
    | so panel uploads, programmatic imports and storefront URLs always agree.
    | FILESYSTEM_DISK stays free for genuinely private/default-disk use.
    |
    */

    'default_filesystem_disk' => env('MEDIA_DISK') ?: 'public',

];
