<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Observers\HomepageDataObserver;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

/**
 * Media reuse — "upload once, use it anywhere" (docs/media-architecture.md → M-10).
 *
 * The first upload owns the file; every further usage is a lightweight shared
 * media row (`shared_path` = the owner's base path) that resolves to the very
 * same file, so nothing is stored twice — locally and on Cloudinary alike.
 *
 * This service is the only place that creates those rows and that merges
 * byte-identical duplicates (`media:dedupe`).
 *
 * @see docs/media-reuse.md
 * @see app/Support/MediaPathGenerator.php
 */
final class MediaReuseService
{
    /**
     * Attach an existing image to $target as a shared row (no disk write).
     *
     * Idempotent: if the same file is already attached to that collection, the
     * existing row is returned instead of creating a second usage.
     */
    public function attach(Media $source, HasMedia $target, string $collection, ?int $order = null): Media
    {
        if (! $target instanceof Model) {
            throw new InvalidArgumentException('Reusable media needs an Eloquent model as the target.');
        }

        $owner = $this->ownerOf($source);
        $basePath = $owner->sharingBasePath();

        $existing = $target->media()
            ->where('collection_name', $collection)
            ->resolvingTo($basePath)
            ->first();

        if ($existing instanceof Media) {
            return $existing;
        }

        // A single-file collection (e.g. category icon, hero image) can hold one
        // image: reuse replaces whatever is there — the file of the replaced row
        // stays as long as other rows reuse it (MediaFileObserver).
        if ($target->getMediaCollection($collection)?->singleFile === true) {
            $target->clearMediaCollection($collection);
        }

        $reference = $target->media()->create([
            'uuid' => (string) Str::uuid(),
            'collection_name' => $collection,
            'name' => $owner->name,
            'file_name' => $owner->file_name,
            'mime_type' => $owner->mime_type,
            'disk' => $owner->disk,
            'conversions_disk' => $owner->conversions_disk,
            'size' => $owner->size,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => $owner->generated_conversions ?? [],
            'responsive_images' => $owner->responsive_images ?? [],
            'order_column' => $order ?? $this->nextOrder($target, $collection),
            'shared_path' => $basePath,
            'source_media_id' => $owner->getKey(),
            'checksum' => $owner->checksum,
        ]);

        /** @var Media $reference */
        return $reference->refresh();
    }

    /** Resolve a possible chain of shared rows to the row that owns the file. */
    public function ownerOf(Media $media): Media
    {
        $seen = [];
        $current = $media;

        while ($current->isShared() && $current->source_media_id !== null) {
            if (isset($seen[$current->getKey()])) {
                break; // defensive: never loop on corrupted data
            }

            $seen[$current->getKey()] = true;

            $parent = Media::query()->find($current->source_media_id);

            if (! $parent instanceof Media) {
                break; // owner row deleted: the shared row itself is the authority
            }

            $current = $parent;
        }

        return $current;
    }

    /**
     * Every row that resolves to the same stored file (owner + usages).
     *
     * @return Collection<int, Media>
     */
    public function usagesOf(Media $media): Collection
    {
        return Media::query()
            ->resolvingTo($media->sharingBasePath())
            ->orderBy('id')
            ->get();
    }

    /** How many places use this image. */
    public function usageCount(Media $media): int
    {
        return Media::query()->resolvingTo($media->sharingBasePath())->count();
    }

    /** Human readable "where is this used" summary for the admin panel. */
    public function usageSummary(Media $media): string
    {
        $labels = [];

        foreach ($this->usagesOf($media) as $row) {
            $owner = $row->model;

            $label = $owner instanceof Model
                ? class_basename($owner).': '.$this->modelLabel($owner)
                : 'Orphaned';

            $labels[] = $label.' ('.$row->collection_name.')';
        }

        if ($labels === []) {
            return 'Not used';
        }

        $summary = array_slice($labels, 0, 2);
        $extra = count($labels) - count($summary);

        return implode(' · ', $summary).($extra > 0 ? " · +{$extra} more" : '');
    }

    /**
     * Collections an image can be attached to on $target, for the admin picker.
     *
     * @return array<string, string>
     */
    public function availableCollections(HasMedia $target): array
    {
        $collections = [];

        foreach ($target->getRegisteredMediaCollections() as $collection) {
            $label = Str::headline($collection->name);
            $collections[$collection->name] = $collection->singleFile ? $label.' (single image)' : $label;
        }

        return $collections;
    }

