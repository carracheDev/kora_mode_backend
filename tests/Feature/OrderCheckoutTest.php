<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_place_a_cash_on_delivery_order_and_stock_is_reserved(): void
    {
        $product = Product::factory()->create([
            'price' => 12000,
            'stock' => 5,
            'sizes' => ['S', 'M'],
            'colors' => [['name' => 'Noir', 'hex' => '#111111']],
        ]);

        $response = $this->postJson('/api/v1/orders', $this->orderPayload($product));

        $response->assertCreated()
            ->assertJsonPath('data.subtotal', 24000)
            ->assertJsonPath('data.delivery_fee', 1000)
            ->assertJsonPath('data.discount_amount', 0)
            ->assertJsonPath('data.total', 25000)
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'customer_phone' => '+2290190000000',
            'payment_method' => 'cod',
            'total' => 25000,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, Order::query()->count());
    }

    public function test_order_applies_only_a_valid_promo_to_eligible_items(): void
    {
        $eligible = Product::factory()->create([
            'slug' => 'promo-piece',
            'price' => 20000,
            'stock' => 5,
            'sizes' => ['M'],
            'colors' => [['name' => 'Noir', 'hex' => '#111111']],
        ]);
        $regular = Product::factory()->create([
            'price' => 10000,
            'stock' => 5,
            'sizes' => ['M'],
            'colors' => [['name' => 'Noir', 'hex' => '#111111']],
        ]);
        PromoCode::factory()->create([
            'code' => 'BF40',
            'discount_percent' => 40,
            'eligible_slugs' => ['promo-piece'],
        ]);

        $payload = $this->orderPayload($eligible);
        $payload['items'][0]['quantity'] = 1;
        $payload['items'][] = [
            'product_slug' => $regular->slug,
            'quantity' => 1,
            'size' => 'M',
            'color' => 'Noir',
        ];
        $payload['promo_code'] = 'bf40';

        $this->postJson('/api/v1/orders', $payload)->assertCreated()
            ->assertJsonPath('data.subtotal', 30000)
            ->assertJsonPath('data.discount_amount', 8000)
            ->assertJsonPath('data.promo_code', 'BF40')
            ->assertJsonPath('data.total', 23000);
    }

    public function test_insufficient_stock_rejects_the_order_without_reserving_anything(): void
    {
        $product = Product::factory()->create([
            'stock' => 1,
            'sizes' => ['M'],
            'colors' => [['name' => 'Noir', 'hex' => '#111111']],
        ]);
        $payload = $this->orderPayload($product);
        $payload['items'][0]['quantity'] = 2;

        $this->postJson('/api/v1/orders', $payload)->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_complete_look_discount_is_recomputed_from_the_server_catalog(): void
    {
        $look = [
            ['slug' => 'blazer-oversize-nova', 'price' => 10000],
            ['slug' => 'pantalon-wide-leg-atlas', 'price' => 20000],
            ['slug' => 'sneakers-blanc-studio', 'price' => 30000],
        ];
        $payload = $this->orderPayload(Product::factory()->create([
            'slug' => $look[0]['slug'],
            'price' => $look[0]['price'],
            'stock' => 5,
            'sizes' => ['M'],
            'colors' => [['name' => 'Noir', 'hex' => '#111111']],
        ]));
        $payload['items'][0]['quantity'] = 1;
        $payload['items'][0]['bundle_id'] = 'complete-look-10';

        foreach (array_slice($look, 1) as $piece) {
            $product = Product::factory()->create([
                'slug' => $piece['slug'],
                'price' => $piece['price'],
                'stock' => 5,
                'sizes' => ['M'],
                'colors' => [['name' => 'Noir', 'hex' => '#111111']],
            ]);
            $payload['items'][] = [
                'product_slug' => $product->slug,
                'quantity' => 1,
                'size' => 'M',
                'color' => 'Noir',
                'bundle_id' => 'complete-look-10',
            ];
        }

        $this->postJson('/api/v1/orders', $payload)->assertCreated()
            ->assertJsonPath('data.subtotal', 60000)
            ->assertJsonPath('data.discount_amount', 6000)
            ->assertJsonPath('data.total', 55000);
    }

    /** @return array<string, mixed> */
    private function orderPayload(Product $product): array
    {
        return [
            'customer' => [
                'name' => 'Awa Demo',
                'phone' => '+2290190000000',
                'city' => 'Cotonou',
                'address' => 'Cadjehoun, près du marché',
            ],
            'payment_method' => 'cod',
            'items' => [[
                'product_slug' => $product->slug,
                'quantity' => 2,
                'size' => 'M',
                'color' => 'Noir',
            ]],
        ];
    }
}
