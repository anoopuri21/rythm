<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make the default auth() helper resolve the Filament admin guard on /admin.
 * Without this, panel code calling auth()->user() would still read the web guard
 * while Filament itself authenticated against "admin".
 */
final class UseAdminAuthGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('admin');

        return $next($request);
    }
}
