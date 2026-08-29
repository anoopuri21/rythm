<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnReasonResource\Pages;

use App\Filament\Resources\ReturnReasonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditReturnReason extends EditRecord
{
    protected static string $resource = ReturnReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
