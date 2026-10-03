<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contracts\HasResolvedMediaUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

/**
 * Keeps stored media on the single configured media disk
 * (config('media-library.disk_name') ← MEDIA_DISK).
 *
 * Why this exists: before the media disk was pinned, Filament panel uploads
 * followed FILESYSTEM_DISK (default: the PRIVATE `local` disk). Those rows
 * still point at a disk the storefront cannot serve, so their images 403/404
 * and the admin preview never loads. relocate() fixes such rows in place.
 *
 * Safety: for every media item the original, its conversions and responsive
 * images are copied to the media disk and verified (size) BEFORE the row is
 * repointed, and the old files are removed only AFTER the row is saved. A crash
 * at any point leaves a valid state and re-running simply finishes the job.
 */
final class MediaRelocationService
{
    public function targetDisk(): string
    {
        return (string) config('media-library.disk_name');
    }

    /**
     * What an operator needs to know before images can load in a browser.
     *
     * `public` is false for a local-driver disk without `visibility => public`
     * (e.g. MEDIA_DISK=local): its files can never be served to a browser.
     *
     * @return array{disk:string, driver:string, public:bool, url:?string, links:list<array{link:string,target:string,ok:bool}>}
     */
    public function diagnostics(): array
    {
        $disk = $this->targetDisk();
        $config = (array) config("filesystems.disks.{$disk}", []);
        $root = isset($config['root']) ? (string) $config['root'] : null;

        $links = [];
        if (($config['driver'] ?? null) === 'local' && $root !== null) {
            foreach ((array) config('filesystems.links', []) as $link => $target) {
                if (realpath((string) $target) !== realpath($root)) {
                    continue;
                }

                $links[] = [
                    'link' => (string) $link,
                    'target' => (string) $target,
                    'ok' => is_link((string) $link) && realpath((string) $link) === realpath((string) $target),
                ];
            }
        }

        return [
            'disk' => $disk,
            'driver' => (string) ($config['driver'] ?? 'unknown'),
            'public' => ($config['driver'] ?? null) !== 'local' || ($config['visibility'] ?? null) === 'public',
            'url' => isset($config['url']) ? (string) $config['url'] : null,
            'links' => $links,
        ];
    }

    /**
     * Number of media rows whose original or conversions live on another disk.
     */
    public function misplacedCount(): int
    {
        return $this->misplacedQuery()->count();
    }

    /**
     * Move every misplaced media item to the media disk.
     *
     * @return array{target:string, dry_run:bool, examined:int, moved:list<array{id:int,from:string,files:int}>, failed:list<array{id:int,reason:string}>}
     */
    public function relocateAll(bool $dryRun = false): array
    {
        $report = [
            'target' => $this->targetDisk(),
            'dry_run' => $dryRun,
            'examined' => 0,
            'moved' => [],
            'failed' => [],
        ];

        $this->misplacedQuery()->chunkById(100, function ($chunk) use (&$report, $dryRun): void {
            /** @var Media $media */
            foreach ($chunk as $media) {
                $report['examined']++;

                try {
                    $report['moved'][] = $this->relocate($media, $dryRun);
                } catch (Throwable $e) {
                    $report['failed'][] = ['id' => (int) $media->getKey(), 'reason' => $e->getMessage()];
                }
            }
        });

        return $report;
    }

    /**
     * Move one media item (original + conversions + responsive images).
     *
     * @return array{id:int,from:string,files:int}
     *
     * @throws RuntimeException when the original is missing or a copy cannot be verified
     */
    public function relocate(Media $media, bool $dryRun = false): array
    {
        $target = $this->targetDisk();
        $originalDisk = (string) $media->disk;
        $derivedDisk = (string) ($media->conversions_disk ?: $originalDisk);
        $generator = PathGeneratorFactory::create($media);

        $original = $generator->getPath($media).$media->file_name;
        $derivedDirs = array_unique([
            $generator->getPathForConversions($media),
            $generator->getPathForResponsiveImages($media),
        ]);

        // source disk => relative paths to move (same relative path on the target)
        $plan = [];

        if ($originalDisk !== $target) {
            if (! Storage::disk($originalDisk)->exists($original)) {
                throw new RuntimeException("Original file [{$original}] is missing on disk [{$originalDisk}].");
            }

            $plan[$originalDisk][] = $original;
        }

        if ($derivedDisk !== $target) {
            foreach ($derivedDirs as $directory) {
                foreach (Storage::disk($derivedDisk)->allFiles($directory) as $file) {
                    $plan[$derivedDisk][] = $file;
                }
            }
        }

        $files = (int) array_sum(array_map('count', $plan));

        if (! $dryRun) {
            $this->copyVerified($plan, $target);

            $media->disk = $target;
            $media->conversions_disk = $target;
            // Quiet on purpose: a normal save would re-trigger Spatie's own
            // MediaObserver (path/file-name syncing) mid-relocation.
            $media->saveQuietly();

            // …which means MediaUrlObserver does not see this change, so the
            // owner's stored URL columns (M-7) must be refreshed explicitly —
            // the URLs just moved to another disk.
            $owner = $media->model;
            if ($owner instanceof HasResolvedMediaUrls) {
                $owner->syncResolvedMediaUrls();
            }

            $this->removeSources($plan, $generator->getPath($media));
        }

        return ['id' => (int) $media->getKey(), 'from' => $originalDisk, 'files' => $files];
    }

    /**
     * @param  array<string, list<string>>  $plan
     */
    private function copyVerified(array $plan, string $target): void
    {
        $destination = Storage::disk($target);

        foreach ($plan as $sourceName => $paths) {
            $source = Storage::disk($sourceName);

            foreach (array_unique($paths) as $path) {
                $stream = $source->readStream($path);

                if (! is_resource($stream)) {
                    throw new RuntimeException("Could not read [{$path}] from disk [{$sourceName}].");
                }

                try {
                    $written = $destination->writeStream($path, $stream);
                } finally {
                    fclose($stream);
                }

                if ($written === false || ! $destination->exists($path) || $destination->size($path) !== $source->size($path)) {
                    throw new RuntimeException("Copy of [{$path}] to disk [{$target}] could not be verified.");
                }
            }
        }
    }

    /**
     * @param  array<string, list<string>>  $plan
     */
    private function removeSources(array $plan, string $mediaDirectory): void
    {
        foreach ($plan as $sourceName => $paths) {
            $source = Storage::disk($sourceName);
            $source->delete(array_values(array_unique($paths)));

            // Tidy the now-empty per-media folder — never the disk root.
            if (trim($mediaDirectory, '/') !== '' && $source->allFiles($mediaDirectory) === []) {
                $source->deleteDirectory($mediaDirectory);
            }
        }
    }

    private function misplacedQuery(): Builder
    {
        $target = $this->targetDisk();
        $model = (string) config('media-library.media_model', Media::class);

        return $model::query()->where(function ($query) use ($target): void {
            $query->where('disk', '!=', $target)
                ->orWhere('conversions_disk', '!=', $target);
        });
    }
}
