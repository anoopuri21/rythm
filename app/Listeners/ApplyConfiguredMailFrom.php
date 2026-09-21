<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\MailSenderSettingsService;
use Illuminate\Mail\Events\MessageSending;

/**
 * Force Symfony message From to the verified admin sender (when set).
 */
final class ApplyConfiguredMailFrom
{
    public function __construct(
        private readonly MailSenderSettingsService $mailSender,
    ) {}

    public function handle(MessageSending $event): void
    {
        if (! $this->mailSender->isVerified()) {
            return;
        }

        $from = $this->mailSender->effectiveFrom();
        $event->message->from($from['address'], $from['name']);
    }
}
