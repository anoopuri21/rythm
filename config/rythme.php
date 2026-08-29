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
    'logo_url' => env('RYTHME_LOGO_URL', '/images/logo-rythme.svg'),

    /*
    |--------------------------------------------------------------------------
    | Hero — two selectable modes (one at a time)
    |   'slider' = cinematic image slider (default)
    |   'video'  = theme-matched fullscreen video banner
    | Override in .env: RYTHME_HERO_MODE=video
    |--------------------------------------------------------------------------
    */
    'hero_mode' => env('RYTHME_HERO_MODE', 'slider'),

    /*
    |--------------------------------------------------------------------------
    | Hero video (video mode) — Pexels free license (CC0)
    | "Man Playing Guitar" by Pixabay — https://www.pexels.com/video/man-playing-guitar-854924/
    | Override in .env: RYTHME_HERO_VIDEO_URL=https://your-cdn/video.mp4
    |--------------------------------------------------------------------------
    */
    'hero_video_url' => env(
        'RYTHME_HERO_VIDEO_URL',
        // Local product montage (16s, 1920x1080, ffmpeg-built):
        // Squier Strat + Roland FP-30X + KRK Classic 7 (Bajaao product photos) +
        // 2 AI-generated dark shots (mic, synth — [AI Generated]) + stage poster,
        // alternating dark/light with smooth crossfades → public/videos/hero-montage.mp4
        '/videos/hero-montage.mp4'
    ),

    /*
    |--------------------------------------------------------------------------
    | s11 · Video Showcase
    |--------------------------------------------------------------------------
    | Same Pexels CC0 clip by default; override via RYTHME_VIDEO_URL
    */
    'video_showcase_url' => env(
        'RYTHME_VIDEO_URL',
        'https://videos.pexels.com/video-files/854924/854924-hd_1920_1080_25fps.mp4'
    ),

    /*
    | Public policy/content pages withheld until the owner approves their
    | business terms. Admin records may exist, but are not publication consent.
    */
    'withheld_public_pages' => ['shipping', 'returns', 'warranty', 'faqs'],

    'shipping' => [
        'flat_fee' => 0,
        'free_above' => 0,
    ],
];
