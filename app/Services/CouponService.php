<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Coupon validation + discount math (server-side only).
 */
final class CouponService
{
    /**
     * @return array{coupon: Coupon, discount: float}
     *
     * @throws RuntimeException on any invalid/expired/insufficient coupon
     */
    public function validateAndApply(string $code, float $subtotal, bool $lockForUpdate = false): array
    {
        $query = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if ($coupon === null) {
            throw new RuntimeException('Invalid coupon code.');
        }

        if (! in_array($coupon->type, [Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED], true)) {
            throw new RuntimeException('This coupon has an invalid discount type.');
        }

        $value = (float) $coupon->value;
        if ($value <= 0 || ($coupon->type === Coupon::TYPE_PERCENT && $value > 100)) {
            throw new RuntimeException('This coupon has an invalid discount value.');
        }

        if ($coupon->starts_at !== null && $coupon->expires_at !== null && $coupon->starts_at->gte($coupon->expires_at)) {
            throw new RuntimeException('This coupon has an invalid active period.');
        }

        if ($coupon->starts_at !== null && Carbon::parse($coupon->starts_at)->isFuture()) {
            throw new RuntimeException('This coupon is not active yet.');
        }

        if ($coupon->expires_at !== null && Carbon::parse($coupon->expires_at)->isPast()) {
            throw new RuntimeException('This coupon has expired.');
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            throw new RuntimeException('This coupon has reached its usage limit.');
        }

        if ($subtotal < (float) $coupon->min_order) {
            throw new RuntimeException(
                'This coupon requires a minimum order of ₹'.number_format((float) $coupon->min_order).'.'
            );
        }

        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->max_discount !== null && $discount > (float) $coupon->max_discount) {
            $discount = (float) $coupon->max_discount;
        }

        $discount = min($discount, $subtotal);

        return ['coupon' => $coupon, 'discount' => round($discount, 2)];
    }

    public function incrementUsage(Coupon $coupon): void
    {
        DB::transaction(function () use ($coupon): void {
            $locked = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->firstOrFail();

            if ($locked->max_uses !== null && $locked->used_count >= $locked->max_uses) {
                throw new RuntimeException('This coupon has reached its usage limit.');
            }

            $locked->increment('used_count');
        });
    }
}
