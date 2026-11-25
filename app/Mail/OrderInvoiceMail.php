<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Services\SiteSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Invoice email sent to the customer (and optionally BCC'd to the owner)
 * when an order payment is confirmed. Renders a compact HTML invoice inline
 * — no PDF attachment required.
 */
final class OrderInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice — Order {$this->order->order_number} — Rhythm Exports",
        );
    }

    public function content(): Content
    {
        $settings = app(SiteSettingsService::class);
        $gst = $this->order->gstTotals();

        return new Content(
            view: 'emails.invoice',
            with: [
                'order' => $this->order,
                'settings' => $settings,
                'gst' => $gst,
                'currencySymbol' => $settings->currencySymbol(),
                'businessName' => $settings->get('business_legal_name') ?: 'Rhythm Exports',
                'businessAddress' => $settings->get('business_address') ?: $settings->get('address_line'),
                'gstin' => $settings->get('origin_gstin'),
                'orderUrl' => url('/'),
            ],
        );
    }
}
