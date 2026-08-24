<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->newStatus) {
            Order::STATUS_SHIPPED => "Your order {$this->order->order_number} has shipped! 🚚",
            Order::STATUS_DELIVERED => "Your order {$this->order->order_number} was delivered 🎉",
            Order::STATUS_CANCELLED => "Order {$this->order->order_number} cancelled",
            default => "Order {$this->order->order_number} — {$this->newStatus}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
            with: [
                'order' => $this->order,
                'status' => $this->newStatus,
                'trackUrl' => route('orders.show', $this->order),
            ],
        );
    }
}
