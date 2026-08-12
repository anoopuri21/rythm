<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\Select;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'SHOP';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('parent_id')
                ->label('Parent category')
                ->options(fn () => Category::query()
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->pluck('name', 'id'))
                ->placeholder('None (top-level)')
                ->searchable(),
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('slug')->required()->maxLength(120),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
            Textarea::make('description')->rows(3),
            SpatieMediaLibraryFileUpload::make('icon')
                ->collection('icon')->image()->maxFiles(1)->label('Category icon'),
            TextInput::make('seo_title')->maxLength(70),
            Textarea::make('seo_description')->rows(2)->maxLength(160),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('icon')->collection('icon')->circular(),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('parent.name')->placeholder('—')->label('Parent'),
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
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
