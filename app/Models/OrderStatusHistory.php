<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'from',
        'to',
        'note',
        'actor',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
