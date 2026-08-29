<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBlockResource\Pages;
use App\Models\HomepageBlock;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HomepageBlockResource extends Resource
{
    protected static ?string $model = HomepageBlock::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'HOMEPAGE';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('section_key')
                ->label('Section')
                ->options(HomepageBlock::SECTIONS)
                ->required()
                ->searchable()
                ->helperText('Which homepage section this item belongs to.'),
            TextInput::make('title')
                ->required()
                ->helperText('Testimonial name / story title / stat value ("12+") / USP heading / promo heading.'),
            TextInput::make('subtitle')->nullable()
                ->helperText('Role, promo kicker, or "other stores" text (comparison).'),
            Textarea::make('content')->rows(3)
                ->helperText('Quote, story excerpt, stat label, USP copy, or shop URL for promos.'),
            SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->maxFiles(1)
                ->helperText('Optional image (stories/UGC/promos).'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->square()->label('Image'),
                TextColumn::make('section_key')->badge()->sortable(),
                TextColumn::make('title')->searchable()->sortable()->weight('bold')->limit(34),
                TextColumn::make('content')->limit(44)->toggleable(),
                TextColumn::make('sort_order')->sortable()->label('Order'),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('section_key', 'asc')
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('section_key')->label('Section')->options(HomepageBlock::SECTIONS),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHomepageBlocks::route('/'),
        ];
    }
}
