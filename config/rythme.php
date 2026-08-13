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
    'logo_url' => env(
        'RYTHME_LOGO_URL',
        'https://www.rhythmexports.com/wp-content/uploads/2023/10/Rhythm.png'
    ),

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
    |--------------------------------------------------------------------------
    | Payments — Razorpay (test mode by default)
    |--------------------------------------------------------------------------
    | When keys are missing the app falls back to FakePaymentGateway so the
    | full checkout flow works locally / in preview. Production must set:
    |   RYTHME_RAZORPAY_KEY_ID=rzp_test_xxx
    |   RYTHME_RAZORPAY_KEY_SECRET=xxx
    |   RYTHME_RAZORPAY_WEBHOOK_SECRET=xxx   (for async webhooks)
    |--------------------------------------------------------------------------
    */
    'razorpay' => [
        'key_id' => env('RYTHME_RAZORPAY_KEY_ID'),
        'key_secret' => env('RYTHME_RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RYTHME_RAZORPAY_WEBHOOK_SECRET'),
    ],

    'shipping' => [
        'flat_fee' => 0,        // free shipping (INR)
        'free_above' => 0,      // always free
    ],
];
