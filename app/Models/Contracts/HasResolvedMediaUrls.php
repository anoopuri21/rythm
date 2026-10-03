<?php

declare(strict_types=1);

namespace App\Models\Contracts;

/**
 * A model whose media URLs are persisted in DB columns (see
 * docs/media-architecture.md → M-7).
 *
 * The model owns the *rules* (`resolvedMediaUrls()`); the shared write mechanics
 * live in App\Models\Concerns\SyncsResolvedMediaUrls; the trigger is
 * App\Observers\MediaUrlObserver (any media add/delete/reorder/conversion save)
 * plus `php artisan media:sync-urls` for a bulk backfill or repair.
 */
interface HasResolvedMediaUrls
{
    /**
     * The exact column values this model's media collections resolve to.
     *
     * Keys are column names on the model; values are host-relative URLs
     * (`/storage/...`) or ordered lists of them. A model with no media returns
     * an empty list for a JSON column (so "resolved, nothing there" is
     * distinguishable from NULL = "never resolved").
     *
     * @return array<string, string|list<string>|null>
     */
    public function resolvedMediaUrls(): array;

    /** Recompute and persist the URL columns — writes only when a value changed. */
    public function syncResolvedMediaUrls(): void;
}
