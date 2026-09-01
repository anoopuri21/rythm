<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
use App\Models\User;
use App\Services\FulfillmentService;
use App\Support\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'idempotency_key';

    public static function canCreate(): bool
    {
        return false; // allocations are created from the owning order action
    }

    public static function canDelete($record): bool
    {
        return false; // shipment history is an operational ledger
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['order', 'creator']);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist->schema([
            Section::make('Shipment')->columns(3)->schema([
                TextEntry::make('order.order_number')->label('Order'),
                TextEntry::make('status')->badge(),
                TextEntry::make('idempotency_key')->label('Fulfillment identity')->copyable(),
                TextEntry::make('carrier')->placeholder('Not assigned'),
                TextEntry::make('awb')->label('AWB / tracking reference')->placeholder('Not assigned')->copyable(),
                TextEntry::make('tracking_url')->url(fn (Shipment $record): ?string => $record->tracking_url)->openUrlInNewTab()->placeholder('Not assigned'),
                TextEntry::make('creator.name')->label('Created by')->placeholder('System'),
                TextEntry::make('dispatched_at')->dateTime()->placeholder('Not dispatched'),
                TextEntry::make('delivered_at')->dateTime()->placeholder('Not delivered'),
                TextEntry::make('note')->columnSpanFull()->placeholder('No internal note'),
            ]),
            Section::make('Allocated items')->schema([
                RepeatableEntry::make('items')->schema([
                    TextEntry::make('orderItem.name')->label('Item'),
                    TextEntry::make('orderItem.sku')->label('SKU'),
                    TextEntry::make('quantity'),
                ])->columns(3),
            ]),
            Section::make('Action history')->schema([
                RepeatableEntry::make('events')->schema([
                    TextEntry::make('from_status')->placeholder('—'),
                    TextEntry::make('to_status')->badge(),
                    TextEntry::make('reason'),
                    TextEntry::make('actor.name')->label('Actor')->placeholder('System'),
                    TextEntry::make('created_at')->dateTime(),
                ])->columns(5),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    Shipment::STATUS_DELIVERED => 'success',
                    Shipment::STATUS_DISPATCHED => 'info',
                    Shipment::STATUS_READY => 'warning',
                    Shipment::STATUS_CANCELLED => 'danger',
                    default => 'gray',
                })->sortable(),
                TextColumn::make('carrier')->searchable()->placeholder('—'),
                TextColumn::make('awb')->label('AWB / reference')->searchable()->placeholder('—'),
                TextColumn::make('dispatched_at')->dateTime()->placeholder('—')->toggleable(),
                TextColumn::make('delivered_at')->dateTime()->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(Shipment::STATUSES)->mapWithKeys(fn (string $status): array => [$status => ucfirst($status)])->all()),
            ])
            ->actions([
                ViewAction::make(),
                self::transitionAction(Shipment::STATUS_READY, 'Mark ready', 'warning'),
                self::dispatchAction(),
                self::transitionAction(Shipment::STATUS_DELIVERED, 'Mark delivered', 'success'),
                self::transitionAction(Shipment::STATUS_CANCELLED, 'Cancel shipment', 'danger'),
            ])
            ->bulkActions([BulkActionGroup::make([])]);
    }

    private static function transitionAction(string $status, string $label, string $color): Action
    {
        return Action::make('shipment_'.$status)
            ->label($label)
            ->color($color)
            ->visible(fn (Shipment $record): bool => self::canManage()
                && match ($status) {
                    Shipment::STATUS_READY => $record->status === Shipment::STATUS_DRAFT,
                    Shipment::STATUS_DELIVERED => $record->status === Shipment::STATUS_DISPATCHED,
                    Shipment::STATUS_CANCELLED => in_array($record->status, [Shipment::STATUS_DRAFT, Shipment::STATUS_READY], true),
                    default => false,
                })
            ->requiresConfirmation()
            ->schema([
                Textarea::make('reason')->required()->minLength(5)->maxLength(500),
            ])
            ->action(fn (Shipment $record, array $data) => self::runTransition($record, $status, $data['reason']));
    }

    private static function dispatchAction(): Action
    {
        return Action::make('shipment_dispatched')
            ->label('Dispatch')
            ->color('info')
            ->visible(fn (Shipment $record): bool => self::canManage() && $record->status === Shipment::STATUS_READY)
            ->requiresConfirmation()
            ->schema([
                TextInput::make('carrier')->required()->maxLength(100),
                TextInput::make('awb')->label('AWB / tracking reference')->maxLength(120),
                TextInput::make('tracking_url')->url()->maxLength(500),
                Textarea::make('reason')->required()->minLength(5)->maxLength(500),
            ])
            ->action(fn (Shipment $record, array $data) => self::runTransition($record, Shipment::STATUS_DISPATCHED, $data['reason'], [
                'carrier' => $data['carrier'],
                'awb' => $data['awb'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
            ]));
    }

    /** @param array<string, ?string> $details */
    private static function runTransition(Shipment $shipment, string $status, string $reason, array $details = []): void
    {
        $actor = auth()->user();
        if (! $actor instanceof User) {
            Notification::make()->danger()->title('Order-management authorization is required.')->send();
            return;
        }

        request()->merge(['audit_reason' => $reason]);

        try {
            app(FulfillmentService::class)->transition($shipment, $status, $reason, $actor, $details);
            Notification::make()->success()->title('Shipment marked '.$status)->send();
        } catch (\RuntimeException $exception) {
            Notification::make()->danger()->title($exception->getMessage())->send();
        }
    }

    private static function canManage(): bool
    {
        return auth()->user()?->hasAdminPermission(AdminAccess::ORDERS_MANAGE) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'view' => Pages\ViewShipment::route('/{record}'),
        ];
    }
}
