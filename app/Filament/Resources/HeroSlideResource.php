<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'HOMEPAGE';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Slide')->tabs([
                Tabs\Tab::make('Content')->schema([
                    TextInput::make('eyebrow')->maxLength(120)->placeholder('High quality · Best sellers'),
                    TextInput::make('title')->required()->maxLength(120)->columnSpan(2),
                    TextInput::make('accent')->maxLength(120)->helperText('The emphasised part (renders in gray).'),
                    TextInput::make('copy')->maxLength(500)->columnSpan(2)->placeholder('Short punchline for the slide.'),
                    TextInput::make('cta_label')->maxLength(60)->placeholder('Explore instruments'),
                    TextInput::make('cta_href')->maxLength(255)->placeholder('/shop'),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
                Tabs\Tab::make('Images')->schema([
                    SpatieMediaLibraryFileUpload::make('desktop_image')
                        ->collection('desktop_image')->image()->maxFiles(1)
                        ->helperText('Desktop (≥768px): large landscape banner, ~1500×800.'),
                    SpatieMediaLibraryFileUpload::make('mobile_image')
                        ->collection('mobile_image')->image()->maxFiles(1)
                        ->helperText('Mobile (<768px): portrait banner, ~900×1200.'),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('desktop_image')->collection('desktop_image')->square()->label('Image'),
                TextColumn::make('title')->searchable()->sortable()->weight('bold')->limit(30),
                TextColumn::make('eyebrow')->limit(24)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')->sortable()->label('Order'),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHeroSlides::route('/'),
        ];
    }
}
