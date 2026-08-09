<?php

return [
    /*
    |--------------------------------------------------------------------------
    | s11 · Video Showcase
    |--------------------------------------------------------------------------
    | Default source: "Man Playing Guitar" by Pixabay on Pexels — free to use
    | (CC0 / Pexels license). https://www.pexels.com/video/man-playing-guitar-854924/
    | Override in .env: RYTHME_VIDEO_URL=https://your-cdn/video.mp4
    */
    'video_showcase_url' => env(
        'RYTHME_VIDEO_URL',
        'https://videos.pexels.com/video-files/854924/854924-hd_1920_1080_25fps.mp4'
    ),
];
