<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromoCode> */
class PromoCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-Z]{4}[0-9]{2}'),
            'discount_percent' => fake()->numberBetween(5, 40),
            'eligible_slugs' => null,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'is_demo_data' => true,
        ];
    }
}
