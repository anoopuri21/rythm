<?php

declare(strict_types=1);

return [
    'source' => [
        'base_url' => env('CATALOGUE_SOURCE_URL', 'https://www.bajaao.com'),
        'allowed_host' => 'www.bajaao.com',
        'media_hosts' => ['cdn.shopify.com'],
        'user_agent' => 'RythmeCataloguePilot/1.0 (+controlled public-data migration; contact store owner)',
    ],
    'pilot' => [
        'collection' => 'acoustic-guitars',
        'limit' => 5,
        'delay_ms' => 1500,
        'images_per_product' => 3,
        'max_image_bytes' => 5 * 1024 * 1024,
        'max_run_bytes' => 100 * 1024 * 1024,
        'output_root' => sys_get_temp_dir().'/rythme-catalogue',
    ],
];
