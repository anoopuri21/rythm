<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('email_verified_at')->label('Verified')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('orders_count')->counts('orders')->label('Orders')->sortable(),
                TextColumn::make('total_spent')
                    ->label('Spent')
                    ->money('INR')
                    ->state(fn (User $record): float => (float) $record->orders()->where('payment_status', 'paid')->sum('total'))
                    ->sortable(),
                TextColumn::make('email_verified_at')->label('Verified')->dateTime('d M Y')->placeholder('No')->sortable(),
                TextColumn::make('created_at')->label('Joined')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('email_verified_at')->label('Email verified'),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
