<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepageCategoryRowResource\Pages;

use App\Filament\Resources\HomepageCategoryRowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHomepageCategoryRows extends ManageRecords
{
    protected static string $resource = HomepageCategoryRowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add category row')];
    }
}
