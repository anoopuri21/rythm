<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Support\SkuGenerator;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /** SKU is optional in the form; generate one if it was cleared. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SkuGenerator::fillIfBlank($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_storefront')
                ->label('View on storefront')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('product.show', $this->getRecord()), shouldOpenInNewTab: true)
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record instanceof Product
                        && filled($record->slug)
                        && $record->is_active;
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
