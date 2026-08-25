<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable payload assembled server-side at checkout placement.
 */
final readonly class CheckoutData
{
    /**
     * @param  array<string, mixed>  $shippingAddress
     * @param  array<string, mixed>  $billingAddress
     */
    public function __construct(
        public int $addressId,
        public array $shippingAddress,
        public array $billingAddress,
        public string $currency = 'INR',
        public ?string $notes = null,
        public ?string $couponCode = null,
    ) {}
}
