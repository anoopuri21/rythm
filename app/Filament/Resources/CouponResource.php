<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('code')->required()->maxLength(50)
                // `->uppercase()` does not exist in the locked Filament version;
                // uppercase visually via CSS and normalise the state on save
                // (the Coupon model mutator also uppercases as a safety net).
                ->extraInputAttributes(['class' => 'uppercase'])
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : $state)
                ->unique(ignoreRecord: true)
                ->helperText('Customer enters this code at checkout. Saved in UPPERCASE.'),
            Select::make('type')->options([
                Coupon::TYPE_PERCENT => 'Percent (%)',
                Coupon::TYPE_FIXED => 'Fixed (₹)',
            ])->required()->live(),
            TextInput::make('value')->numeric()->required()->minValue(0.01)
                ->maxValue(fn ($get): ?int => $get('type') === Coupon::TYPE_PERCENT ? 100 : null)
                ->helperText('Percent must be 0.01–100; fixed amount is in ₹.'),
            TextInput::make('min_order')->numeric()->default(0)->minValue(0)->prefix('₹'),
            TextInput::make('max_discount')->numeric()->nullable()->minValue(0.01)->prefix('₹')
                ->helperText('Max discount cap for percent coupons (optional).'),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('expires_at')->after('starts_at'),
            TextInput::make('max_uses')->numeric()->nullable()->minValue(1),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->badge()->color('gray')->searchable()->sortable()->fontFamily('mono'),
                TextColumn::make('type')->badge(),
                TextColumn::make('value')->label('Value')->formatStateUsing(fn (Coupon $record): string => $record->type === 'percent' ? $record->value.'%' : '₹'.$record->value),
                TextColumn::make('min_order')->money('INR'),
                TextColumn::make('used_count')->label('Uses')->suffix(fn (Coupon $record): string => $record->max_uses ? '/'.$record->max_uses : ''),
                TextColumn::make('expires_at')->dateTime('d M Y')->placeholder('Never')->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
                SelectFilter::make('type')->options([
                    Coupon::TYPE_PERCENT => 'Percent',
                    Coupon::TYPE_FIXED => 'Fixed',
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
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
