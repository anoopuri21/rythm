<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

#[Fillable(['event_key', 'event_type', 'aggregate_type', 'aggregate_id', 'payload_hash', 'metadata', 'occurred_at'])]
final class CommerceEvent extends Model
{
    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        self::updating(fn (): never => throw new RuntimeException('Commerce events are immutable.'));
        self::deleting(fn (): never => throw new RuntimeException('Commerce events are immutable.'));
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
