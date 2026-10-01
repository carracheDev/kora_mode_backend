<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FedaPayWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_approved_event_confirms_an_order_and_is_idempotent(): void
    {
        config()->set('services.fedapay.webhook_secret', 'webhook-secret');
        $order = Order::factory()->create([
            'fedapay_transaction_id' => 'fedapay-456',
            'payment_method' => 'mtn',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $event = [
            'id' => 'evt-approved-1',
            'name' => 'transaction.approved',
            'entity' => [
                'id' => 'fedapay-456',
                'custom_metadata' => ['order_number' => $order->order_number],
            ],
        ];
        $payload = json_encode($event, JSON_THROW_ON_ERROR);

        $this->sendSignedEvent($payload)->assertOk()->assertJsonPath('duplicate', false);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);

        $this->sendSignedEvent($payload)->assertOk()->assertJsonPath('duplicate', true);
        $this->assertDatabaseCount('webhook_events', 1);
    }

    public function test_invalid_signature_does_not_change_order_state(): void
    {
        config()->set('services.fedapay.webhook_secret', 'webhook-secret');
        $order = Order::factory()->create([
            'fedapay_transaction_id' => 'fedapay-789',
            'payment_method' => 'moov',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $payload = json_encode([
            'id' => 'evt-forged',
            'name' => 'transaction.approved',
            'entity' => ['id' => 'fedapay-789', 'custom_metadata' => ['order_number' => $order->order_number]],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', '/api/v1/webhooks/fedapay', [], [], [], [
            'HTTP_X_FEDAPAY_SIGNATURE' => 't='.time().',s=bad-signature',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertBadRequest();

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseCount('webhook_events', 0);
    }

    public function test_signed_failed_event_releases_reserved_stock_once(): void
    {
        config()->set('services.fedapay.webhook_secret', 'webhook-secret');
        $product = Product::factory()->create(['stock' => 2]);
        $order = Order::factory()->create([
            'fedapay_transaction_id' => 'fedapay-901',
            'payment_method' => 'celtiis',
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
        $payload = json_encode([
            'id' => 'evt-failed-1',
            'name' => 'transaction.canceled',
            'entity' => ['id' => 'fedapay-901', 'custom_metadata' => ['order_number' => $order->order_number]],
        ], JSON_THROW_ON_ERROR);

        $this->sendSignedEvent($payload)->assertOk();
        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_released_at);

        $this->sendSignedEvent($payload)->assertOk();
        $this->assertSame(4, $product->fresh()->stock);
    }

    private function sendSignedEvent(string $payload): TestResponse
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'webhook-secret');

        return $this->call('POST', '/api/v1/webhooks/fedapay', [], [], [], [
            'HTTP_X_FEDAPAY_SIGNATURE' => "t={$timestamp},s={$signature}",
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
    }
}
