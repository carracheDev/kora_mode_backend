<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sku' => Str::upper(fake()->unique()->bothify('KM-#####')),
            'description' => fake()->sentence(),
            'gender' => fake()->randomElement(['femme', 'homme', 'mixte']),
            'subcategory' => fake()->word(),
            'price' => fake()->numberBetween(5000, 80000),
            'old_price' => null,
            'images' => [],
            'sizes' => [],
            'colors' => [],
            'stock' => fake()->numberBetween(0, 30),
            'tags' => [],
            'rating' => null,
            'popularity' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'is_featured' => false,
            'is_demo_data' => true,
        ];
    }
}
