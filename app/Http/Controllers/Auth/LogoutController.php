<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Storefront only — leave Filament admin guard intact if staff is also signed in.
        Auth::guard('web')->logout();

        if (Auth::guard('admin')->check()) {
            // Keep admin session payload; rotate id to avoid fixation on the web logout path.
            $request->session()->regenerate();
        } else {
            $request->session()->invalidate();
        }

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
