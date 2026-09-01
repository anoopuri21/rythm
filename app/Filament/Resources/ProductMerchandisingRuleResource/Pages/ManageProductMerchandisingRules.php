<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductMerchandisingRuleResource\Pages;

use App\Filament\Resources\ProductMerchandisingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

final class ManageProductMerchandisingRules extends ManageRecords
{
    protected static string $resource = ProductMerchandisingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
