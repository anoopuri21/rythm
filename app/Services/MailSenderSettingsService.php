<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\VerifyMailFromAddressMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Admin-managed outbound "From" address (Admin → Settings).
 * Address becomes live only after the mailbox owner confirms a signed link.
 */
final class MailSenderSettingsService
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    /**
     * Capture env/config From once per app boot before any admin override is applied.
     * Stored under mail.from_bootstrap so PHPUnit app refresh does not leak static state.
     */
    public function captureBootstrapFrom(): void
    {
        if (config('mail.from_bootstrap.captured') === true) {
            return;
        }

        config([
            'mail.from_bootstrap.captured' => true,
            'mail.from_bootstrap.address' => (string) config('mail.from.address'),
            'mail.from_bootstrap.name' => (string) config('mail.from.name'),
        ]);
    }

    public function liveAddress(): string
    {
        return trim((string) $this->settings->get('mail_from_address', ''));
    }

    public function liveName(): string
    {
        $name = trim((string) $this->settings->get('mail_from_name', ''));

        return $name !== '' ? $name : (string) config('app.name');
    }

    public function isVerified(): bool
    {
        $address = $this->liveAddress();
        $verifiedAt = trim((string) $this->settings->get('mail_from_verified_at', ''));

        return $address !== '' && $verifiedAt !== '';
    }

    public function pendingAddress(): string
    {
        return trim((string) $this->settings->get('mail_from_pending_address', ''));
    }

    /**
     * Human-readable status line for Admin → Settings.
     */
    public function statusSummary(): string
    {
        $fallback = $this->bootstrapFrom()['address'];

        if ($this->isVerified()) {
            $line = 'Verified live sender: '.$this->liveAddress().' ('.$this->liveName().')';
        } else {
            $line = 'No verified admin sender yet. Outbound mail uses server default: '.$fallback;
        }

        $pending = $this->pendingAddress();
        if ($pending !== '') {
            $line .= ' · Pending confirmation: '.$pending.' (open the link sent to that inbox within 24h)';
        }

        return $line;
    }

    /**
     * Effective From for outbound mail: verified admin setting, else env/config bootstrap.
     *
     * @return array{address: string, name: string}
     */
    public function effectiveFrom(): array
    {
        if ($this->isVerified()) {
            return [
                'address' => $this->liveAddress(),
                'name' => $this->liveName(),
            ];
        }

        return $this->bootstrapFrom();
    }

    public function applyToConfig(): void
    {
        $this->captureBootstrapFrom();
        $from = $this->effectiveFrom();
        config([
            'mail.from.address' => $from['address'],
            'mail.from.name' => $from['name'],
        ]);
    }

    /**
     * Persist display name immediately. Address only goes live after verification.
     *
     * @param  array{mail_from_address?: mixed, mail_from_name?: mixed}  $input
     * @return array{status: string, message: string}
     */
    public function saveFromAdmin(array $input): array
    {
        $name = trim((string) ($input['mail_from_name'] ?? ''));
        $address = strtolower(trim((string) ($input['mail_from_address'] ?? '')));

        if ($name !== '') {
            $this->settings->put('mail_from_name', $name);
        } else {
            $this->settings->forget('mail_from_name');
        }

        if ($address === '') {
            $this->clearLiveAndPending();
            $this->applyToConfig();

            return [
                'status' => 'cleared',
                'message' => 'Sender email cleared. Outbound mail will use the server MAIL_FROM_ADDRESS.',
            ];
        }

        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid sender email address.');
        }

        // Already live and verified — only name may have changed.
        if ($this->isVerified() && $this->liveAddress() === $address) {
            $this->applyToConfig();

            return [
                'status' => 'unchanged',
                'message' => 'Sender settings saved.',
            ];
        }

        $this->startVerification($address);

        return [
            'status' => 'verification_sent',
            'message' => "Verification link sent to {$address}. The address becomes the store sender only after you open that link.",
        ];
    }

    public function startVerification(string $address): void
    {
        $address = strtolower(trim($address));
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid sender email address.');
        }

        $plain = Str::random(64);

        $this->settings->putMany([
            'mail_from_pending_address' => $address,
            'mail_from_pending_token' => hash('sha256', $plain),
            'mail_from_pending_sent_at' => now()->toIso8601String(),
        ]);

        $url = URL::temporarySignedRoute(
            'mail-from.verify',
            now()->addHours(24),
            ['token' => $plain],
        );

        // Verification mail uses bootstrap (env) From via config + listener only when verified
        // (verified From is fine for re-confirming a new mailbox).
        Mail::mailer((string) config('mail.default'))
            ->to($address)
            ->send(new VerifyMailFromAddressMail(
                confirmUrl: $url,
                pendingAddress: $address,
                storeName: (string) config('app.name'),
            ));
    }

    public function confirmPending(string $plainToken): bool
    {
        $pending = $this->pendingAddress();
        $storedHash = (string) $this->settings->get('mail_from_pending_token', '');
        $sentAt = (string) $this->settings->get('mail_from_pending_sent_at', '');

        if ($pending === '' || $storedHash === '' || $plainToken === '') {
            return false;
        }

        if (! hash_equals($storedHash, hash('sha256', $plainToken))) {
            return false;
        }

        if ($sentAt !== '') {
            try {
                if (now()->greaterThan(\Carbon\Carbon::parse($sentAt)->addHours(24))) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        $name = trim((string) $this->settings->get('mail_from_name', ''));
        if ($name === '') {
            $name = (string) config('app.name');
        }

        $this->settings->putMany([
            'mail_from_address' => $pending,
            'mail_from_name' => $name,
            'mail_from_verified_at' => now()->toIso8601String(),
        ]);
        $this->clearPendingOnly();
        $this->applyToConfig();

        return true;
    }

    /**
     * @return array{address: string, name: string}
     */
    private function bootstrapFrom(): array
    {
        $this->captureBootstrapFrom();

        return [
            'address' => (string) config('mail.from_bootstrap.address', config('mail.from.address')),
            'name' => (string) config('mail.from_bootstrap.name', config('mail.from.name')),
        ];
    }

    private function clearLiveAndPending(): void
    {
        $this->settings->forgetMany([
            'mail_from_address',
            'mail_from_name',
            'mail_from_verified_at',
            'mail_from_pending_address',
            'mail_from_pending_token',
            'mail_from_pending_sent_at',
        ]);
    }

    private function clearPendingOnly(): void
    {
        $this->settings->forgetMany([
            'mail_from_pending_address',
            'mail_from_pending_token',
            'mail_from_pending_sent_at',
        ]);
    }
}
