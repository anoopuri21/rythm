<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'SHOP';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('slug')->required()->maxLength(120),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
            Textarea::make('description')->rows(3),
            SpatieMediaLibraryFileUpload::make('logo')
                ->collection('logo')->image()->maxFiles(1)->label('Brand logo'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
