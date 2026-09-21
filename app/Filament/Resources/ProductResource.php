<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\SeoFields;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Brand;
use App\Models\Category;
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
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
                            ->description('Fill required fields top-to-bottom, then Variants → Media → SEO. Save once — use “View on storefront” after save.')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')->required()->maxLength(255)->columnSpanFull()
                                    ->helperText('Customer-facing title (single H1 on the product page).'),
                                TextInput::make('slug')->required()->maxLength(255)
                                    ->helperText('URL path under /product/{slug}. Keep stable after publish.'),
                                TextInput::make('sku')->required()->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Unique stock-keeping code for this base product.'),
                                Select::make('category_id')->relationship('category', 'name')
                                    ->searchable()->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required()->maxLength(120),
                                        TextInput::make('slug')->maxLength(140)
                                            ->helperText('Optional — auto from name if blank.'),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $name = trim((string) ($data['name'] ?? ''));
                                        $slug = trim((string) ($data['slug'] ?? ''));
                                        if ($slug === '') {
                                            $slug = Str::slug($name);
                                        }
                                        $category = Category::query()->create([
                                            'name' => $name,
                                            'slug' => $slug !== '' ? $slug : Str::lower(Str::random(8)),
                                            'is_active' => true,
                                            'sort_order' => 0,
                                        ]);

                                        return (int) $category->id;
                                    })
                                    ->helperText('Required for shop filters. Create inline if missing.'),
                                Select::make('brand_id')->relationship('brand', 'name')
                                    ->searchable()->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required()->maxLength(120),
                                        TextInput::make('slug')->maxLength(140)
                                            ->helperText('Optional — auto from name if blank.'),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $name = trim((string) ($data['name'] ?? ''));
                                        $slug = trim((string) ($data['slug'] ?? ''));
                                        if ($slug === '') {
                                            $slug = Str::slug($name);
                                        }
                                        $brand = Brand::query()->create([
                                            'name' => $name,
                                            'slug' => $slug !== '' ? $slug : Str::lower(Str::random(8)),
                                            'is_active' => true,
                                            'sort_order' => 0,
                                        ]);

                                        return (int) $brand->id;
                                    })
                                    ->helperText('Manufacturer label only (not a marketplace seller).'),
                                TextInput::make('price')->numeric()->required()->minValue(0)->prefix('₹')
                                    ->helperText('Base selling price (variants may override).'),
                                TextInput::make('compare_at_price')->numeric()->minValue(0)->prefix('₹')
                                    ->helperText('Optional MRP — strikethrough when higher than selling price.'),
                                TextInput::make('stock')->numeric()->required()->default(0)->minValue(0)
                                    ->helperText('Used when the product has no active variants. With variants, each option has its own stock.'),
                                TextInput::make('low_stock_threshold')->numeric()->default(5)->minValue(0),
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->disabled(fn (?Product $record): bool => $record?->importSource !== null)
                                    ->helperText('Off = hidden from shop. Imported rows use Approve & activate instead.'),
                                Toggle::make('is_featured'),
                                Toggle::make('is_trending')->label('Trending (homepage carousel)'),
                                TextInput::make('featured_rank')->numeric()->minValue(0)
                                    ->label('Featured rank')
                                    ->helperText('Lower number appears first in homepage Best Sellers (0 = first).'),
                                Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull()
                                    ->helperText('One or two lines under the title / for cards.'),
                                RichEditor::make('description')->maxLength(100000)->columnSpanFull()
                                    ->helperText('Full product story — original copy only.'),
                            ]),
                        Section::make('GST')
                            ->description('Leave blank unless you have an HSN code and GST rate for this product.')
                            ->columns(3)
                            ->schema([
                                TextInput::make('hsn_code')->label('HSN code')->maxLength(20),
                                TextInput::make('tax_classification')->label('Tax class')->maxLength(80),
                                TextInput::make('tax_rate')
                                    ->label('GST rate (%)')
                                    ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                                    ->helperText('Overrides the store default GST rate for this product.'),
                            ]),
                        Section::make('Variants')
                            ->description('Optional sellable options — each row has its own price, stock, colour, specs and images. Leave empty for simple single-SKU products.')
                            ->collapsible()
                            ->schema([
                                Repeater::make('variants')
                                    ->relationship()
                                    ->defaultItems(0)
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->mutateRelationshipDataBeforeFillUsing(fn (array $data): array => self::expandVariantFormData($data))
                                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::collapseVariantFormData($data))
                                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::collapseVariantFormData($data))
                                    ->schema([
                                        Grid::make(6)->schema([
                                            TextInput::make('name')->required()->label('Variant name')
                                                ->helperText('e.g. Sunburst · 6-string'),
                                            TextInput::make('sku')->required()->unique(ignoreRecord: true),
                                            TextInput::make('price_override')->numeric()->minValue(0)->prefix('₹')
                                                ->label('Price override (optional)')
                                                ->helperText('Blank = use product base price'),
                                            TextInput::make('stock')->numeric()->default(0)->minValue(0)
                                                ->helperText('Stock for this option only'),
                                            Toggle::make('is_active')->default(true),
                                            TextInput::make('color_name')->label('Color name')
                                                ->maxLength(80)
                                                ->dehydrated(false)
                                                ->helperText('Shown on storefront swatches'),
                                            ColorPicker::make('color_hex')->label('Color swatch')
                                                ->hex()
                                                ->dehydrated(false),
                                            KeyValue::make('specs')
                                                ->label('Other specs')
                                                ->keyLabel('Spec')
                                                ->valueLabel('Value')
                                                ->reorderable()
                                                ->dehydrated(false)
                                                ->helperText('e.g. Finish → Gloss, Scale → 25.5″ (optional)')
                                                ->columnSpanFull(),
                                            SpatieMediaLibraryFileUpload::make('variant_images')
                                                ->label('Variant images')
                                                ->collection('variant_gallery')
                                                ->multiple()
                                                ->image()
                                                ->maxFiles(6)
                                                ->maxSize(5120)
                                                ->helperText('Max 6 images — storefront swaps gallery when this option is selected. Falls back to product gallery if empty.')
                                                ->columnSpanFull(),
                                        ]),
                                    ])
                                    ->columns(1),
                            ]),
                        Section::make('Media')
                            ->description('Default product gallery (used when a variant has no images of its own).')
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
                TernaryFilter::make('is_active')->label('Published'),
                TernaryFilter::make('is_featured'),
                TernaryFilter::make('is_trending'),
                Filter::make('out_of_stock')
                    ->label('Out of stock (base)')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', 0)),
                Filter::make('has_variants')
                    ->label('Has variants')
                    ->query(fn (Builder $query): Builder => $query->whereHas('variants')),
                Filter::make('no_gallery')
                    ->label('Missing gallery image')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                        'media',
                        fn (Builder $media): Builder => $media->where('collection_name', 'gallery'),
                    )),
                Filter::make('imported_pending')
                    ->label('Imported — pending activation')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_active', false)
                        ->whereHas('importSource')),
            ])
            ->actions([
                Action::make('view_storefront')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Product $record): string => route('product.show', $record), shouldOpenInNewTab: true)
                    ->visible(fn (Product $record): bool => filled($record->slug) && $record->is_active),
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

    /**
     * Expand options JSON into admin-only colour/spec fields for the variant repeater.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function expandVariantFormData(array $data): array
    {
        $options = $data['options'] ?? [];
        if (is_string($options)) {
            $decoded = json_decode($options, true);
            $options = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($options)) {
            $options = [];
        }

        $data['color_name'] = isset($options['color']) && is_string($options['color']) ? $options['color'] : null;
        $hex = $options['color_hex'] ?? null;
        $data['color_hex'] = is_string($hex) && preg_match('/^#([A-Fa-f0-9]{6})$/', $hex) === 1 ? $hex : null;

        $specs = $options;
        unset($specs['color'], $specs['color_hex']);
        $data['specs'] = $specs;

        return $data;
    }

    /**
     * Collapse admin colour/spec fields back into options JSON before variant save.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function collapseVariantFormData(array $data): array
    {
        $options = [];

        $colorName = trim((string) ($data['color_name'] ?? ''));
        if ($colorName !== '') {
            $options['color'] = $colorName;
        }

        $hex = trim((string) ($data['color_hex'] ?? ''));
        if ($hex !== '' && preg_match('/^#([A-Fa-f0-9]{6})$/', $hex) === 1) {
            $options['color_hex'] = strtoupper($hex);
        }

        $specs = $data['specs'] ?? [];
        if (is_array($specs)) {
            foreach ($specs as $key => $value) {
                $key = trim((string) $key);
                if ($key === '' || in_array($key, ['color', 'color_hex'], true)) {
                    continue;
                }
                if ($value === null || $value === '') {
                    continue;
                }
                $options[$key] = is_scalar($value) ? (string) $value : $value;
            }
        }

        unset($data['color_name'], $data['color_hex'], $data['specs']);
        $data['options'] = $options === [] ? null : $options;

        return $data;
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
