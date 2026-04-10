<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_and_logout(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $register->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonStructure(['token']);

        $token = $login->json('token');

        $logout = $this->withToken($token)->postJson('/api/auth/logout');

        $logout->assertOk()->assertJson(['message' => 'Logged out']);
    }
}
