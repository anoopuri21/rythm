<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => 'shipping',
            'name' => fake()->name(),
            'phone' => '9'.fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'line1' => fake()->streetAddress(),
            'line2' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => fake()->numerify('######'),
            'country' => 'IN',
            'is_default' => false,
        ];
    }

    public function billing(): static
    {
        return $this->state(fn () => ['type' => 'billing']);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
