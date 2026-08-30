<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BackInStockSubscription;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;

final class BackInStockSubscriptionService
{
    public function subscribe(
        User $user,
        Product $product,
        ?ProductVariant $variant = null,
        bool $consent = false,
    ): BackInStockSubscription {
        if (! $consent) {
            throw new RuntimeException('Please confirm stock-availability email consent.');
        }

        if (! $product->is_active) {
            throw new RuntimeException('This product is no longer available.');
        }

        if ($variant !== null && ($variant->product_id !== $product->id || ! $variant->is_active)) {
            throw new RuntimeException('Please choose a valid option.');
        }

        if ($this->stock($product, $variant) > 0) {
            throw new RuntimeException('This item is currently available.');
        }

        $targetKey = BackInStockSubscription::targetKey($product->id, $variant?->id);
        $subscription = BackInStockSubscription::query()
            ->where('user_id', $user->id)
            ->where('target_key', $targetKey)
            ->first();

        if ($subscription !== null) {
            $subscription->forceFill([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'consent_at' => now(),
                'notified_at' => null,
                'cancelled_at' => null,
            ])->save();

            return $subscription;
        }

        try {
            return BackInStockSubscription::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'target_key' => $targetKey,
                'consent_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return BackInStockSubscription::query()
                ->where('user_id', $user->id)
                ->where('target_key', $targetKey)
                ->firstOrFail();
        }
    }

    public function cancel(User $user, BackInStockSubscription $subscription): void
    {
        if ($subscription->user_id !== $user->id) {
            throw new RuntimeException('You cannot cancel another customer’s request.');
        }

        $subscription->forceFill(['cancelled_at' => now()])->save();
    }

    public function stock(Product $product, ?ProductVariant $variant = null): int
    {
        return $variant?->stock ?? $product->stock;
    }
}
