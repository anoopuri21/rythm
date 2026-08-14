<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\SeoFields;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'CONTENT';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Page editor')->tabs([
                Tabs\Tab::make('Content')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        TextInput::make('slug')
                            ->label('URL slug')
                            ->maxLength(120)
                            ->helperText('The page opens at this URL (e.g. "terms" → /terms). Empty = homepage SEO entry. Reserved route slugs are blocked.')
                            ->rules([
                                'nullable',
                                'regex:/^[a-z0-9\-_]+$/',
                                function (string $attribute, mixed $value, \Closure $fail): void {
                                    if ($value !== null && in_array($value, Page::RESERVED_SLUGS, true)) {
                                        $record = \Filament\Facades\Filament::getRecord();
                                        // Allow editing the seeded SEO anchor (e.g. slug 'shop');
                                        // block creating a NEW page that would shadow a route.
                                        if ($record === null || $record->slug !== $value) {
                                            $fail('This slug is reserved by a system route.');
                                        }
                                    }
                                },
                            ])
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state === '' || $state === null ? null : $state),
                        TextInput::make('title')->required()->maxLength(255),
                        Select::make('template')
                            ->options(array_combine(Page::TEMPLATES, array_map('ucfirst', Page::TEMPLATES)))
                            ->default('generic'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_active')->default(true),
                        TiptapEditor::make('content')
                            ->label('Page content')
                            ->profile('default')
                            ->columnSpanFull()
                            ->helperText('Body of the page — shown inside the selected template.'),
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
                TextColumn::make('slug')
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '(home)' : '/'.$state)
                    ->badge()
                    ->color(fn (?string $state): string => $state === null ? 'warning' : 'gray')
                    ->sortable(),
                TextColumn::make('title')->searchable()->sortable()->limit(40),
                TextColumn::make('template')->badge()->color('gray'),
                TextColumn::make('updated_at')->dateTime('d M Y')->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePages::route('/'),
        ];
    }
}
