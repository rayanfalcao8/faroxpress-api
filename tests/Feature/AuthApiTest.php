<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_valid_payload(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_login_me_logout_flow(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Rayan',
            'email' => 'rayan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'rayan@test.com',
            'password' => 'password123',
        ])->assertOk();

        $token = $login->json('token');
        $this->assertNotEmpty($token);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email']]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'rayan@test.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'rayan@test.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    public function test_forgot_and_reset_password(): void
    {
        Notification::fake();

        User::query()->create([
            'name' => 'Rayan',
            'email' => 'rayan@test.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'rayan@test.com',
        ])->assertOk()->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'rayan@test.com')->first();

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->assertNotEmpty($token);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'rayan@test.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->postJson('/api/auth/login', [
            'email' => 'rayan@test.com',
            'password' => 'newpassword123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'rayan@test.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'rayan@test.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertUnprocessable()
            ->assertJson([
                'ok' => false,
            ]);
    }
}
