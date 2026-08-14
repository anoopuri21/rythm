<?php

declare(strict_types=1);

namespace App\Payment;

/**
 * Immutable outcome of a gateway verification.
 */
final readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $gatewayPaymentId = null,
        public ?string $message = null,
    ) {}
}
