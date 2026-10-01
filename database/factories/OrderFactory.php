<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => (string) Str::uuid(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '+2290100000000',
            'customer_city' => 'Cotonou',
            'customer_address' => fake()->streetAddress(),
            'subtotal' => 25000,
            'delivery_fee' => 1000,
            'discount_amount' => 0,
            'total' => 26000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'stock_reserved_at' => now(),
        ];
    }
}
