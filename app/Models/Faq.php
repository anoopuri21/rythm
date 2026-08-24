<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\HomepageDataObserver;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('faqs')]
#[ObservedBy(HomepageDataObserver::class)]
#[Fillable(['question', 'answer', 'sort_order', 'is_active'])]
class Faq extends Model
{
    use HasFactory;

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
