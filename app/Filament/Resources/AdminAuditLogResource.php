<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AdminAuditLogResource\Pages;
use App\Models\AdminAuditLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class AdminAuditLogResource extends Resource
{
    protected static ?string $model = AdminAuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'SECURITY';

    protected static ?string $navigationLabel = 'Audit log';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('actor.email')->label('Actor')->searchable(),
                TextColumn::make('action')->badge()->searchable(),
                TextColumn::make('subject_type')->label('Subject')->formatStateUsing(fn (?string $state): string => $state === null ? 'System' : class_basename($state)),
                TextColumn::make('subject_id')->label('ID'),
                TextColumn::make('reason')->limit(50)->placeholder('Not supplied'),
                TextColumn::make('request_id')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')->options(fn (): array => AdminAuditLog::query()->distinct()->orderBy('action')->pluck('action', 'action')->all()),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAdminAuditLogs::route('/')];
    }
}
