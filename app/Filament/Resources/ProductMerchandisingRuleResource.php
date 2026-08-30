<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMerchandisingRuleResource\Pages;
use App\Models\Product;
use App\Models\ProductMerchandisingRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ProductMerchandisingRuleResource extends Resource
{
    protected static ?string $model = ProductMerchandisingRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'SHOP';

    protected static ?string $navigationLabel = 'Merchandising rules';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('source_product_id')
                ->label('Product')
                ->relationship('sourceProduct', 'name', fn (Builder $query): Builder => $query->where('is_active', true)->orderBy('name'))
                ->searchable()
                ->preload()
                ->required(),
            Select::make('target_product_id')
                ->label('Recommended product')
                ->relationship('targetProduct', 'name', fn (Builder $query): Builder => $query->where('is_active', true)->orderBy('name'))
                ->searchable()
                ->preload()
                ->different('source_product_id')
                ->required(),
            Select::make('rule_type')
                ->options([
                    ProductMerchandisingRule::TYPE_RELATED => 'Related',
                    ProductMerchandisingRule::TYPE_COMPLEMENTARY => 'Complementary',
                    ProductMerchandisingRule::TYPE_FREQUENTLY_BOUGHT_TOGETHER => 'Frequently bought together',
                ])
                ->required()
                ->helperText('Only curated product links are shown; prices and stock remain product-owned.'),
            TextInput::make('priority')->numeric()->minValue(0)->maxValue(65535)->default(0),
            Toggle::make('is_active')->label('Publish rule')->default(false),
            DateTimePicker::make('starts_at')->seconds(false),
            DateTimePicker::make('ends_at')->seconds(false)->afterOrEqual('starts_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sourceProduct.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('targetProduct.name')->label('Recommendation')->searchable()->sortable(),
                TextColumn::make('rule_type')->badge()->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),
                TextColumn::make('priority')->sortable(),
                TextColumn::make('starts_at')->dateTime('d M Y H:i')->placeholder('Immediately'),
                TextColumn::make('ends_at')->dateTime('d M Y H:i')->placeholder('No expiry'),
                ToggleColumn::make('is_active')->label('Published'),
            ])
            ->defaultSort('priority', 'desc')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductMerchandisingRules::route('/'),
        ];
    }
}
