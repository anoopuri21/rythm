<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class VerifyMailFromAddressMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $confirmUrl,
        public readonly string $pendingAddress,
        public readonly string $storeName,
    ) {}

    public function envelope(): Envelope
    {
        // Always send verification from the server bootstrap From (env), never the pending address.
        return new Envelope(
            subject: "Confirm sender email for {$this->storeName}",
            from: new Address(
                (string) config('mail.from_bootstrap.address', config('mail.from.address')),
                (string) config('mail.from_bootstrap.name', config('mail.from.name')),
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verify-mail-from',
        );
    }
}
