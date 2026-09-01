<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'customer_guidance', 'is_active', 'sort_order'])]
final class ReturnReason extends Model
{
    use HasFactory;

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }
}
