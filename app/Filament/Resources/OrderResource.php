<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function canCreate(): bool
    {
        return false; // orders are created by checkout only
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('order_number')->label('Order no')->copyable()->weight('bold'),
                        TextEntry::make('customer')->state(fn (Order $record): string => $record->user?->name ?? ($record->shipping_address['name'] ?? '—')),
                        TextEntry::make('email'),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                            'delivered', 'confirmed' => 'success',
                            'shipped', 'processing' => 'info',
                            'pending' => 'warning',
                            'cancelled', 'refunded' => 'danger',
                            default => 'gray',
                        }),
                        TextEntry::make('payment_status')->badge()->color(fn (string $state): string => $state === 'paid' ? 'success' : 'danger'),
                        TextEntry::make('placed_at')->dateTime('d M Y, h:i A'),
                        TextEntry::make('total')->money('INR')->weight('bold')->size(TextEntry\TextEntrySize::Large),
                    ]),
                Section::make('Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('sku'),
                                TextEntry::make('options.finish')->label('Option')->placeholder('—'),
                                TextEntry::make('qty'),
                                TextEntry::make('unit_price')->money('INR'),
                                TextEntry::make('total')->money('INR')->weight('bold'),
                            ])
                            ->columns(6),
                    ]),
                Section::make('Addresses')
                    ->columns(2)
                    ->schema([
                        Group::make([
                            TextEntry::make('shipping_address.name')->label('Name'),
                            TextEntry::make('shipping_address.phone')->label('Phone'),
                            TextEntry::make('shipping_address.line1')->label('Address'),
                            TextEntry::make('shipping_address.city')->label('City'),
                            TextEntry::make('shipping_address.state')->label('State'),
                            TextEntry::make('shipping_address.pincode')->label('PIN'),
                        ])->label('Shipping'),
                        Group::make([
                            TextEntry::make('payment_transaction')->label('Gateway payment id')
                                ->state(fn (Order $record): ?string => $record->payments->last()?->gateway_payment_id),
                            TextEntry::make('payment_gateway')->label('Gateway')
                                ->state(fn (Order $record): string => $record->payments->last()?->gateway ?? '—'),
                        ])->label('Payment'),
                    ]),
                Section::make('Status history')
                    ->schema([
                        RepeatableEntry::make('statusHistory')
                            ->schema([
                                TextEntry::make('from')->placeholder('—'),
                                TextEntry::make('to')->badge(),
                                TextEntry::make('note')->placeholder('—'),
                                TextEntry::make('actor'),
                                TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable()->weight('bold')->fontFamily('mono'),
                TextColumn::make('customer')
                    ->state(fn (Order $record): string => $record->user?->name ?? ($record->shipping_address['name'] ?? '—'))
                    ->searchable(),
                TextColumn::make('email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')->money('INR')->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Items')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered', 'confirmed' => 'success',
                        'shipped', 'processing' => 'info',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_status')->badge()->color(fn (string $state): string => $state === 'paid' ? 'success' : 'danger'),
                TextColumn::make('placed_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('placed_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(collect(Order::STATUSES)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()),
                SelectFilter::make('payment_status')->options(collect(Order::PAYMENT_STATUSES)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()),
                TernaryFilter::make('is_paid')
                    ->label('Paid')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('payment_status', 'paid'),
                        false: fn (Builder $query): Builder => $query->where('payment_status', '!=', 'paid'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                self::statusAction(Order::STATUS_PROCESSING, 'Mark processing', 'heroicon-o-arrow-path', 'info'),
                self::statusAction(Order::STATUS_SHIPPED, 'Mark shipped', 'heroicon-o-truck', 'info'),
                self::statusAction(Order::STATUS_DELIVERED, 'Mark delivered', 'heroicon-o-check-circle', 'success'),
                self::statusAction(Order::STATUS_CANCELLED, 'Cancel order', 'heroicon-o-x-circle', 'danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    private static function statusAction(string $to, string $label, string $icon, string $color): Action
    {
        return Action::make('status_'.$to)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->action(function (Order $record) use ($to): void {
                try {
                    app(OrderService::class)->changeStatus($record, $to);
                    Notification::make()->success()->title("Order marked {$to}")->send();
                } catch (\RuntimeException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                }
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
