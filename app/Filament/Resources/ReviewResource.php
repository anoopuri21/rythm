<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('rating')->numeric()->disabled(),
            TextInput::make('user.name')->label('Customer')->disabled(),
            TextInput::make('product.name')->label('Product')->disabled(),
            Textarea::make('comment')->rows(4)->disabled(),
            Select::make('status')->options([
                Review::STATUS_PENDING => 'Pending',
                Review::STATUS_APPROVED => 'Approved',
                Review::STATUS_REJECTED => 'Rejected',
            ])->required(),
            Textarea::make('merchant_reply')
                ->label('Merchant reply')
                ->maxLength(2000)
                ->rows(4)
                ->helperText('Published only when the review is approved.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->searchable()->sortable()->limit(30),
                TextColumn::make('user.name')->label('Customer')->searchable()->placeholder('Deleted account'),
                TextColumn::make('rating')->badge()->color(fn (int $state): string => match (true) {
                    $state >= 4 => 'success',
                    $state === 3 => 'warning',
                    default => 'danger',
                }),
                TextColumn::make('comment')->limit(50)->searchable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    Review::STATUS_APPROVED => 'success',
                    Review::STATUS_REJECTED => 'danger',
                    default => 'warning',
                })->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    Review::STATUS_PENDING => 'Pending',
                    Review::STATUS_APPROVED => 'Approved',
                    Review::STATUS_REJECTED => 'Rejected',
                ]),
                SelectFilter::make('rating')->options([1 => '1★', 2 => '2★', 3 => '3★', 4 => '4★', 5 => '5★']),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReviews::route('/'),
        ];
    }
}
