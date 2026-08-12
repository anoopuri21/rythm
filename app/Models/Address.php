<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'phone',
        'email',
        'line1',
        'line2',
        'city',
        'state',
        'pincode',
        'country',
        'is_default',
    ];

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
