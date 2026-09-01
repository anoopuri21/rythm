<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\SeoFields;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'CONTENT';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
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
                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                    if ($value !== null && in_array($value, Page::RESERVED_SLUGS, true)) {
                                        $record = Filament::getRecord();
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
                            ->default('generic')
                            ->live()
                            ->helperText('About & Contact templates unlock a "Page design" tab with extra managed blocks.'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_active')->default(true),
                        RichEditor::make('content')->maxLength(200000)
                            ->label('Page content')
                            ->columnSpanFull()
                            ->helperText('Body of the page — shown inside the selected template.'),
                    ]),
                Tabs\Tab::make('Page design')
                    ->icon('heroicon-o-paint-brush')
                    ->visible(fn (Get $get): bool => in_array($get('template'), ['about', 'contact'], true))
                    ->schema([
                        // ——— ABOUT template blocks ———
                        Section::make('Hero')
                            ->visible(fn (Get $get): bool => $get('template') === 'about')
                            ->description('Dark hero banner at the top of the About page. The page title and rich-text content above are shown inside it.')
                            ->schema([
                                TextInput::make('settings.hero_kicker')
                                    ->label('Kicker (small line above the title)')
                                    ->placeholder('Our story')
                                    ->maxLength(80),
                            ]),
                        Section::make('Highlight stats')
                            ->visible(fn (Get $get): bool => $get('template') === 'about')
                            ->description('Row of stat cards under the hero. Leave empty to use the built-in defaults.')
                            ->schema([
                                Repeater::make('settings.stats')
                                    ->label('Stat cards')
                                    ->schema([
                                        TextInput::make('value')->label('Big value')->required()->maxLength(40)
                                            ->placeholder('Curated'),
                                        TextInput::make('label')->label('Small label')->required()->maxLength(60)
                                            ->placeholder('Instrument catalogue'),
                                    ])
                                    ->columns(2)
                                    ->maxItems(4)
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add stat card'),
                            ]),
                        Section::make('Our promise')
                            ->visible(fn (Get $get): bool => $get('template') === 'about')
                            ->description('Left column: heading, intro paragraph, checklist and CTA button. Right column: quote card.')
                            ->schema([
                                TextInput::make('settings.promise_kicker')->label('Kicker')->placeholder('Our promise')->maxLength(80),
                                TextInput::make('settings.promise_heading')->label('Heading')->maxLength(160)
                                    ->placeholder('A clearer way to explore musical instruments'),
                                Textarea::make('settings.promise_text')->label('Intro paragraph')->rows(3)->maxLength(600),
                                Repeater::make('settings.promise_points')
                                    ->label('Checklist points')
                                    ->simple(TextInput::make('point')->required()->maxLength(160))
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point'),
                                TextInput::make('settings.cta_label')->label('CTA button label')->placeholder('Explore the collection')->maxLength(60),
                                TextInput::make('settings.cta_url')->label('CTA link (optional)')
                                    ->placeholder('/shop')
                                    ->maxLength(500)
                                    ->helperText('Leave empty to link to the shop.'),
                                TextInput::make('settings.quote_emoji')->label('Quote card emoji')->placeholder('🎹')->maxLength(16),
                                Textarea::make('settings.quote_text')->label('Quote card text')->rows(2)->maxLength(400),
                            ])->columns(2),
                        Section::make('Values')
                            ->visible(fn (Get $get): bool => $get('template') === 'about')
                            ->description('Optional "What we stand for" cards at the bottom of the About page. Leave empty to hide the section.')
                            ->schema([
                                TextInput::make('settings.values_heading')->label('Section heading')->placeholder('What we stand for')->maxLength(120),
                                Repeater::make('settings.values')
                                    ->label('Value cards')
                                    ->schema([
                                        TextInput::make('icon')->label('Emoji')->maxLength(16)->placeholder('🎸'),
                                        TextInput::make('title')->label('Title')->required()->maxLength(80),
                                        Textarea::make('text')->label('Text')->rows(2)->maxLength(300),
                                    ])
                                    ->columns(3)
                                    ->maxItems(6)
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add value card'),
                            ]),

                        // ——— CONTACT template blocks ———
                        Section::make('Contact intro')
                            ->visible(fn (Get $get): bool => $get('template') === 'contact')
                            ->schema([
                                TextInput::make('settings.contact_kicker')
                                    ->label('Kicker (small line above the title)')
                                    ->placeholder("We're listening")
                                    ->maxLength(80),
                            ]),
                        Section::make('Contact info cards')
                            ->visible(fn (Get $get): bool => $get('template') === 'contact')
                            ->description('Cards shown beside the contact form (support, showroom, partnerships…). Leave empty to use the built-in defaults.')
                            ->schema([
                                Repeater::make('settings.cards')
                                    ->label('Info cards')
                                    ->schema([
                                        TextInput::make('icon')->label('Emoji')->maxLength(16)->placeholder('🎧'),
                                        TextInput::make('title')->label('Title')->required()->maxLength(80)->placeholder('Support & orders'),
                                        TextInput::make('line1')->label('Line 1 (highlighted)')->maxLength(120)->placeholder('support@rythme.store'),
                                        TextInput::make('line2')->label('Line 2')->maxLength(120)->placeholder('+91 98765 43210'),
                                        TextInput::make('line3')->label('Line 3 (small)')->maxLength(120)->placeholder('Mon–Sat, 10am–7pm IST'),
                                    ])
                                    ->columns(2)
                                    ->maxItems(5)
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add info card'),
                            ]),
                        Section::make('WhatsApp block')
                            ->visible(fn (Get $get): bool => $get('template') === 'contact')
                            ->schema([
                                Toggle::make('settings.whatsapp_enabled')
                                    ->label('Show WhatsApp block')
                                    ->default(true),
                                TextInput::make('settings.whatsapp_number')
                                    ->label('WhatsApp number')
                                    ->placeholder('+91 98765 43210')
                                    ->maxLength(30)
                                    ->helperText('Used to build the wa.me chat link.'),
                                TextInput::make('settings.whatsapp_title')->label('Title')->placeholder('Prefer WhatsApp?')->maxLength(80),
                                Textarea::make('settings.whatsapp_text')->label('Text')->rows(2)->maxLength(300),
                                TextInput::make('settings.whatsapp_button')->label('Button label')->placeholder('Chat on WhatsApp')->maxLength(60),
                            ])->columns(2),
                        Section::make('Map')
                            ->visible(fn (Get $get): bool => $get('template') === 'contact')
                            ->schema([
                                TextInput::make('settings.map_embed_url')
                                    ->label('Google Maps embed URL')
                                    ->url()
                                    ->maxLength(1000)
                                    ->placeholder('https://www.google.com/maps/embed?pb=…')
                                    ->helperText('Google Maps → Share → Embed a map → copy the iframe "src" URL. Only https://www.google.com/maps/embed URLs are rendered. Leave empty to hide the map.')
                                    ->rule('nullable')
                                    ->rule('starts_with:https://www.google.com/maps/embed'),
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
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePages::route('/'),
        ];
    }
}
