<?php

declare(strict_types=1);

namespace App\Support;

use App\Payment\RazorpayGateway;

/**
 * Storefront-facing payment readiness (C4 / W2).
 *
 * Real Razorpay keys → ready.
 * Local + RAZORPAY_ALLOW_FAKE_PAYMENTS → simulated test checkout only.
 * Production / staging without keys → blocked (no silent fake success).
 */
final class PaymentAvailability
{
    public static function razorpayConfigured(): bool
    {
        return RazorpayGateway::isConfigured();
    }

    public static function fakeAllowed(): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        if (! app()->environment('local')) {
            return false;
        }

        return (bool) config('services.razorpay.allow_fake', false);
    }

    /** Checkout can attempt placeOrder (real keys or allowed fake). */
    public static function canCheckout(): bool
    {
        return self::razorpayConfigured() || self::fakeAllowed();
    }

    public static function mode(): string
    {
        if (self::razorpayConfigured()) {
            return 'razorpay';
        }

        if (self::fakeAllowed()) {
            return 'fake';
        }

        return 'unavailable';
    }

    public static function customerMessage(): string
    {
        return match (self::mode()) {
            'razorpay' => 'Pay in the Razorpay window. UPI, cards and netbanking are offered there.',
            'fake' => 'This is a local test checkout. Nothing is charged.',
            default => 'Payment is not ready. Ask a Super Admin to add Razorpay keys in Admin → Razorpay. Your cart is saved.',
        };
    }

    public static function payButtonLabel(float $grandTotal): string
    {
        $amount = '₹'.number_format($grandTotal, 0);

        return match (self::mode()) {
            'razorpay' => "Pay {$amount}",
            'fake' => "Test pay {$amount}",
            default => 'Payment unavailable',
        };
    }
}
