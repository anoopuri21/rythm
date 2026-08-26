<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Support\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class StaffResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|\UnitEnum|null $navigationGroup = 'SECURITY';

    protected static ?string $navigationLabel = 'Staff access';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAdminPermission(AdminAccess::STAFF_MANAGE) ?? false;
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return self::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
            User::ROLE_CATALOGUE_MANAGER,
            User::ROLE_ORDER_MANAGER,
            User::ROLE_SUPPORT,
            User::ROLE_MARKETING,
            User::ROLE_FINANCE,
        ]);
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
            Select::make('role')->required()->options([
                User::ROLE_SUPER_ADMIN => 'Super Admin',
                User::ROLE_CATALOGUE_MANAGER => 'Catalogue Manager',
                User::ROLE_ORDER_MANAGER => 'Order Manager',
                User::ROLE_SUPPORT => 'Support',
                User::ROLE_MARKETING => 'Marketing',
                User::ROLE_FINANCE => 'Finance',
            ]),
            TextInput::make('password')
                ->password()
                ->revealable(false)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(12)
                ->maxLength(255),
            Textarea::make('audit_reason')
                ->label('Reason for access assignment/change')
                ->required()
                ->minLength(5)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->sortable(),
            TextColumn::make('role')->badge()->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
            IconColumn::make('app_authentication_secret')->label('TOTP')->boolean()->state(fn (User $record): bool => filled($record->getAppAuthenticationSecret())),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([
            EditAction::make(),
            Action::make('reset_totp')
                ->label('Reset TOTP')
                ->icon('heroicon-o-key')
                ->color('danger')
                ->visible(fn (User $record): bool => filled($record->getAppAuthenticationSecret()))
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('reason')->required()->minLength(5)->maxLength(500),
                ])
                ->action(function (User $record, array $data): void {
                    abort_unless(static::canEdit($record), 403);
                    $record->saveAppAuthenticationSecret(null);
                    $record->saveAppAuthenticationRecoveryCodes(null);
                    app(AdminAuditService::class)->record(
                        auth()->user(),
                        'staff.mfa_reset',
                        $record,
                        ['totp_enabled' => true],
                        ['totp_enabled' => false],
                        $data['reason'],
                    );
                }),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
