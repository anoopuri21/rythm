<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationDeliveryResource\Pages;
use App\Models\NotificationDelivery;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class NotificationDeliveryResource extends Resource
{
    protected static ?string $model = NotificationDelivery::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static string|\UnitEnum|null $navigationGroup = 'OPERATIONS';

    protected static ?string $navigationLabel = 'Notification deliveries';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event.event_type')->label('Event')->searchable(),
                TextColumn::make('channel')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    NotificationDelivery::STATUS_SENT => 'success',
                    NotificationDelivery::STATUS_FAILED => 'danger',
                    NotificationDelivery::STATUS_SUPPRESSED => 'gray',
                    default => 'warning',
                }),
                TextColumn::make('attempts')->sortable(),
                TextColumn::make('user.email')->label('Customer')->placeholder('Anonymous')->toggleable(),
                TextColumn::make('last_error')->limit(60)->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    NotificationDelivery::STATUS_QUEUED => 'Queued',
                    NotificationDelivery::STATUS_SENT => 'Sent',
                    NotificationDelivery::STATUS_FAILED => 'Failed',
                    NotificationDelivery::STATUS_SUPPRESSED => 'Suppressed',
                ]),
                SelectFilter::make('channel')->options(['mail' => 'Mail', 'database' => 'Database']),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListNotificationDeliveries::route('/')];
    }
}
