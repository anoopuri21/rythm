<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */
    'brand_name' => 'Rhythm Exports',
    'brand_short' => 'RHYTHM',
    'logo_url' => env('RYTHME_LOGO_URL', 'https://rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png'),

    // Fallbacks only — the live values are managed in the admin panel
    // (Filament → Settings). See App\Services\SiteSettingsService.
    'contact_phone' => env('RYTHME_CONTACT_PHONE', ''),
    'contact_email' => env('RYTHME_CONTACT_EMAIL', ''),
    'social_links' => [
        'instagram' => env('RYTHME_SOCIAL_INSTAGRAM', ''),
        'facebook' => env('RYTHME_SOCIAL_FACEBOOK', ''),
        'youtube' => env('RYTHME_SOCIAL_YOUTUBE', ''),
    ],

    /*
    | Public policy/content pages withheld until the owner approves their
    | business terms. Admin records may exist, but are not publication consent.
    */
    'withheld_public_pages' => [],

    'shipping' => [
        'flat_fee' => 0,
        'free_above' => 0,
    ],
];
