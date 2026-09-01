<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Authorized = 'authorized';
    case Paid = 'paid';
    case Failed = 'failed';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
}
