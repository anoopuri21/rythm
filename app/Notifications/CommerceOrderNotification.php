<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\NotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CommerceOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $channels
     * @param  array<string, scalar|null>  $data
     */
    public function __construct(
        public readonly NotificationDelivery $delivery,
        public readonly array $channels,
        public readonly string $title,
        public readonly string $message,
        public readonly string $actionUrl,
        public readonly array $data = [],
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'emails', 'database' => 'default'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title.' — Rhythm Exports')
            ->greeting('Hello!')
            ->line($this->message)
            ->action('View order', $this->actionUrl)
            ->line('This is a transactional update about your Rhythm Exports order.');
    }

    /** @return array<string, scalar|null> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->actionUrl,
            'event_type' => $this->delivery->event->event_type,
            'delivery_id' => $this->delivery->id,
            ...$this->data,
        ];
    }
}
