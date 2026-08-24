<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('contact_messages')]
#[Fillable(['name', 'email', 'phone', 'subject', 'message', 'status'])]
class ContactMessage extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => 'string',
    ];
}
