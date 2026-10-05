<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver as SpatieMediaObserver;

/**
 * File safety for reused media (docs/media-architecture.md → M-10).
 *
 * Spatie deletes the files of a media row whenever the row is deleted. That is
 * exactly right for a file owner — and exactly wrong for a *shared* row (it does
 * not own the file) or for an owner whose file is still reused elsewhere.
 *
 * Rules:
 *  1. Deleting a shared row  → only the row goes away, the file stays.
 *  2. Deleting an owner row that other rows still reuse → the file stays
 *     (the last remaining usage to go takes the file with it).
 *  3. Deleting the last row on a path → normal Spatie cleanup.
 *  4. A shared row never renames or moves the file it does not own.
 *  5. When a conversion finishes for an owner row, every reused row is mirrored
 *     so all usages upgrade to the same URL (`MediaUrlObserver` then re-resolves
 *     their owners' stored URL columns).
 *
 * Registered as `media-library.media_observer` in config/media-library.php.
 */
final class MediaFileObserver extends SpatieMediaObserver
{
    public function updating(SpatieMedia $media): void
    {
        if ($this->isShared($media)) {
            // The file (and therefore the file name) belongs to the owner row:
            // renaming/moving it here would break every other usage. Two
            // legitimate exceptions: `shared_path` itself moved (media:dedupe
            // re-points the row), or the owner's name was mirrored onto this
            // usage by mirrorToSharedRows() below.
            if (! $media->isDirty('shared_path') && $media->isDirty('file_name')) {
                $owner = $media->sharedOwner;

                if (! $owner instanceof Media || $owner->file_name !== $media->file_name) {
                    $media->file_name = $media->getOriginal('file_name');
                }
            }

            return;
        }

        parent::updating($media);
    }

    public function updated(SpatieMedia $media): void
    {
        parent::updated($media);

        if (! $media instanceof Media || $this->isShared($media)) {
            return;
        }

        if ($media->wasChanged('file_name')
            || $media->wasChanged('generated_conversions')
            || $media->wasChanged('responsive_images')) {
            $this->mirrorToSharedRows($media);
        }
    }

    public function deleted(SpatieMedia $media): void
    {
        if (! $media instanceof Media) {
            parent::deleted($media);

            return;
        }

        // 1. A shared row owns no file. Its file belongs to the owner row, and
        //    stays as long as that row — or any other usage — needs it. Only the
        //    very last usage of a file whose owner is already gone cleans up.
        if ($media->isShared()) {
            if (Media::rowsResolvingTo($media->sharingBasePath(), $media->getKey())->exists()) {
                return;
            }

            parent::deleted($media);

            return;
        }

        // 2. The file is still used somewhere else — keep it. The rows that
        //    reuse it keep resolving through their own `shared_path`, so they
        //    simply stop pointing at a row that no longer exists.
        if (Media::rowsResolvingTo($media->sharingBasePath(), $media->getKey())->exists()) {
            Media::query()
                ->where('source_media_id', $media->getKey())
                ->update(['source_media_id' => null]);

            return;
        }

        // 3. Nobody else uses it: normal cleanup (original + conversions +
        //    responsive images).
        parent::deleted($media);
    }

    private function isShared(SpatieMedia $media): bool
    {
        return $media instanceof Media && $media->isShared();
    }

    /**
     * Copy an owner's file state onto every reused row: the file name is part of
     * the resolved path, and the conversion/responsive flags decide whether the
     * storefront serves the original or the optimised file.
     */
    private function mirrorToSharedRows(Media $owner): void
    {
        $rows = Media::rowsResolvingTo($owner->sharingBasePath(), $owner->getKey())->get();

        foreach ($rows as $row) {
            $row->file_name = $owner->file_name;
            $row->generated_conversions = $owner->generated_conversions;
            $row->responsive_images = $owner->responsive_images;

            // A normal save (not saveQuietly) so MediaUrlObserver re-resolves the
            // URL columns of the model that reuses this image.
            $row->save();
        }
    }
}
