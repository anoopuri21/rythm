<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AccountClosureLinkNotification extends Notification
{
    public function __construct(public readonly string $url) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Close your Rhythm Exports account')
            ->greeting('Hello!')
            ->line('Use the button below to deactivate your login or remove your personal data. This link expires in 60 minutes and only works while you are signed in to this same account.')
            ->action('Review account closure', $this->url)
            ->line('If you did not ask for this, you can ignore the email. Your account stays as it is.');
    }
}
