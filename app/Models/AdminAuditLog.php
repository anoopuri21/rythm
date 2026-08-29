<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

#[Table('admin_audit_logs')]
#[Fillable(['actor_id', 'action', 'subject_type', 'subject_id', 'reason', 'before_values', 'after_values', 'ip_hash', 'user_agent', 'request_id', 'created_at'])]
final class AdminAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
        'created_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Admin audit records are immutable.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Admin audit records cannot be deleted through the application.');
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
