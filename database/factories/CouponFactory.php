<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => 'percent',
            'value' => fake()->numberBetween(5, 30),
            'min_order' => 0,
            'max_discount' => null,
            'starts_at' => null,
            'expires_at' => now()->addDays(30),
            'max_uses' => null,
            'used_count' => 0,
            'is_active' => true,
        ];
    }

    public function fixed(float $value): static
    {
        return $this->state(fn () => ['type' => 'fixed', 'value' => $value]);
    }
}
