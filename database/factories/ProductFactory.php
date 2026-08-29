<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->numberBetween(500, 100000);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'sku' => 'RYM-'.strtoupper(Str::random(8)),
            'short_description' => fake()->sentence(10),
            'description' => '<p>'.implode('</p><p>', fake()->paragraphs(2)).'</p>',
            'price' => $price,
            'compare_at_price' => fake()->boolean(40) ? $price + fake()->numberBetween(100, 20000) : null,
            'stock' => fake()->numberBetween(0, 60),
            'low_stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => fake()->boolean(15),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function withStock(int $stock): static
    {
        return $this->state(fn () => ['stock' => $stock]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