    /**
     * Give every file owner a sha256 (media:dedupe needs it to find images that
     * are byte-identical yet stored twice).
     *
     * Reading a Cloudinary row downloads it once — the command warns about it.
     */
    public function checksum(Media $media, bool $persist = true): ?string
    {
        $existing = trim((string) ($media->checksum ?? ''));

        if ($existing !== '') {
            return $existing;
        }

        try {
            $stream = Storage::disk($media->disk)->readStream($media->getPathRelativeToRoot());
        } catch (Throwable) {
            return null;
        }

        if (! is_resource($stream)) {
            return null;
        }

        $hash = hash_init('sha256');
        hash_update_stream($hash, $stream);
        fclose($stream);
        $checksum = hash_final($hash);

        if ($persist) {
            // Quiet: a checksum write is bookkeeping — it must not look like a
            // media change (no URL column sync, no file operations).
            $media->forceFill(['checksum' => $checksum])->saveQuietly();
        }

        return $checksum;
    }

    /**
     * Merge byte-identical duplicates into shared rows (php artisan media:dedupe).
     *
     * The oldest row of a group stays the file owner; every other copy is
     * re-pointed at it (usages included), then its files are removed. Nothing is
     * deleted unless the re-pointing succeeded.
     *
     * @param  Closure(string, string):void|null  $report  fn (level, message) — levels: info|merged|skipped|failed
     * @return array{groups:int, merged:int, repointed:int, files_removed:int, failed:int}
     */
    public function mergeDuplicates(bool $dryRun = false, ?string $disk = null, int $limit = 500, ?Closure $report = null): array
    {
        $report ??= static function (string $level, string $message): void {
        };

        $result = ['groups' => 0, 'merged' => 0, 'repointed' => 0, 'files_removed' => 0, 'failed' => 0];

        $groups = Media::query()
            ->fileOwners()
            ->whereNotNull('checksum')
            ->where('checksum', '!=', '')
            ->when($disk !== null && $disk !== '', fn ($query) => $query->where('disk', $disk))
            ->selectRaw('checksum, disk, COUNT(*) as rows_count')
            ->groupBy('checksum', 'disk')
            ->havingRaw('COUNT(*) > 1')
            ->orderByRaw('MIN(id)')
            ->limit($limit)
            ->get();

        foreach ($groups as $group) {
            $result['groups']++;

            $rows = Media::query()
                ->fileOwners()
                ->where('checksum', $group->checksum)
                ->where('disk', $group->disk)
                ->orderBy('id')
                ->get();

            /** @var Media $keeper */
            $keeper = $rows->first();
            $extras = $rows->skip(1);

            $report('info', "Group {$group->checksum} on [{$group->disk}]: ".count($rows)." copies — keeping media #{$keeper->getKey()} ({$keeper->file_name})");

            foreach ($extras as $duplicate) {
                /** @var Media $duplicate */
                try {
                    $merged = $this->mergeInto($duplicate, $keeper, $dryRun, $report);

                    if ($merged['removed']) {
                        $result['merged']++;
                    }

                    $result['repointed'] += $merged['repointed'];
                    $result['files_removed'] += $merged['removed'] ? 1 : 0;
                } catch (Throwable $exception) {
                    $result['failed']++;
                    $report('failed', "media #{$duplicate->getKey()}: ".$exception->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Turn a duplicate copy into a usage of $keeper: every row that resolves to
     * the duplicate's file — **including the duplicate row itself**, which is
     * somebody's gallery item — is re-pointed at the keeper, and only then are
     * the duplicate's own files removed.
     *
     * @return array{repointed:int, removed:bool}
     */
    private function mergeInto(Media $duplicate, Media $keeper, bool $dryRun, Closure $report): array
    {
        if ($duplicate->is($keeper)) {
            return ['repointed' => 0, 'removed' => false];
        }

        $duplicateBase = $duplicate->sharingBasePath();
        $keeperBase = $keeper->sharingBasePath();

        if ($duplicateBase === $keeperBase) {
            return ['repointed' => 0, 'removed' => false];
        }

        $rows = Media::query()
            ->resolvingTo($duplicateBase)
            ->orderBy('id')
            ->get();

        // Only keep the keeper's conversion marks that actually exist on its disk:
        // a mark without a file would 404 for every usage after the merge.
        $keeperConversions = array_filter(
            (array) ($keeper->generated_conversions ?? []),
            fn ($generated, string $conversion): bool => (bool) $generated
                && $this->conversionExists($keeper, $conversion),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($dryRun) {
            $report('info', '  would re-point '.count($rows)." row(s) and remove the extra copy of media #{$duplicate->getKey()}");

            return ['repointed' => count($rows), 'removed' => true];
        }

        // The duplicate's own locations, captured BEFORE the rows are re-pointed —
        // afterwards the duplicate row resolves to the keeper's path and these
        // would point at the file we must keep.
        $generator = PathGeneratorFactory::create($duplicate);
        $duplicateBaseDir = $generator->getPath($duplicate);
        $duplicateOriginal = $duplicateBaseDir.$duplicate->file_name;
        $duplicateConversionsDir = $generator->getPathForConversions($duplicate);
        $duplicateResponsiveDir = $generator->getPathForResponsiveImages($duplicate);
        $duplicateDisk = (string) $duplicate->disk;
        $duplicateConversionsDisk = (string) ($duplicate->conversions_disk ?: $duplicate->disk);

        $repointed = 0;

        foreach ($rows as $row) {
            $row->forceFill([
                'shared_path' => $keeperBase,
                'source_media_id' => $keeper->getKey(),
                'file_name' => $keeper->file_name,
                'mime_type' => $keeper->mime_type,
                'size' => $keeper->size,
                'disk' => $keeper->disk,
                'conversions_disk' => $keeper->conversions_disk,
                'generated_conversions' => $keeperConversions,
                'responsive_images' => $keeper->responsive_images ?? [],
            ])->save();

            $repointed++;
        }

        // Every row now resolves to the keeper, so the duplicate's own copy is
        // unreachable — remove it (and its derived files).
        $this->removeFiles(
            $duplicateBaseDir,
            $duplicateOriginal,
            $duplicateConversionsDir,
            $duplicateResponsiveDir,
            $duplicateDisk,
            $duplicateConversionsDisk,
        );

        $report('merged', "  media #{$duplicate->getKey()} merged into #{$keeper->getKey()} ({$repointed} row(s) re-pointed)");

        return ['repointed' => $repointed, 'removed' => true];
    }

    /**
     * Remove an unreferenced original plus its derived files and the now-empty
     * per-media folder, tolerating missing files/disks (the merge itself
     * already succeeded).
     */
    private function removeFiles(
        string $baseDir,
        string $original,
        string $conversionsDir,
        string $responsiveDir,
        string $disk,
        string $derivedDisk,
    ): void {
        foreach (array_unique([$disk, $derivedDisk]) as $diskName) {
            try {
                Storage::disk($diskName)->delete($original);
            } catch (Throwable) {
                // nothing to clean up
            }
        }

        foreach ([$conversionsDir, $responsiveDir] as $directory) {
            try {
                $storage = Storage::disk($derivedDisk);

                if ($storage->allFiles($directory) !== []) {
                    $storage->deleteDirectory($directory);
                }
            } catch (Throwable) {
                // nothing to clean up
            }
        }

        // Tidy the per-media folder — never the disk root.
        try {
            $storage = Storage::disk($disk);

            if (trim($baseDir, '/') !== '' && $storage->allFiles($baseDir) === []) {
                $storage->deleteDirectory($baseDir);
            }
        } catch (Throwable) {
            // nothing to clean up
        }
    }

    private function conversionExists(Media $media, string $conversion): bool
    {
        try {
            return Storage::disk($media->conversions_disk ?: $media->disk)
                ->exists($media->getPathRelativeToRoot($conversion));
        } catch (Throwable) {
            return false;
        }
    }

    private function nextOrder(HasMedia $target, string $collection): int
    {
        return 1 + (int) $target->media()
            ->where('collection_name', $collection)
            ->max('order_column');
    }

    private function modelLabel(Model $model): string
    {
        foreach (['name', 'title', 'slug'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }

    /** Kept for symmetry with the other media services: drop cached storefront payloads. */
    public function flushCaches(): void
    {
        HomepageDataObserver::flush();
    }
}
