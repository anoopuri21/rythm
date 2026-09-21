<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payment_gateway_settings')]
#[Fillable([
    'driver',
    'mode',
    'test_key_id',
    'test_key_secret',
    'test_webhook_secret',
    'live_key_id',
    'live_key_secret',
    'live_webhook_secret',
])]
#[Hidden(['test_key_secret', 'test_webhook_secret', 'live_key_secret', 'live_webhook_secret'])]
class PaymentGatewaySetting extends Model
{
    public const DRIVER_RAZORPAY = 'razorpay';

    public const MODE_TEST = 'test';

    public const MODE_LIVE = 'live';

    protected function casts(): array
    {
        return [
            'test_key_secret' => 'encrypted',
            'test_webhook_secret' => 'encrypted',
            'live_key_secret' => 'encrypted',
            'live_webhook_secret' => 'encrypted',
        ];
    }
}
