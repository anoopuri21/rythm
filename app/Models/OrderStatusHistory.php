<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('order_status_history')]
#[Fillable(['order_id', 'from', 'to', 'note', 'actor'])]
class OrderStatusHistory extends Model
{
    use HasFactory;

    

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
