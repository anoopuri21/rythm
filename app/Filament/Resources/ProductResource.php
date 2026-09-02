<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\SeoFields;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\ImportedProductActivationService;
use App\Support\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-musical-note';

    protected static string|\UnitEnum|null $navigationGroup = 'SHOP';

    protected static ?int $navigationSort = 1;

    /** Prevent N+1 on the list table (category/brand/gallery). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'brand', 'media', 'importSource']);
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Tabs::make('Product editor')->tabs([
                Tabs\Tab::make('Details')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
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
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->disabled(fn (?Product $record): bool => $record?->importSource !== null)
                                    ->helperText('Imported products use the reviewed activation action after real stock is entered.'),
                                Toggle::make('is_featured'),
                                Toggle::make('is_trending')->label('Trending (homepage carousel)'),
                                TextInput::make('featured_rank')->numeric()->minValue(0)
                                    ->label('Featured rank')
                                    ->helperText('Order in homepage Best Sellers (0 = first).'),
                                Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull(),
                                RichEditor::make('description')->maxLength(100000)->columnSpanFull(),
                            ]),
                        Section::make('Optional tax classification')
                            ->description('Leave blank unless approved product classification and rate values are available.')
                            ->columns(3)
                            ->schema([
                                TextInput::make('hsn_code')->label('HSN code')->maxLength(20),
                                TextInput::make('tax_classification')->maxLength(80),
                                TextInput::make('tax_rate')
                                    ->label('Approved tax rate (%)')
                                    ->numeric()->minValue(0)->maxValue(100)->suffix('%'),
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
                                        Grid::make(6)->schema([
                                            TextInput::make('name')->required()->label('Variant name'),
                                            TextInput::make('sku')->required()->unique(ignoreRecord: true),
                                            TextInput::make('price_override')->numeric()->minValue(0)->prefix('₹')
                                                ->label('Price override (optional)'),
                                            TextInput::make('stock')->numeric()->default(0)->minValue(0),
                                            Toggle::make('is_active')->default(true),
                                            SpatieMediaLibraryFileUpload::make('variant_images')
                                                ->label('Variant Images')
                                                ->collection('variant_gallery')
                                                ->multiple()
                                                ->image()
                                                ->maxFiles(6)
                                                ->maxSize(5120)
                                                ->helperText('Max 6 images per variant')
                                                ->columnSpanFull(),
                                        ]),
                                    ])
                                    ->columns(1),
                            ]),
                        Section::make('Media')
                            ->description('Product images — Bajaao product shots per image rules.')
                            ->collapsible()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->collection('gallery')->multiple()->image()->maxFiles(12)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                    ->maxSize(5120),
                                SpatieMediaLibraryFileUpload::make('og')
                                    ->collection('og')->image()->maxFiles(1)->label('Social share image')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(3072),
                            ]),
                    ]),
                Tabs\Tab::make('SEO')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->schema(SeoFields::schema()),
            ])->columnSpanFull(),
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
                TextColumn::make('hsn_code')->label('HSN')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_classification')->label('Tax class')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_rate')->label('Tax rate')->suffix('%')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('compare_at_price')->money('INR')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 5 ? 'danger' : 'success')
                    ->sortable(),
                IconColumn::make('is_featured')->boolean()->sortable()->label('Featured'),
                ToggleColumn::make('is_trending')->sortable()->label('Trending'),
                TextColumn::make('featured_rank')->sortable()->label('Rank')->toggleable(),
                ToggleColumn::make('is_active')
                    ->disabled(fn (Product $record): bool => $record->importSource !== null)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->searchable(),
                SelectFilter::make('brand')->relationship('brand', 'name')->searchable(),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
                TernaryFilter::make('is_trending'),
            ])
            ->actions([
                Action::make('approve_activate_import')
                    ->label('Approve & activate')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (Product $record): bool => ! $record->is_active
                        && $record->importSource !== null
                        && (auth()->user()?->hasAdminPermission(AdminAccess::CATALOGUE_MANAGE) ?? false))
                    ->requiresConfirmation()
                    ->schema(self::activationSchema())
                    ->action(function (Product $record, array $data): void {
                        app(ImportedProductActivationService::class)->approveAndActivate($record, auth()->user(), $data['reason']);
                        Notification::make()->success()->title('Imported product reviewed and activated')->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_activate_imports')
                        ->label('Approve & activate imported products')
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->schema(self::activationSchema())
                        ->action(function (Collection $records, array $data): void {
                            if ($records->count() > 20) {
                                throw new \RuntimeException('Activate no more than 20 reviewed products at a time.');
                            }
                            foreach ($records as $record) {
                                app(ImportedProductActivationService::class)->approveAndActivate($record, auth()->user(), $data['reason']);
                            }
                            Notification::make()->success()->title($records->count().' imported products activated')->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function activationSchema(): array
    {
        return [
            Checkbox::make('content_verified')
                ->label('Description and title contain no unsupported retailer promises')
                ->accepted()
                ->required(),
            Checkbox::make('price_stock_verified')
                ->label('Price and real Rythme stock have been verified')
                ->accepted()
                ->required(),
            Checkbox::make('media_rights_verified')
                ->label('Local product media is approved for Rythme commercial use')
                ->accepted()
                ->required(),
            Textarea::make('reason')
                ->label('Activation reason / review note')
                ->required()
                ->minLength(5)
                ->maxLength(500),
        ];
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
