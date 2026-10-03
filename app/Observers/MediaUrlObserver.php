<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Models\Contracts\HasResolvedMediaUrls;
use App\Services\CategoryService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Keeps every persisted media-URL column in step with its media rows
 * (docs/media-architecture.md → M-7).
 *
 * This is the ONLY automatic trigger, and it covers every path that can change
 * a resolved URL:
 *  - upload / replace (`created`),
 *  - delete (`deleted`),
 *  - drag-reorder in the admin (`order_column`),
 *  - WebP conversion finished, i.e. the column upgrades from the original to
 *    the converted URL (`generated_conversions`),
 *  - a media row moved to another disk (`disk`/`conversions_disk`).
 */
final class MediaUrlObserver
{
    /** Media attributes whose change can alter a resolved URL. */
    private const RELEVANT = [
        'collection_name',
        'file_name',
        'name',
        'disk',
        'conversions_disk',
        'generated_conversions',
        'order_column',
        'mime_type',
        'size',
        'manipulations',
        'custom_properties',
    ];

    public function created(Media $media): void
    {
        $this->syncOwner($media);
    }

    public function updated(Media $media): void
    {
        // Conversions, reorders and disk moves save the media row *without*
        // touching the file itself; unrelated writes (e.g. a touch) must not
        // cost an owner query.
        if (! $media->wasChanged(self::RELEVANT)) {
            return;
        }

        $this->syncOwner($media);
    }

    public function deleted(Media $media): void
    {
        $this->syncOwner($media);
    }

    private function syncOwner(Media $media): void
    {
        // `model` is a morphTo: null when the owner is gone (or soft-deleted),
        // in which case there is nothing left to keep in sync.
        $owner = $media->model;

        if (! $owner instanceof HasResolvedMediaUrls) {
            return;
        }

        $changed = $owner->resolvedMediaUrlChanges() !== [];
        $owner->syncResolvedMediaUrls();

        if (! $changed) {
            return;
        }

        // The homepage caches the resolved payload (models + brand logo URLs +
        // category icon URLs) for an hour, and CategoryService caches the
        // category tree forever. A media change that moves a URL must drop
        // those caches — otherwise a replaced image keeps rendering the old URL.
        HomepageDataObserver::flush();

        if ($owner instanceof Category) {
            app(CategoryService::class)->flush();
        }
    }
}
