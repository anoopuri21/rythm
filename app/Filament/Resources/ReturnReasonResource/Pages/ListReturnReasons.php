<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnReasonResource\Pages;

use App\Filament\Resources\ReturnReasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListReturnReasons extends ListRecords
{
    protected static string $resource = ReturnReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
