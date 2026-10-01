<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'sku' => $product->sku,
            'size' => 'M',
            'color' => 'Noir',
            'quantity' => 1,
            'unit_price' => 25000,
            'discount_amount' => 0,
            'line_total' => 25000,
        ];
    }
}
