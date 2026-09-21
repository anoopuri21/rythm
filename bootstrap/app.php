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
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $message = match (true) {
                $status === 403 => 'You do not have access to this.',
                $status === 404 => 'We could not find that.',
                $status === 429 => 'Too many tries. Wait a moment and try again.',
                $status >= 500 && ! config('app.debug') => 'Something went wrong. Please try again.',
                default => null,
            };

            if ($message === null) {
                return null;
            }

            return response()->json(['message' => $message], $status);
        });
    })->create();
