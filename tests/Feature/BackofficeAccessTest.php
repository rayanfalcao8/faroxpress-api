<?php

namespace Tests\Feature;

use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackofficeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_ping_requires_authentication(): void
    {
        $this->getJson('/api/backoffice/ping')->assertUnauthorized();
    }

    public function test_backoffice_ping_forbids_user_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User->value,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/backoffice/ping')
            ->assertForbidden();
    }

    public function test_backoffice_ping_allows_operator_role(): void
    {
        $operator = User::factory()->operator()->create();

        $token = $operator->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/backoffice/ping')
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'ping' => 'pong',
            ]);
    }

    public function test_backoffice_ping_allows_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $token = $admin->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/backoffice/ping')
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'ping' => 'pong',
            ]);
    }
}
