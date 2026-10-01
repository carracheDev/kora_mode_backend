<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_customer_can_simulate_success_and_failure_releases_stock_once(): void
    {
        config()->set('services.fedapay.driver', 'simulation');
        $product = Product::factory()->create(['stock' => 2]);
        $order = Order::factory()->create([
            'fedapay_transaction_id' => null,
            'payment_method' => 'mtn',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => $product->price,
            'discount_amount' => 0,
            'line_total' => $product->price * 2,
        ]);

        $this->postJson("/api/v1/orders/{$order->order_number}/payments")->assertOk()
            ->assertJsonPath('data.simulation', true);
        $this->postJson("/api/v1/orders/{$order->order_number}/payment-simulation", ['outcome' => 'failed'])
            ->assertOk()->assertJsonPath('data.payment_status', 'failed');

        $this->assertSame(4, $product->fresh()->stock);
        $this->postJson("/api/v1/orders/{$order->order_number}/payment-simulation", ['outcome' => 'failed'])->assertOk();
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_simulation_endpoint_is_hidden_when_fedapay_is_enabled(): void
    {
        config()->set('services.fedapay.driver', 'fedapay');
        $order = Order::factory()->create(['payment_method' => 'mtn']);

        $this->postJson("/api/v1/orders/{$order->order_number}/payment-simulation", ['outcome' => 'succeeded'])
            ->assertNotFound();
    }
}
