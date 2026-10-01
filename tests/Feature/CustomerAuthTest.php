<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_read_their_profile(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Demo',
            'email' => 'AWA.DEMO@example.com',
            'phone' => '+2290100000000',
            'password' => 'DemoPassword2026',
            'password_confirmation' => 'DemoPassword2026',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'awa.demo@example.com')
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', [
            'email' => 'awa.demo@example.com',
            'phone' => '+2290100000000',
        ]);

        $this->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.name', 'Awa Demo');
    }

    public function test_customer_can_log_in_and_log_out(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'DemoPassword2026',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'CUSTOMER@example.com',
            'password' => 'DemoPassword2026',
        ])->assertOk()->assertJsonPath('data.email', 'customer@example.com');

        $this->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_registration_requires_a_confirmed_strong_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Demo',
            'email' => 'awa@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}
