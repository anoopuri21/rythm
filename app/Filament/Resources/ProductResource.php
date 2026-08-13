<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'SHOP';

    protected static ?int $navigationSort = 1;

    /** Prevent N+1 on the list table (category/brand/gallery). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'brand', 'media']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Product details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->required()->maxLength(255)
                        ->helperText('Leave blank to auto-generate from name.'),
                    TextInput::make('sku')->required()->maxLength(50)
                        ->unique(ignoreRecord: true),
                    Select::make('category_id')->relationship('category', 'name')
                        ->searchable()->preload(),
                    Select::make('brand_id')->relationship('brand', 'name')
                        ->searchable()->preload(),
                    TextInput::make('price')->numeric()->required()->minValue(0)->prefix('₹'),
                    TextInput::make('compare_at_price')->numeric()->minValue(0)->prefix('₹')
                        ->helperText('MRP — shown as strikethrough on the storefront'),
                    TextInput::make('stock')->numeric()->required()->default(0)->minValue(0),
                    TextInput::make('low_stock_threshold')->numeric()->default(5)->minValue(0),
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_featured'),
                    Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull(),
                    TiptapEditor::make('description')->profile('default')->columnSpanFull(),
                ]),
            Section::make('Variants')
                ->description('Optional — finishes, sizes or configurations.')
                ->collapsible()
                ->schema([
                    Repeater::make('variants')
                        ->relationship()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->schema([
                            Grid::make(5)->schema([
                                TextInput::make('name')->required()->label('Variant name'),
                                TextInput::make('sku')->required()->unique(ignoreRecord: true),
                                TextInput::make('price_override')->numeric()->minValue(0)->prefix('₹')
                                    ->label('Price override (optional)'),
                                TextInput::make('stock')->numeric()->default(0)->minValue(0),
                                Toggle::make('is_active')->default(true),
                            ]),
                        ]),
                ]),
            Section::make('Media')
                ->description('Product images — Bajaao product shots per image rules.')
                ->collapsible()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->collection('gallery')->multiple()->image()->maxFiles(12),
                    SpatieMediaLibraryFileUpload::make('og')
                        ->collection('og')->image()->maxFiles(1)->label('Social share image'),
                ]),
            Section::make('SEO')
                ->collapsible()->collapsed()
                ->columns(2)
                ->schema([
                    TextInput::make('meta_title')->maxLength(70),
                    Textarea::make('meta_description')->rows(2)->maxLength(160),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')->collection('gallery')->circular(),
                TextColumn::make('name')->searchable()->sortable()->limit(38),
                TextColumn::make('category.name')->badge()->color('gray'),
                TextColumn::make('brand.name')->badge()->color('gray'),
                TextColumn::make('price')->money('INR')->sortable(),
                TextColumn::make('compare_at_price')->money('INR')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 5 ? 'danger' : 'success')
                    ->sortable(),
                IconColumn::make('is_featured')->boolean()->sortable(),
                ToggleColumn::make('is_active')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->searchable(),
                SelectFilter::make('brand')->relationship('brand', 'name')->searchable(),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
