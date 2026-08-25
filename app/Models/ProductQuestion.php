<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_questions')]
#[Fillable([
    'product_id', 'user_id', 'question', 'status', 'answer',
    'moderated_by', 'moderated_at', 'answered_by', 'answered_at',
])]
class ProductQuestion extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'moderated_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductQuestion $question): void {
            $question->status = in_array($question->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED], true)
                ? $question->status
                : self::STATUS_PENDING;

            $question->question = trim($question->question);
            $answer = trim((string) $question->answer);
            $question->answer = $answer !== '' ? $answer : null;

            $actor = auth()->user();
            if ($question->isDirty('status') && $actor?->isAdmin()) {
                $question->moderated_by = $actor->id;
                $question->moderated_at = now();
            }

            if ($question->isDirty('answer')) {
                if ($question->answer !== null && $actor?->isAdmin()) {
                    $question->answered_by = $actor->id;
                    $question->answered_at = now();
                } elseif ($question->answer === null) {
                    $question->answered_by = null;
                    $question->answered_at = null;
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

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
