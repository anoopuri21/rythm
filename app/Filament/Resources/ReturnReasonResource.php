<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnReasonResource\Pages;
use App\Models\ReturnReason;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ReturnReasonResource extends Resource
{
    protected static ?string $model = ReturnReason::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';
    protected static string|\UnitEnum|null $navigationGroup = 'SETTINGS';
    protected static ?string $navigationLabel = 'Return reasons';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(120)->unique(ignoreRecord: true),
            Textarea::make('customer_guidance')
                ->label('Customer guidance')
                ->maxLength(2000)
                ->helperText('Publish only approved return guidance. Do not add unsupported promises.'),
            Toggle::make('is_active')->label('Available to customers')->default(false),
            TextInput::make('sort_order')->integer()->minValue(0)->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('customer_guidance')->limit(80)->placeholder('—'),
                IconColumn::make('is_active')->boolean()->label('Available'),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnReasons::route('/'),
            'create' => Pages\CreateReturnReason::route('/create'),
            'edit' => Pages\EditReturnReason::route('/{record}/edit'),
        ];
    }
}
