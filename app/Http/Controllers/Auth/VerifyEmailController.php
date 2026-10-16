<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class VerifyEmailController extends Controller
{
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('account.index')->with('status', 'Email verified!');
    }

    public function send(Request $request): RedirectResponse
    {
        try {
            // Same guard as signup: a mailer error must not turn Resend into a 500.
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('verification_mail_failed', true);
        }

        return back()
            ->with('verification_mail_sent', true)
            ->with('status', 'Verification link sent!');
    }
}
