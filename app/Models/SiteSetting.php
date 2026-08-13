<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('site_settings')]
#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    use HasFactory;

    protected $casts = [
        'value' => 'string',
    ];
}
