<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Columns\StoredMediaUrlColumn;
use App\Filament\Components\MediaUpload;
use App\Filament\Resources\HeroBannerResource\Pages;
use App\Models\HeroBanner;
use App\Services\HeroBannerService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin overrides for the homepage banner slots.
 *
 * The built-in copy/image for every slot lives in
 * {@see HeroBannerService::DEFAULTS} and stays in the code. A row here only
 * replaces it: every blank field falls back to the built-in default, so
 * deleting a row restores the shipped banner instead of leaving a hole.
 */
class HeroBannerResource extends Resource
{
    protected static ?string $model = HeroBanner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'HOMEPAGE';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'slot';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Slot')
                ->description('Leave a field blank to keep the built-in default shown below it.')
                ->schema([
                    Select::make('slot')
                        ->label('Banner slot')
                        ->options(HeroBanner::SLOTS)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->selectablePlaceholder(false)
                        ->helperText('One row per slot. The slot decides where the banner renders.'),
                    Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Off = the built-in default is shown again.'),
                ])->columns(2),

            Section::make('Content')->schema([
                Textarea::make('title')
                    ->label('Title')
                    ->rows(2)
                    ->maxLength(160)
                    ->placeholder(fn (?HeroBanner $record) => self::defaultFor($record, 'title'))
                    ->helperText('Main line. A line break becomes a line break on the page. Blank = built-in default.'),
                TextInput::make('subtitle')
                    ->label('Subtitle / kicker')
                    ->maxLength(200)
                    ->placeholder(fn (?HeroBanner $record) => self::defaultFor($record, 'subtitle'))
                    ->helperText('Blank = built-in default.'),
                TextInput::make('cta_label')
                    ->label('Button label')
                    ->maxLength(80)
                    ->placeholder(fn (?HeroBanner $record) => self::defaultFor($record, 'cta_label'))
                    ->helperText('Blank = built-in default.'),
                TextInput::make('alt')
                    ->label('Image alt text')
                    ->maxLength(200)
                    ->placeholder(fn (?HeroBanner $record) => self::defaultFor($record, 'alt'))
                    ->helperText('Screen-reader text. Blank = built-in default.'),
                TextInput::make('href')
                    ->label('Link')
                    ->maxLength(300)
                    ->regex('/^\/(?!\/)[A-Za-z0-9\/_?&=%#.+-]*$/')
                    ->placeholder(fn (?HeroBanner $record) => self::defaultFor($record, 'href'))
                    ->helperText('Internal path only, beginning with one slash. A link with ?category= must point at an active category, otherwise it falls back to /shop.'),
            ])->columns(2),

            Section::make('Image')->schema([
                MediaUpload::single('image', 'image', maxSizeKb: 4096)
                    ->helperText('Optional. Blank = the built-in image for this slot is used.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                StoredMediaUrlColumn::make('image_url')->label('Image')->square(),
                TextColumn::make('slot')
                    ->label('Slot')
                    ->formatStateUsing(fn (string $state): string => HeroBanner::SLOTS[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('title')->label('Title')->limit(28)->default('— built-in —')->placeholder('— built-in —'),
                TextColumn::make('href')->label('Link')->limit(30)->placeholder('— built-in —'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('slot')
            ->actions([
                EditAction::make(),
                DeleteAction::make()->after(fn () => Notification::make()
                    ->title('Override removed')
                    ->body('The built-in default banner is shown again.')
                    ->success()
                    ->send()),
            ])
            ->emptyStateHeading('No overrides yet')
            ->emptyStateDescription('The homepage shows its built-in banners. Create a row to override one.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHeroBanners::route('/'),
        ];
    }

    /** The built-in default for the record's slot, used as the field placeholder. */
    private static function defaultFor(?Model $record, string $field): string
    {
        $slot = $record?->getAttribute('slot');

        return is_string($slot) ? (string) (HeroBannerService::DEFAULTS[$slot][$field] ?? '') : '';
    }
}
