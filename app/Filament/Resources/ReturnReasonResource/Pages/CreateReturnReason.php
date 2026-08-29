<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReturnReasonResource\Pages;

use App\Filament\Resources\ReturnReasonResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReturnReason extends CreateRecord
{
    protected static string $resource = ReturnReasonResource::class;
}
