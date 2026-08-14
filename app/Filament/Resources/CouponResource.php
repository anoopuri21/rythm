<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->required()->maxLength(50)->uppercase()
                ->unique(ignoreRecord: true)
                ->helperText('Customer enters this code at checkout.'),
            Select::make('type')->options(['percent' => 'Percent (%)', 'fixed' => 'Fixed (₹)'])->required(),
            TextInput::make('value')->numeric()->required()->minValue(0)
                ->helperText('Percent value (1–100) or fixed amount in ₹.'),
            TextInput::make('min_order')->numeric()->default(0)->prefix('₹'),
            TextInput::make('max_discount')->numeric()->nullable()->prefix('₹')
                ->helperText('Max discount cap for percent coupons (optional).'),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('expires_at'),
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
                SelectFilter::make('type')->options(['percent' => 'Percent', 'fixed' => 'Fixed']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
