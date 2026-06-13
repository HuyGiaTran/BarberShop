<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTokenCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_removes_old_tokens_before_issuing_a_new_one(): void
    {
        $user = User::factory()->create([
            'email' => 'cleanup@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
        ]);

        $firstResponse = $this->postJson('/api/login', [
            'email' => 'cleanup@example.com',
            'password' => 'secret123',
        ]);

        $firstResponse->assertOk();
        $firstToken = $firstResponse->json('data.token');

        $user->refresh();
        $this->assertSame(1, $user->tokens()->count());

        $secondResponse = $this->postJson('/api/login', [
            'email' => 'cleanup@example.com',
            'password' => 'secret123',
        ]);

        $secondResponse->assertOk();
        $secondToken = $secondResponse->json('data.token');

        $user->refresh();
        $this->assertSame(1, $user->tokens()->count());
        $this->assertNotSame($firstToken, $secondToken);
    }
}
