<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductQuestionResource\Pages;

use App\Filament\Resources\ProductQuestionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProductQuestions extends ManageRecords
{
    protected static string $resource = ProductQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
