<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Table('orders')]
#[Fillable(['order_number', 'idempotency_key', 'user_id', 'email', 'status', 'payment_status', 'subtotal', 'discount', 'coupon_code', 'coupon_usage_recorded_at', 'coupon_usage_released_at', 'shipping_fee', 'tax', 'total', 'currency', 'shipping_address', 'billing_address', 'notes', 'placed_at'])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = OrderStatus::Pending->value;

    public const STATUS_CONFIRMED = OrderStatus::Confirmed->value;

    public const STATUS_PROCESSING = OrderStatus::Processing->value;

    public const STATUS_SHIPPED = OrderStatus::Shipped->value;

    public const STATUS_DELIVERED = OrderStatus::Delivered->value;

    public const STATUS_CANCELLED = OrderStatus::Cancelled->value;

    public const STATUS_REFUNDED = OrderStatus::Refunded->value;

    public const PAYMENT_UNPAID = OrderPaymentStatus::Unpaid->value;

    public const PAYMENT_AUTHORIZED = OrderPaymentStatus::Authorized->value;

    public const PAYMENT_PAID = OrderPaymentStatus::Paid->value;

    public const PAYMENT_FAILED = OrderPaymentStatus::Failed->value;

    public const PAYMENT_REFUND_PENDING = OrderPaymentStatus::RefundPending->value;

    public const PAYMENT_REFUNDED = OrderPaymentStatus::Refunded->value;

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID,
        self::PAYMENT_AUTHORIZED,
        self::PAYMENT_PAID,
        self::PAYMENT_FAILED,
        self::PAYMENT_REFUND_PENDING,
        self::PAYMENT_REFUNDED,
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'placed_at' => 'datetime',
        'coupon_usage_recorded_at' => 'datetime',
        'coupon_usage_released_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Ordered tracking steps for the user timeline — each with a
     * human label, description and the timestamp it was reached.
     *
     * @return array<int, array{key:string, label:string, desc:string, done:bool, at:?Carbon}>
     */
    public function trackingTimeline(): array
    {
        $reached = collect($this->statusHistory)
            ->mapWithKeys(fn ($entry): array => [
                ($entry->to === self::STATUS_PENDING ? 'placed' : $entry->to) => $entry->created_at,
            ]);

        $steps = [
            'placed' => ['label' => 'Order placed', 'desc' => 'We have received your order.'],
            'confirmed' => ['label' => 'Payment confirmed', 'desc' => 'Your payment was captured and the order is confirmed.'],
            'processing' => ['label' => 'Processing', 'desc' => 'Your instruments are being packed with care.'],
            'shipped' => ['label' => 'Shipped', 'desc' => 'Your order is on its way to your address.'],
            'delivered' => ['label' => 'Delivered', 'desc' => 'Enjoy your new sound!'],
        ];

        $timeline = [];

        foreach ($steps as $key => $step) {
            $timeline[] = [
                'key' => $key,
                'label' => $step['label'],
                'desc' => $step['desc'],
                'done' => $reached->has($key) || $this->isPastStep($key, $reached),
                'at' => $reached->get($key),
            ];
        }

        if ($this->isCancelled()) {
            $timeline[] = [
                'key' => 'cancelled',
                'label' => 'Cancelled',
                'desc' => 'This order was cancelled.',
                'done' => true,
                'at' => $reached->get('cancelled'),
            ];
        }

        return $timeline;
    }

    private function isPastStep(string $key, Collection $reached): bool
    {
        $steps = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];

        if (! in_array($key, $steps, true)) {
            return false;
        }

        return $reached->get($key) !== null || $this->stepIndex($key) <= $this->currentStepIndex();
    }

    private function stepIndex(string $key): int
    {
        return (int) array_search($key, ['placed', 'confirmed', 'processing', 'shipped', 'delivered'], true);
    }

    private function currentStepIndex(): int
    {
        return match ($this->status) {
            self::STATUS_PENDING => 0,
            self::STATUS_CONFIRMED => 1,
            self::STATUS_PROCESSING => 2,
            self::STATUS_SHIPPED => 3,
            self::STATUS_DELIVERED => 4,
            default => -1,
        };
    }
}
