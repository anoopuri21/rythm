<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'SHOP';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('slug')->required()->maxLength(120),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
            Textarea::make('description')->rows(3),
            SpatieMediaLibraryFileUpload::make('logo')
                ->collection('logo')->image()->maxFiles(1)->label('Brand logo')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(2048),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')->collection('logo')->circular(),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('products_count')->counts('products')->sortable()->label('Products'),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBrands::route('/'),
        ];
    }
}
