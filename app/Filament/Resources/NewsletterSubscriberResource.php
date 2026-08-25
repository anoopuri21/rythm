<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMUNICATION';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('subscribed_at')->label('Subscribed')->dateTime('d M Y')->sortable(),
                TextColumn::make('created_at')->label('Added')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => response()->streamDownload(function (): void {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['email', 'subscribed_at']);
                        foreach (NewsletterSubscriber::cursor() as $sub) {
                            fputcsv($out, [$sub->email, $sub->subscribed_at?->toDateTimeString()]);
                        }
                        fclose($out);
                    }, 'newsletter-subscribers.csv')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
        ];
    }
}
