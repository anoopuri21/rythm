<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('orders')]
#[Fillable(['order_number', 'user_id', 'email', 'status', 'payment_status', 'subtotal', 'discount', 'shipping_fee', 'tax', 'total', 'currency', 'shipping_address', 'billing_address', 'notes', 'placed_at'])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

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
        self::PAYMENT_PAID,
        self::PAYMENT_FAILED,
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

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
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
     * @return array<int, array{key:string, label:string, desc:string, done:bool, at:?\Illuminate\Support\Carbon}>
     */
    public function trackingTimeline(): array
    {
        $reached = collect($this->statusHistory)
            ->keyBy('to')
            ->map(fn ($entry) => $entry->created_at);

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

    private function isPastStep(string $key, \Illuminate\Support\Collection $reached): bool
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
        return $this->stepIndex($this->status);
    }
}
