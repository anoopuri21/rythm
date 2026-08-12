<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('newsletter_subscribers')]
#[Fillable(['email', 'subscribed_at'])]
class NewsletterSubscriber extends Model
{
    

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
        ];
    }
}
