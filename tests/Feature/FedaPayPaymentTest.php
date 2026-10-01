<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FedaPayPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_money_order_receives_a_fedapay_checkout_url(): void
    {
        config()->set('services.fedapay.driver', 'fedapay');
        config()->set('services.fedapay.secret_key', 'sandbox-secret');
        config()->set('services.fedapay.base_url', 'https://sandbox-api.fedapay.com/v1');
        config()->set('services.fedapay.frontend_url', 'https://kora.example');
        Http::preventStrayRequests();
        Http::fake([
            'sandbox-api.fedapay.com/v1/transactions' => Http::response(['v1/transaction' => ['id' => 456]], 201),
            'sandbox-api.fedapay.com/v1/transactions/456/token' => Http::response(['url' => 'https://fpay.li/demo-checkout'], 200),
        ]);
        $order = Order::factory()->create([
            'payment_method' => 'mtn',
            'payment_status' => 'pending',
            'customer_email' => 'awa@example.com',
        ]);

        $this->postJson("/api/v1/orders/{$order->order_number}/payments")
            ->assertOk()
            ->assertJsonPath('data.payment_url', 'https://fpay.li/demo-checkout');

        $this->assertSame('456', $order->fresh()->fedapay_transaction_id);
        $this->assertSame('https://fpay.li/demo-checkout', $order->fresh()->payment_url);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/transactions')
            && $request['amount'] === $order->total
            && $request['currency']['iso'] === 'XOF'
            && str_contains($request['callback_url'], $order->order_number));
    }

    public function test_mobile_money_payment_is_unavailable_without_sandbox_credentials(): void
    {
        config()->set('services.fedapay.driver', 'fedapay');
        config()->set('services.fedapay.secret_key', null);
        $order = Order::factory()->create(['payment_method' => 'moov']);

        $this->postJson("/api/v1/orders/{$order->order_number}/payments")
            ->assertServiceUnavailable();
    }
}
