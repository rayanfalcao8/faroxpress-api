<?php

namespace Tests\Feature;

use App\Domain\Transfers\TransferStatus;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_returns_uuid_and_writes_audit_log(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfers', [
                'provider' => 'ORANGE_MONEY',
                'amount_cad' => 100.50,
                'fee_cad' => 2.00,
                'rate' => 410.123456,
                'amount_xaf' => 41267.41,
                'recipient_name' => 'Jane Doe',
                'recipient_phone' => '+237690000001',
            ]);

        $response->assertCreated()
            ->assertJsonPath('transfer.status', TransferStatus::CREATED);

        $transferId = (string) $response->json('transfer.id');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $transferId
        );

        $this->assertDatabaseHas('transfers', [
            'id' => $transferId,
            'user_id' => $user->id,
            'status' => TransferStatus::CREATED,
            'provider' => 'ORANGE_MONEY',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'USER',
            'actor_id' => $user->id,
            'action' => 'TRANSFER_CREATED',
            'entity_type' => 'Transfer',
            'entity_id' => $transferId,
        ]);
    }

    public function test_user_b_cannot_get_transfer_of_user_a(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $tokenB = $userB->createToken('api')->plainTextToken;

        $transferA = Transfer::factory()->create([
            'user_id' => $userA->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/transfers/{$transferA->id}")
            ->assertNotFound();
    }

    public function test_cancel_is_allowed_when_created(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $transfer = Transfer::factory()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::CREATED,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$transfer->id}/cancel")
            ->assertOk()
            ->assertJsonPath('transfer.status', TransferStatus::CANCELLED);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'USER',
            'actor_id' => $user->id,
            'action' => 'TRANSFER_STATUS_CHANGED',
            'entity_id' => $transfer->id,
        ]);
    }

    public function test_cancel_is_forbidden_when_paid(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $transfer = Transfer::factory()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::PAID,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$transfer->id}/cancel")
            ->assertUnprocessable()
            ->assertJson([
                'ok' => false,
            ]);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'TRANSFER_STATUS_CHANGED',
            'entity_id' => $transfer->id,
        ]);
    }
}
