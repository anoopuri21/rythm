<?php

declare(strict_types=1);

namespace App\Payment;

final readonly class RefundResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $gatewayRefundId = null,
        public ?string $message = null,
    ) {}
}
