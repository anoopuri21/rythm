<?php

declare(strict_types=1);

namespace App\Filament\Columns;

use Filament\Tables\Columns\ImageColumn;

/**
 * Renders a media URL that is persisted in a DB column (docs/media-architecture.md → M-7).
 *
 * Filament's ImageColumn treats its state as a path **on a filesystem disk**
 * (`$disk->exists($state)` then `$disk->url($state)`), so a stored web URL like
 * `/storage/12/photo.webp` would be looked up as
 * `storage/app/public/storage/12/photo.webp` and silently render nothing.
 *
 * Our columns hold the exact host-relative URL the storefront uses (M-2), so
 * this column returns app-relative state as-is and only falls back to Filament's
 * disk behaviour for anything else (absolute URLs are handled by the parent).
 */
final class StoredMediaUrlColumn extends ImageColumn
{
    public function getImageUrl(?string $state = null): ?string
    {
        $state ??= $this->getState();
        $state = is_string($state) ? $state : null;

        if ($state !== null && str_starts_with($state, '/')) {
            return $state;
        }

        return parent::getImageUrl($state);
    }
}
