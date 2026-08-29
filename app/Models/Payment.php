<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('payments')]
#[Fillable(['order_id', 'gateway', 'method', 'gateway_order_id', 'gateway_payment_id', 'gateway_signature', 'amount', 'currency', 'status', 'payload'])]
class Payment extends Model
{
    use HasFactory;

    public const STATUS_INITIATED = PaymentStatus::Initiated->value;

    public const STATUS_AUTHORIZED = PaymentStatus::Authorized->value;

    public const STATUS_PAID = PaymentStatus::Paid->value;

    public const STATUS_FAILED = PaymentStatus::Failed->value;

    public const STATUS_REFUNDED = PaymentStatus::Refunded->value;

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
