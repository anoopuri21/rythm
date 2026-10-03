<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Write mechanics for App\Models\Contracts\HasResolvedMediaUrls.
 *
 * Deliberately quiet (`forceFill` + `saveQuietly`):
 *  - these columns are a derived cache, so they must not appear in the admin
 *    audit log or trigger model observers (import-activation guards, homepage
 *    caches, …) — the media change that caused the sync is the auditable event;
 *  - `forceFill` keeps them out of mass assignment, so no request payload can
 *    ever set what the site displays.
 *
 * @see docs/media-architecture.md → M-7
 */
trait SyncsResolvedMediaUrls
{
    public function syncResolvedMediaUrls(): void
    {
        $changes = $this->resolvedMediaUrlChanges();

        if ($changes === []) {
            return;
        }

        // Timestamps stay untouched: a resolved-URL refresh is a cache write,
        // not a content edit. `updated_at` drives merchandising order (Trending
        // / Best Sellers / "recently updated"), so uploading a photo must not
        // reshuffle the storefront.
        $timestamps = $this->timestamps;
        $this->timestamps = false;

        try {
            $this->forceFill($changes)->saveQuietly();
        } finally {
            $this->timestamps = $timestamps;
        }
    }

    /**
     * Column => new value, for every column whose stored value is stale.
     *
     * Public because the sync command's `--dry-run` and the tests assert on it.
     *
     * @return array<string, string|list<string>|null>
     */
    public function resolvedMediaUrlChanges(): array
    {
        $changes = [];

        foreach ($this->resolvedMediaUrls() as $column => $value) {
            if ($this->getAttribute($column) !== $value) {
                $changes[$column] = $value;
            }
        }

        return $changes;
    }
}
