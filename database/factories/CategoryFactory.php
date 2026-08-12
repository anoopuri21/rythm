<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Guitars', 'Keyboards', 'Drums', 'Pro Audio', 'DJ Gear', 'Accessories',
            'Acoustic Guitars', 'Electric Guitars', 'Microphones', 'Studio Monitors',
        ]);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'seo_title' => $name,
            'seo_description' => fake()->sentence(),
        ];
    }

    public function childOf(int $parentId): static
    {
        return $this->state(fn () => ['parent_id' => $parentId]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
