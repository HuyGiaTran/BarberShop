<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_view_another_user_profile(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $otherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($customer);

        $this->getJson("/api/users/{$otherCustomer->id}")
            ->assertForbidden();
    }

    public function test_customer_cannot_promote_self_to_admin_via_update_api(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($customer);

        $this->putJson("/api/users/{$customer->id}", [
            'name' => 'Updated Customer',
            'role' => 'admin',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'role' => 'customer',
        ]);
    }

    public function test_admin_can_view_other_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/users/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id);
    }
}
