<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Support\SkuGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** SKU is optional in the form; generate one when left blank. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SkuGenerator::fillIfBlank($data);
    }
}
