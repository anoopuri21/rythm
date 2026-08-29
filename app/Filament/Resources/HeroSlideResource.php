<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'HOMEPAGE';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
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
                        ->collection('desktop_image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->maxFiles(1)
                        ->helperText('Desktop (≥768px): large landscape banner, ~1500×800.'),
                    SpatieMediaLibraryFileUpload::make('mobile_image')
                        ->collection('mobile_image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->maxFiles(1)
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
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHeroSlides::route('/'),
        ];
    }
}
