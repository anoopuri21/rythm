<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRequestResource\Pages;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\ReturnRequestService;
use App\Support\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';
    protected static ?string $navigationLabel = 'Return requests';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'request_number';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['order', 'customer', 'reason', 'refund']);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist->schema([
            Section::make('Return request')->columns(3)->schema([
                TextEntry::make('request_number')->copyable(),
                TextEntry::make('status')->badge(),
                TextEntry::make('order.order_number')->label('Order'),
                TextEntry::make('customer.name')->label('Customer'),
                TextEntry::make('customer.email')->label('Email'),
                TextEntry::make('reason_snapshot')->label('Reason'),
                TextEntry::make('customer_note')->columnSpanFull()->placeholder('No customer note'),
                TextEntry::make('approved_at')->dateTime()->placeholder('—'),
                TextEntry::make('received_at')->dateTime()->placeholder('—'),
                TextEntry::make('closed_at')->dateTime()->placeholder('—'),
                TextEntry::make('refund.status')->label('Linked refund')->badge()->placeholder('Not requested'),
                TextEntry::make('refund.amount')->label('Refund amount')->money('INR')->placeholder('—'),
            ]),
            Section::make('Requested items')->schema([
                RepeatableEntry::make('items')->schema([
                    TextEntry::make('orderItem.name')->label('Item'),
                    TextEntry::make('orderItem.sku')->label('SKU'),
                    TextEntry::make('quantity'),
                ])->columns(3),
            ]),
            Section::make('Audit history')->schema([
                RepeatableEntry::make('events')->schema([
                    TextEntry::make('from_status')->placeholder('—'),
                    TextEntry::make('to_status')->badge(),
                    TextEntry::make('reason'),
                    TextEntry::make('actor.name')->label('Actor')->placeholder('System'),
                    TextEntry::make('created_at')->dateTime(),
                ])->columns(5),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('request_number')->searchable()->copyable(),
                TextColumn::make('order.order_number')->label('Order')->searchable(),
                TextColumn::make('customer.email')->label('Customer')->searchable(),
                TextColumn::make('reason_snapshot')->label('Reason')->limit(40),
                TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(ReturnRequest::STATUSES)
                    ->mapWithKeys(fn (string $status): array => [$status => ucfirst(str_replace('_', ' ', $status))])->all()),
            ])
            ->actions([
                ViewAction::make(),
                self::pendingRefundAction(),
                self::transitionAction(ReturnRequest::STATUS_UNDER_REVIEW, 'Start review', 'warning'),
                self::transitionAction(ReturnRequest::STATUS_APPROVED, 'Approve logistics', 'success'),
                self::transitionAction(ReturnRequest::STATUS_REJECTED, 'Reject', 'danger'),
                self::transitionAction(ReturnRequest::STATUS_RECEIVED, 'Mark received', 'info'),
                self::transitionAction(ReturnRequest::STATUS_CLOSED, 'Close', 'gray'),
            ])
            ->bulkActions([]);
    }

    private static function pendingRefundAction(): Action
    {
        return Action::make('create_pending_refund')
            ->label('Create pending refund')
            ->color('warning')
            ->visible(fn (ReturnRequest $record): bool => (auth()->user()?->hasAdminPermission(AdminAccess::FINANCE_MANAGE) ?? false)
                && $record->refund_id === null
                && in_array($record->status, [ReturnRequest::STATUS_APPROVED, ReturnRequest::STATUS_RECEIVED], true))
            ->requiresConfirmation()
            ->schema([
                TextInput::make('amount')->numeric()->prefix('₹')->minValue(0.01)->required(),
                Textarea::make('reason')
                    ->label('Financial review reason')
                    ->required()
                    ->minLength(5)
                    ->maxLength(500)
                    ->helperText('This creates a pending Phase 8 refund only; it does not call the payment provider.'),
            ])
            ->action(function (ReturnRequest $record, array $data): void {
                $actor = auth()->user();
                if (! $actor instanceof User) {
                    Notification::make()->danger()->title('Finance authorization is required.')->send();
                    return;
                }
                request()->merge(['audit_reason' => $data['reason']]);
                try {
                    app(ReturnRequestService::class)->requestPendingRefund(
                        $record,
                        (float) $data['amount'],
                        $data['reason'],
                        $actor,
                    );
                    Notification::make()->success()->title('Pending refund linked for separate processing')->send();
                } catch (\RuntimeException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                }
            });
    }

    private static function transitionAction(string $status, string $label, string $color): Action
    {
        return Action::make('return_'.$status)
            ->label($label)
            ->color($color)
            ->visible(fn (ReturnRequest $record): bool => self::canTransition($record, $status))
            ->requiresConfirmation()
            ->schema([
                Textarea::make('reason')->label('Decision / operational reason')->required()->minLength(5)->maxLength(500),
            ])
            ->action(function (ReturnRequest $record, array $data) use ($status): void {
                $actor = auth()->user();
                if (! $actor instanceof User) {
                    Notification::make()->danger()->title('Staff authorization is required.')->send();
                    return;
                }
                request()->merge(['audit_reason' => $data['reason']]);
                try {
                    app(ReturnRequestService::class)->transition($record, $status, $data['reason'], $actor);
                    Notification::make()->success()->title('Return marked '.str_replace('_', ' ', $status))->send();
                } catch (\RuntimeException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                }
            });
    }

    private static function canTransition(ReturnRequest $record, string $to): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }
        $expected = match ($to) {
            ReturnRequest::STATUS_UNDER_REVIEW => [ReturnRequest::STATUS_REQUESTED],
            ReturnRequest::STATUS_APPROVED => [ReturnRequest::STATUS_UNDER_REVIEW],
            ReturnRequest::STATUS_REJECTED => [ReturnRequest::STATUS_REQUESTED, ReturnRequest::STATUS_UNDER_REVIEW],
            ReturnRequest::STATUS_RECEIVED => [ReturnRequest::STATUS_APPROVED],
            ReturnRequest::STATUS_CLOSED => [ReturnRequest::STATUS_RECEIVED],
            default => [],
        };
        $allowedRole = $to === ReturnRequest::STATUS_UNDER_REVIEW
            ? ($user->hasAdminPermission(AdminAccess::INTERACTIONS_MANAGE) || $user->hasAdminPermission(AdminAccess::ORDERS_MANAGE))
            : $user->hasAdminPermission(AdminAccess::ORDERS_MANAGE);

        return $allowedRole && in_array($record->status, $expected, true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnRequests::route('/'),
            'view' => Pages\ViewReturnRequest::route('/{record}'),
        ];
    }
}
