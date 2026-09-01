<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\NotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BackInStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NotificationDelivery $delivery,
        public readonly string $productName,
        public readonly string $productUrl,
        public readonly ?string $variantName = null,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'emails'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item = $this->variantName === null
            ? $this->productName
            : $this->productName.' — '.$this->variantName;

        return (new MailMessage)
            ->subject($item.' is back in stock — Rhythm Exports')
            ->greeting('Good news!')
            ->line($item.' is currently available again.')
            ->action('View item', $this->productUrl)
            ->line('This stock-availability email was sent because you requested an update.');
    }
}
