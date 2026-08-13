<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'RYM-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
            'subtotal' => fake()->numberBetween(500, 50000),
            'discount' => 0,
            'shipping_fee' => 0,
            'tax' => 0,
            'total' => fake()->numberBetween(500, 50000),
            'currency' => 'INR',
            'shipping_address' => [
                'name' => fake()->name(),
                'phone' => '9876543210',
                'line1' => fake()->streetAddress(),
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
                'country' => 'IN',
            ],
            'placed_at' => now(),
        ];
    }
}
