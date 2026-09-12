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
            'razorpay' => 'You will complete payment in Razorpay’s secure window (UPI, cards, netbanking, wallets as offered by the gateway).',
            'fake' => 'Local development only: no Razorpay keys are set, so payment is simulated and no real charge is created. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET for real test checkout.',
            default => 'Online payment is not available right now. The store has not finished configuring the payment gateway. Your cart is saved — please try again later or contact the store.',
        };
    }

    public static function payButtonLabel(float $grandTotal): string
    {
        $amount = '₹'.number_format($grandTotal, 0);

        return match (self::mode()) {
            'razorpay' => "Pay {$amount} securely",
            'fake' => "Simulate pay {$amount} (dev only)",
            default => 'Payment unavailable',
        };
    }
}
