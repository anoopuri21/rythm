<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MailSenderSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MailFromVerifyController extends Controller
{
    public function __invoke(Request $request, MailSenderSettingsService $mailSender): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $token = (string) $request->query('token', '');
        $ok = $token !== '' && $mailSender->confirmPending($token);

        if (! $ok) {
            return redirect()
                ->route('home')
                ->with('mail_from_status', 'This sender confirmation link is invalid or has expired. Ask an admin to send a new one from Settings.');
        }

        return redirect()
            ->route('home')
            ->with('mail_from_status', 'Sender email confirmed. The store will use this address for outbound mail.');
    }
}
