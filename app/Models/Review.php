<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('reviews')]
#[Fillable([
    'product_id', 'user_id', 'rating', 'comment', 'is_approved', 'status',
    'merchant_reply', 'moderated_by', 'moderated_at', 'replied_by', 'replied_at',
])]
class Review extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'moderated_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Review $review): void {
            $requestedStatus = $review->status;
            if ($requestedStatus === null && $review->is_approved) {
                $requestedStatus = self::STATUS_APPROVED;
            }

            $review->status = in_array($requestedStatus, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED], true)
                ? $requestedStatus
                : self::STATUS_PENDING;
            $review->is_approved = $review->status === self::STATUS_APPROVED;

            $actor = auth()->user();
            if ($review->isDirty('status') && $actor?->isAdmin()) {
                $review->moderated_by = $actor->id;
                $review->moderated_at = now();
            }

            $reply = trim((string) $review->merchant_reply);
            $review->merchant_reply = $reply !== '' ? $reply : null;

            if ($review->isDirty('merchant_reply')) {
                if ($review->merchant_reply !== null && $actor?->isAdmin()) {
                    $review->replied_by = $actor->id;
                    $review->replied_at = now();
                } elseif ($review->merchant_reply === null) {
                    $review->replied_by = null;
                    $review->replied_at = null;
                }
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
