<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared-hosting-safe queue processing: cPanel invokes `schedule:run`
// each minute; this worker drains available jobs and then exits.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3 --timeout=45')
    ->everyMinute()
    ->withoutOverlapping(2);
