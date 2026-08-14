<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('addresses')]
#[Fillable(['user_id', 'type', 'name', 'phone', 'email', 'line1', 'line2', 'city', 'state', 'pincode', 'country', 'is_default'])]
class Address extends Model
{
    use HasFactory;

    

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Full one-line label for checkout/orders. */
    public function fullAddress(): string
    {
        return trim(implode(', ', array_filter([
            $this->line1,
            $this->line2,
            $this->city,
            $this->state,
            $this->pincode,
            $this->country,
        ])));
    }
}
