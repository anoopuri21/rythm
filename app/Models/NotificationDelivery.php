<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'commerce_event_id',
    'user_id',
    'delivery_key',
    'channel',
    'notification_type',
    'recipient_hash',
    'status',
    'attempts',
    'last_error',
    'queued_at',
    'sent_at',
    'failed_at',
])]
final class NotificationDelivery extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SUPPRESSED = 'suppressed';

    protected $casts = [
        'attempts' => 'integer',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CommerceEvent::class, 'commerce_event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
