<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->withCount('items')->orderByDesc('created_at')->limit(8))
            ->columns([
                TextColumn::make('order_number')->weight('bold')->fontFamily('mono'),
                TextColumn::make('customer')->state(fn (Order $record): string => $record->user?->name ?? ($record->shipping_address['name'] ?? '—')),
                TextColumn::make('total')->money('INR'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime('d M Y, h:i A')->label('Placed'),
            ])
            ->paginated(false);
    }
}
