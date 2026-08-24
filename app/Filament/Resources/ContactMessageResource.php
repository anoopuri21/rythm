<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'COMMUNICATION';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('phone')->disabled(),
            TextInput::make('subject')->disabled(),
            Textarea::make('message')->rows(5)->disabled(),
            Select::make('status')->options([
                'new' => 'New',
                'read' => 'Read',
                'replied' => 'Replied',
                'archived' => 'Archived',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(40)->placeholder('—'),
                TextColumn::make('message')->limit(60),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'read' => 'warning',
                    'replied' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContactMessages::route('/'),
        ];
    }
}
