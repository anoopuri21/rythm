<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductQuestionResource\Pages;
use App\Models\ProductQuestion;
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

class ProductQuestionResource extends Resource
{
    protected static ?string $model = ProductQuestion::class;

    protected static ?string $navigationLabel = 'Product Q&A';

    protected static ?string $modelLabel = 'product question';

    protected static ?string $pluralModelLabel = 'product questions';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('product.name')->label('Product')->disabled(),
            TextInput::make('user.name')->label('Customer')->disabled(),
            Textarea::make('question')->rows(4)->disabled(),
            Select::make('status')->options([
                ProductQuestion::STATUS_PENDING => 'Pending',
                ProductQuestion::STATUS_APPROVED => 'Approved',
                ProductQuestion::STATUS_REJECTED => 'Rejected',
            ])->required(),
            Textarea::make('answer')
                ->required(fn ($get): bool => $get('status') === ProductQuestion::STATUS_APPROVED)
                ->maxLength(3000)
                ->rows(5)
                ->helperText('Approved questions require a public staff answer.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->searchable()->sortable()->limit(30),
                TextColumn::make('user.name')->label('Customer')->searchable()->placeholder('Deleted account'),
                TextColumn::make('question')->limit(60)->searchable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    ProductQuestion::STATUS_APPROVED => 'success',
                    ProductQuestion::STATUS_REJECTED => 'danger',
                    default => 'warning',
                })->sortable(),
                TextColumn::make('answered_at')->dateTime('d M Y')->placeholder('Not answered')->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    ProductQuestion::STATUS_PENDING => 'Pending',
                    ProductQuestion::STATUS_APPROVED => 'Approved',
                    ProductQuestion::STATUS_REJECTED => 'Rejected',
                ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductQuestions::route('/'),
        ];
    }
}
