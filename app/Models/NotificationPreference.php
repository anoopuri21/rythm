<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

#[Fillable(['user_id', 'category', 'email_enabled', 'database_enabled'])]
final class NotificationPreference extends Model
{
    public const CATEGORY_ORDER_UPDATES = 'order_updates';

    public const CATEGORY_PRODUCT_UPDATES = 'product_updates';

    public const OPTIONAL_CATEGORIES = [
        self::CATEGORY_ORDER_UPDATES,
        self::CATEGORY_PRODUCT_UPDATES,
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'database_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        self::saving(function (self $preference): void {
            if (! in_array($preference->category, self::OPTIONAL_CATEGORIES, true)) {
                throw new InvalidArgumentException('Only approved optional notification categories can be configured.');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
