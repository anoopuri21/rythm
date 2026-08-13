<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Black', 'Natural', 'Sunburst', 'White']),
            'options' => [],
            'sku' => 'RYM-'.strtoupper(Str::random(8)),
            'price_override' => null,
            'stock' => fake()->numberBetween(0, 30),
            'is_active' => true,
        ];
    }
}
