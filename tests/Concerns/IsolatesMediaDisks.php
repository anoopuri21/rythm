<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Point the real `local` (private), `public` and `uploads` disks at a
 * throw-away directory for the duration of one test.
 *
 * Unlike Storage::fake() this keeps the disks' real configuration (visibility,
 * `url`, serve flags) — which is exactly what the media URL regressions are
 * about — and nothing is ever written into storage/app of the repository.
 */
trait IsolatesMediaDisks
{
    protected string $mediaRoot = '';

    protected function isolateMediaDisks(): void
    {
        $this->mediaRoot = sys_get_temp_dir().'/rythm-media-'.bin2hex(random_bytes(6));

        foreach (['local', 'public', 'uploads'] as $disk) {
            File::ensureDirectoryExists($this->mediaRoot.'/'.$disk);
            config(["filesystems.disks.{$disk}.root" => $this->mediaRoot.'/'.$disk]);
        }

        // Spatie's conversion scratch space must not leak into storage/ either.
        config(['media-library.temporary_directory_path' => $this->mediaRoot.'/tmp']);

        Storage::forgetDisk(['local', 'public', 'uploads']);

        $this->beforeApplicationDestroyed(function (): void {
            File::deleteDirectory($this->mediaRoot);
        });
    }
}
