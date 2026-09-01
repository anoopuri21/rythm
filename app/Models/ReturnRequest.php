<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'user_id',
    'return_reason_id',
    'refund_id',
    'request_number',
    'idempotency_key',
    'status',
    'reason_snapshot',
    'customer_note',
    'approved_at',
    'received_at',
    'closed_at',
])]
final class ReturnRequest extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_RECEIVED,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReturnRequestEvent::class)->oldest('id');
    }
}
