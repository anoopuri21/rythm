<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Payment\RazorpayGateway;
use App\Services\OrderService;
use App\Services\RefundService;
use App\Support\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMERCE';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function canCreate(): bool
    {
        return false; // orders are created by checkout only
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Order')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('order_number')->label('Order no')->copyable()->weight('bold'),
                        TextEntry::make('customer')->state(fn (Order $record): string => $record->user?->name ?? ($record->shipping_address['name'] ?? '—')),
                        TextEntry::make('email'),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                            'delivered', 'confirmed' => 'success',
                            'shipped', 'processing' => 'info',
                            'pending' => 'warning',
                            'cancelled', 'refunded' => 'danger',
                            default => 'gray',
                        }),
                        TextEntry::make('payment_status')->badge()->color(fn (string $state): string => $state === 'paid' ? 'success' : 'danger'),
                        TextEntry::make('placed_at')->dateTime('d M Y, h:i A'),
                        TextEntry::make('total')->money('INR')->weight('bold')->size(TextSize::Large),
                    ]),
                Section::make('Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('sku'),
                                TextEntry::make('options.finish')->label('Option')->placeholder('—'),
                                TextEntry::make('qty'),
                                TextEntry::make('unit_price')->money('INR'),
                                TextEntry::make('total')->money('INR')->weight('bold'),
                            ])
                            ->columns(6),
                    ]),
                Section::make('Addresses')
                    ->columns(2)
                    ->schema([
                        Section::make('Shipping')->schema([
                            TextEntry::make('shipping_address.name')->label('Name'),
                            TextEntry::make('shipping_address.phone')->label('Phone'),
                            TextEntry::make('shipping_address.line1')->label('Address'),
                            TextEntry::make('shipping_address.city')->label('City'),
                            TextEntry::make('shipping_address.state')->label('State'),
                            TextEntry::make('shipping_address.pincode')->label('PIN'),
                        ]),
                        Section::make('Payment')->schema([
                            TextEntry::make('payment_transaction')->label('Gateway payment id')
                                ->state(fn (Order $record): ?string => $record->payments->last()?->gateway_payment_id),
                            TextEntry::make('payment_gateway')->label('Gateway')
                                ->state(fn (Order $record): string => $record->payments->last()?->gateway ?? '—'),
                        ]),
                    ]),
                Section::make('Payment activity')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->schema([
                                TextEntry::make('amount')->money('INR'),
                                TextEntry::make('currency'),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('gateway_order_id')->label('Gateway order')->placeholder('—'),
                                TextEntry::make('gateway_payment_id')->label('Gateway payment')->placeholder('—'),
                                TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
                            ])
                            ->columns(6),
                        RepeatableEntry::make('paymentEvents')
                            ->label('Webhook events')
                            ->schema([
                                TextEntry::make('event_type'),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('gateway_event_id')->label('Provider event'),
                                TextEntry::make('failure_message')->placeholder('—'),
                                TextEntry::make('received_at')->dateTime('d M Y, h:i A'),
                            ])
                            ->columns(5),
                    ]),
                Section::make('Refunds')
                    ->schema([
                        RepeatableEntry::make('refunds')
                            ->schema([
                                TextEntry::make('amount')->money('INR'),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('reason'),
                                TextEntry::make('gateway_refund_id')->placeholder('Not processed'),
                                TextEntry::make('processed_at')->dateTime('d M Y, h:i A')->placeholder('Pending'),
                            ])
                            ->columns(5),
                    ]),
                Section::make('Status history')
                    ->schema([
                        RepeatableEntry::make('statusHistory')
                            ->schema([
                                TextEntry::make('from')->placeholder('—'),
                                TextEntry::make('to')->badge(),
                                TextEntry::make('note')->placeholder('—'),
                                TextEntry::make('actor'),
                                TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable()->weight('bold')->fontFamily('mono'),
                TextColumn::make('customer')
                    ->state(fn (Order $record): string => $record->user?->name ?? ($record->shipping_address['name'] ?? '—'))
                    ->searchable(),
                TextColumn::make('email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')->money('INR')->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Items')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered', 'confirmed' => 'success',
                        'shipped', 'processing' => 'info',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_status')->badge()->color(fn (string $state): string => $state === 'paid' ? 'success' : 'danger'),
                TextColumn::make('placed_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('placed_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(collect(Order::STATUSES)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()),
                SelectFilter::make('payment_status')->options(collect(Order::PAYMENT_STATUSES)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()),
                TernaryFilter::make('is_paid')
                    ->label('Paid')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('payment_status', 'paid'),
                        false: fn (Builder $query): Builder => $query->where('payment_status', '!=', 'paid'),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                self::processPendingRefundAction(),
                self::refundAction(),
                self::statusAction(Order::STATUS_PROCESSING, 'Mark processing', 'heroicon-o-arrow-path', 'info'),
                self::statusAction(Order::STATUS_SHIPPED, 'Mark shipped', 'heroicon-o-truck', 'info'),
                self::statusAction(Order::STATUS_DELIVERED, 'Mark delivered', 'heroicon-o-check-circle', 'success'),
                self::statusAction(Order::STATUS_CANCELLED, 'Cancel order', 'heroicon-o-x-circle', 'danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([]),
            ]);
    }

    private static function processPendingRefundAction(): Action
    {
        return Action::make('process_pending_refund')
            ->label('Process pending refund')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(fn (Order $record): bool => (auth()->user()?->hasAdminPermission(AdminAccess::FINANCE_MANAGE) ?? false)
                && $record->refunds()->where('status', Refund::STATUS_PENDING)->exists())
            ->requiresConfirmation()
            ->schema([
                Textarea::make('reason')
                    ->label('Approval note')
                    ->required()
                    ->minLength(5)
                    ->maxLength(500),
            ])
            ->action(function (Order $record, array $data): void {
                $user = auth()->user();

                if ($user === null) {
                    Notification::make()->danger()->title('Finance authorization is required.')->send();

                    return;
                }

                request()->merge(['audit_reason' => $data['reason']]);

                try {
                    $gateway = RazorpayGateway::resolve();
                    $refund = app(RefundService::class)->processPendingForOrder($record, $gateway, $user);

                    if ($refund->status === Refund::STATUS_REFUNDED) {
                        Notification::make()->success()->title('Pending refund processed')->send();
                    } elseif ($refund->status === Refund::STATUS_FAILED) {
                        Notification::make()->danger()->title('Refund was rejected; review the recorded failure before retrying.')->send();
                    } else {
                        Notification::make()->warning()->title('Provider completion is pending reconciliation')->send();
                    }
                } catch (\RuntimeException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                } catch (\Throwable) {
                    Notification::make()->warning()->title('Refund outcome requires reconciliation before retry.')->send();
                }
            });
    }

    private static function refundAction(): Action
    {
        return Action::make('request_refund')
            ->label('Refund')
            ->icon('heroicon-o-receipt-refund')
            ->color('danger')
            ->visible(fn (Order $record): bool => (auth()->user()?->hasAdminPermission(AdminAccess::FINANCE_MANAGE) ?? false)
                && in_array($record->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_REFUND_PENDING], true)
                && ! $record->refunds()->whereIn('status', [Refund::STATUS_PENDING, Refund::STATUS_PROCESSING])->exists())
            ->requiresConfirmation()
            ->schema([
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('₹')
                    ->minValue(0.01)
                    ->required(),
                Textarea::make('reason')
                    ->required()
                    ->minLength(5)
                    ->maxLength(500),
            ])
            ->action(function (Order $record, array $data): void {
                $user = auth()->user();
                $payment = $record->payments()
                    ->whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_REFUNDED])
                    ->latest('id')
                    ->first();

                if ($user === null || $payment === null) {
                    Notification::make()->danger()->title('A captured payment is required.')->send();

                    return;
                }

                request()->merge(['audit_reason' => $data['reason']]);

                try {
                    $service = app(RefundService::class);
                    $refund = $service->request($payment, (float) $data['amount'], $data['reason'], $user);
                    $gateway = RazorpayGateway::resolve();
                    $refund = $service->process($refund, $gateway, $user);
                    $notification = Notification::make()->title(
                        $refund->status === Refund::STATUS_REFUNDED
                                                    ? 'Refund processed'
                                                    : 'Refund submitted; provider completion is pending reconciliation'
                    );
                    ($refund->status === Refund::STATUS_REFUNDED ? $notification->success() : $notification->warning())->send();
                } catch (\RuntimeException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                } catch (\Throwable) {
                    Notification::make()->warning()->title('Refund outcome requires reconciliation before retry.')->send();
                }
            });
    }

    private static function statusAction(string $to, string $label, string $icon, string $color): Action
    {
        return Action::make('status_'.$to)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(fn (): bool => auth()->user()?->hasAdminPermission(AdminAccess::ORDERS_MANAGE) ?? false)
            ->requiresConfirmation()
            ->schema([
                Textarea::make('reason')->label('Reason / operational note')->required()->minLength(5)->maxLength(500),
            ])
            ->action(function (Order $record, array $data) use ($to): void {
                Gate::authorize('update', $record);
                request()->merge(['audit_reason' => $data['reason']]);

                try {
                    app(OrderService::class)->changeStatus($record, $to, $data['reason']);
                    Notification::make()->success()->title("Order marked {$to}")->send();
                } catch (\RuntimeException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                }
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
