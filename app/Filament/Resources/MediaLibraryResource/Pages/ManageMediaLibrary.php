<?php

declare(strict_types=1);

namespace App\Filament\Resources\MediaLibraryResource\Pages;

use App\Filament\Resources\MediaLibraryResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * Browse every stored image and reuse it elsewhere (docs/media-reuse.md).
 * Read-only listing: the header create button is deliberately absent — images
 * enter through the records that own them (MediaUpload), never from here (M-8).
 */
class ManageMediaLibrary extends ManageRecords
{
    protected static string $resource = MediaLibraryResource::class;

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
