<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BackInStockNotificationRequested;
use App\Models\BackInStockSubscription;
use App\Models\NotificationDelivery;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\BackInStockNotification;
use App\Services\CommerceNotificationService;
use Illuminate\Support\Facades\URL;

final class HandleBackInStockNotification
{
    public function __construct(private readonly CommerceNotificationService $notifications) {}

    public function handle(BackInStockNotificationRequested $requested): void
    {
        $subscription = BackInStockSubscription::query()
            ->with(['user', 'product', 'variant'])
            ->whereKey($requested->subscriptionId)
            ->first();

        if (! $this->isReady($subscription)) {
            return;
        }

        /** @var User $user */
        $user = $subscription->user;
        /** @var Product $product */
        $product = $subscription->product;
        $variant = $subscription->variant;
        $event = $this->notifications->recordEvent(
            'back-in-stock.subscription.'.$subscription->id,
            'product.back_in_stock',
            'back_in_stock_subscription',
            $subscription->id,
        );
        $delivery = $this->notifications->reserveDelivery(
            $event,
            $user,
            'mail',
            BackInStockNotification::class,
            (string) $user->email,
        );

        if (! $delivery->wasRecentlyCreated) {
            return;
        }

        $subscription->forceFill(['notified_at' => now()])->save();
        $url = URL::route('product.show', ['product' => $product]);
        $user->notify(new BackInStockNotification(
            $delivery,
            (string) $product->name,
            $url,
            $variant?->name,
        ));
    }

    public function retryDelivery(NotificationDelivery $delivery): void
    {
        $delivery->loadMissing('user', 'event');
        $subscription = BackInStockSubscription::query()
            ->with(['product', 'variant'])
            ->whereKey($delivery->event?->aggregate_id)
            ->first();
        if ($subscription === null || ! $delivery->user instanceof User) {
            throw new \RuntimeException('Back-in-stock delivery retry requires an existing subscription and customer.');
        }

        if (! $this->isReady($subscription, allowNotified: true)) {
            throw new \RuntimeException('The subscribed item is no longer available for notification retry.');
        }

        $delivery->user->notify(new BackInStockNotification(
            $delivery,
            (string) $subscription->product->name,
            URL::route('product.show', ['product' => $subscription->product]),
            $subscription->variant?->name,
        ));
    }

    private function isReady(?BackInStockSubscription $subscription, bool $allowNotified = false): bool
    {
        if ($subscription === null || ! $subscription->relationLoaded('user') || ! $subscription->relationLoaded('product')) {
            return false;
        }

        if (! $subscription->user instanceof User || ! $subscription->product instanceof Product) {
            return false;
        }

        if (! $subscription->user->hasVerifiedEmail()) {
            return false;
        }

        if (! $subscription->product->is_active || $subscription->cancelled_at !== null || (! $allowNotified && $subscription->notified_at !== null)) {
            return false;
        }

        $variant = $subscription->variant;
        if ($variant !== null && (! $variant instanceof ProductVariant || ! $variant->is_active)) {
            return false;
        }

        return ($variant?->stock ?? $subscription->product->stock) > 0;
    }
}
