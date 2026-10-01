<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoritesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_require_authentication(): void
    {
        $this->getJson('/api/v1/favorites')->assertUnauthorized();
        $this->postJson('/api/v1/favorites', ['product_slug' => 'bomber-demo'])->assertUnauthorized();
    }

    public function test_customer_can_add_list_and_remove_a_favorite(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'slug' => 'bomber-demo',
        ]);
        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/favorites', ['product_slug' => $product->slug])
            ->assertCreated()
            ->assertJsonPath('data.product.slug', 'bomber-demo');

        $this->postJson('/api/v1/favorites', ['product_slug' => $product->slug])->assertCreated();
        $this->assertDatabaseCount('favorites', 1);

        $this->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonPath('data.0.product.slug', 'bomber-demo');

        $this->deleteJson('/api/v1/favorites/bomber-demo')->assertNoContent();
        $this->assertDatabaseCount('favorites', 0);
    }
}
