<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageCategoryRowResource\Pages;
use App\Models\HomepageCategoryRow;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HomepageCategoryRowResource extends Resource
{
    protected static ?string $model = HomepageCategoryRow::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'HOMEPAGE';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Category rows';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->unique(ignoreRecord: true)
                ->required()
                ->helperText('Each category can appear in one homepage row.'),
            TextInput::make('title')
                ->maxLength(255)
                ->helperText('Optional public heading; category name is used when blank.'),
            TextInput::make('product_limit')
                ->label('Products')
                ->numeric()
                ->integer()
                ->minValue(4)
                ->maxValue(8)
                ->default(4)
                ->required()
                ->helperText('Bounded to 4–8 products per row.'),
            TextInput::make('sort_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            Toggle::make('is_active')
                ->label('Visible when category has active products')
                ->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->label('Category')->searchable()->sortable(),
                TextColumn::make('title')->placeholder('Uses category name')->limit(36),
                TextColumn::make('product_limit')->label('Products')->sortable(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->defaultSort('sort_order')
            ->filters([TernaryFilter::make('is_active')])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageHomepageCategoryRows::route('/')];
    }
}
