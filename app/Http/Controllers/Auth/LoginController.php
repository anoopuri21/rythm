<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function show(\Illuminate\Http\Request $request): View
    {
        // Allow cart/checkout CTAs to pass ?intended=/checkout so guests return after login.
        $intended = $request->query('intended');
        if (is_string($intended) && $intended !== '' && str_starts_with($intended, '/') && ! str_starts_with($intended, '//')) {
            $request->session()->put('url.intended', $intended);
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Only email + password are auth credentials. `remember` is a checkbox flag —
        // if it is left inside validated() Auth::attempt treats it as a users.remember column.
        $credentials = $request->safe()->only(['email', 'password']);

        // Explicit web guard — never touch the Filament admin session.
        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::guard('web')->user();
        if ($user instanceof User && ($user->deactivated_at !== null || $user->removed_at !== null)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'This account is deactivated.']);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        if ($user instanceof User && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('home'));
    }
}
