<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController extends Controller
{
    public function __invoke(StoreNewsletterSubscriptionRequest $request): JsonResponse|RedirectResponse
    {
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $request->validated('email')],
            ['subscribed_at' => now()],
        );

        $message = $subscriber->wasRecentlyCreated
            ? 'Welcome to the Rhythm Exports community.'
            : 'You are already on the list.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'subscribed' => true,
            ], $subscriber->wasRecentlyCreated ? 201 : 200);
        }

        return back()->with('newsletter_status', $message);
    }
}
