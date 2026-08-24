<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepageBlockResource\Pages;

use App\Filament\Resources\HomepageBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHomepageBlocks extends ManageRecords
{
    protected static string $resource = HomepageBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New block'),
        ];
    }
}
