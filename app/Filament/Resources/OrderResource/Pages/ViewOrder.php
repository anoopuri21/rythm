<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_invoice')
                ->label('Print invoice')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => URL::temporarySignedRoute('orders.invoice', now()->addMinutes(15), ['order' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }
}
