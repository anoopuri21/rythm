<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_invoice')
                ->label('Print invoice')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => route('orders.invoice', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
