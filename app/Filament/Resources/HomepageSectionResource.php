<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageSectionResource\Pages;
use App\Models\HomepageSection;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'HOME';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('section_key')
                ->label('Section')
                ->required()
                ->options([
                    'categories' => 'Featured Categories',
                    'bestsellers' => 'Best Sellers',
                    'why-rythme' => 'Why Rythme',
                    'brands' => 'Brand Showcase',
                    'numbers' => 'Numbers',
                    'new-arrivals' => 'New Arrivals',
                    'deals' => 'Deals Banner',
                    'video-showcase' => 'Video Showcase',
                    'stories' => 'Latest Stories',
                    'testimonials' => 'Testimonials',
                    'comparison' => 'Comparison',
                    'ugc' => 'UGC Gallery',
                    'faq' => 'FAQ',
                ])
                ->searchable()
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->helperText('Which homepage section this content belongs to (locked after creation).'),
            TextInput::make('kicker')
                ->label('Kicker / eyebrow')
                ->maxLength(120)
                ->helperText('Small uppercase line above the title.'),
            TextInput::make('title')
                ->maxLength(255)
                ->helperText('Main heading — renders in the section title.'),
            TextInput::make('title_accent')
                ->label('Title accent')
                ->maxLength(255)
                ->helperText('The emphasised part of the heading (brand colour).'),
            TiptapEditor::make('content')
                ->label('Body content')
                ->profile('default')
                ->helperText('Long-form body shown in this section (optional).'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('section_key')->label('Section')->badge()->color('gray')->sortable(),
                TextColumn::make('kicker')->label('Kicker')->limit(28),
                TextColumn::make('title')->label('Title')->limit(32),
                TextColumn::make('sort_order')->sortable(),
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
            'index' => Pages\ManageHomepageSections::route('/'),
        ];
    }
}
