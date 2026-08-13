<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required()->disabled(),
            TextInput::make('user.name')->label('Customer')->disabled(),
            TextInput::make('product.name')->label('Product')->disabled(),
            Textarea::make('comment')->rows(4)->disabled(),
            Toggle::make('is_approved')->label('Approved'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->searchable()->sortable()->limit(30),
                TextColumn::make('user.name')->label('Customer')->searchable()->placeholder('Guest'),
                TextColumn::make('rating')->badge()->color(fn (int $state): string => match (true) {
                    $state >= 4 => 'success',
                    $state === 3 => 'warning',
                    default => 'danger',
                }),
                TextColumn::make('comment')->limit(50)->searchable(),
                IconColumn::make('is_approved')->boolean()->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved'),
                SelectFilter::make('rating')->options([1 => '1★', 2 => '2★', 3 => '3★', 4 => '4★', 5 => '5★']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReviews::route('/'),
        ];
    }
}
