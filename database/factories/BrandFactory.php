<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Fender', 'Yamaha', 'Roland', 'Korg', 'Shure', 'AKG', 'Ibanez', 'Casio',
        ]);

        return [
            'name' => $name,
            // Suffix keeps factory slugs unique against seeded brand rows
            // (same convention as CategoryFactory and ProductFactory).
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
