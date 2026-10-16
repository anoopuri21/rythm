<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmAccountClosureRequest;
use App\Models\User;
use App\Services\AccountClosureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AccountClosureController extends Controller
{
    public function send(Request $request, AccountClosureService $closure): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        try {
            $closure->sendLink($user);
        } catch (\Throwable $exception) {
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $exception;
            }

            report($exception);

            return back()->with('closure_error', 'We could not send the email. Please try again.');
        }

        return back()->with('closure_success', 'We sent a link to your email. It expires in 60 minutes.');
    }

    public function show(Request $request, User $user, string $hash, AccountClosureService $closure): View|Response
    {
        $refusal = $this->refusal($request, $user, $hash, $closure);
        if ($refusal !== null) {
            return $refusal;
        }

        $token = (string) $request->query('token');

        return view('account.closure', [
            'state' => 'ready',
            'deactivateUrl' => $closure->signedActionUrl('account.closure.deactivate', $user, $token),
            'removeUrl' => $closure->signedActionUrl('account.closure.remove', $user, $token),
        ]);
    }

    public function deactivate(
        Request $request,
        User $user,
        string $hash,
        AccountClosureService $closure,
    ): RedirectResponse|Response {
        $refusal = $this->refusal($request, $user, $hash, $closure);
        if ($refusal !== null) {
            return $refusal;
        }

        $request->validate((new ConfirmAccountClosureRequest)->rules());
        $closure->deactivate($user);

        return $this->endSession($request, 'Your account is deactivated. You have been signed out.');
    }

    public function remove(
        Request $request,
        User $user,
        string $hash,
        AccountClosureService $closure,
    ): RedirectResponse|Response {
        $refusal = $this->refusal($request, $user, $hash, $closure);
        if ($refusal !== null) {
            return $refusal;
        }

        $request->validate((new ConfirmAccountClosureRequest)->rules());
        $closure->remove($user);

        return $this->endSession($request, 'Your personal data has been removed and you have been signed out.');
    }

    private function refusal(Request $request, User $user, string $hash, AccountClosureService $closure): ?Response
    {
        $actor = $request->user();
        if (! $actor instanceof User || $actor->id !== $user->id) {
            return response()->view('account.closure', ['state' => 'mismatch'], 403);
        }

        if (! $closure->linkIsValid($user, $hash, $request->query('token'))) {
            return response()->view('account.closure', ['state' => 'invalid'], 403);
        }

        return null;
    }

    private function endSession(Request $request, string $message): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', $message);
    }
}
