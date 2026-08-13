<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Security headers on every response (CSP, nosniff, frame options…).
        $middleware->prependToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // Razorpay posts to these endpoints without a CSRF token
        // (crypto-verified server-side instead).
        $middleware->validateCsrfTokens(except: [
            'payment/razorpay/callback',
            'payment/razorpay/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
