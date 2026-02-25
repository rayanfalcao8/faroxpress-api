<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\User;
use App\TransferStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransfersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_transfer_returns_uuid_and_writes_audit_log(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfers');

        $response->assertCreated()
            ->assertJsonPath('transfer.status', TransferStatus::Created->value);

        $transferId = $response->json('transfer.id');

        $this->assertIsString($transferId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $transferId
        );

        $this->assertDatabaseHas('transfers', [
            'id' => $transferId,
            'user_id' => $user->id,
            'status' => TransferStatus::Created->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => 'transfer',
            'entity_id' => $transferId,
            'action' => 'transfer.created',
        ]);
    }

    public function test_user_can_only_list_and_access_own_transfers(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownerToken = $owner->createToken('api')->plainTextToken;

        $ownerTransfer = Transfer::factory()->create([
            'user_id' => $owner->id,
        ]);
        $otherTransfer = Transfer::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$ownerToken}")
            ->getJson('/api/transfers')
            ->assertOk()
            ->assertJsonCount(1, 'transfers')
            ->assertJsonPath('transfers.0.id', $ownerTransfer->id);

        $this->withHeader('Authorization', "Bearer {$ownerToken}")
            ->getJson("/api/transfers/{$ownerTransfer->id}")
            ->assertOk()
            ->assertJsonPath('transfer.id', $ownerTransfer->id);

        $this->withHeader('Authorization', "Bearer {$ownerToken}")
            ->getJson("/api/transfers/{$otherTransfer->id}")
            ->assertNotFound();

        $this->withHeader('Authorization', "Bearer {$ownerToken}")
            ->postJson("/api/transfers/{$otherTransfer->id}/cancel")
            ->assertNotFound();
    }

    public function test_cancel_is_allowed_for_created_and_pending_payment_and_writes_audit_log(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $createdTransfer = Transfer::factory()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::Created->value,
        ]);
        $pendingTransfer = Transfer::factory()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::PendingPayment->value,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$createdTransfer->id}/cancel")
            ->assertOk()
            ->assertJsonPath('transfer.status', TransferStatus::Cancelled->value);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$pendingTransfer->id}/cancel")
            ->assertOk()
            ->assertJsonPath('transfer.status', TransferStatus::Cancelled->value);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => 'transfer',
            'entity_id' => $createdTransfer->id,
            'action' => 'transfer.cancelled',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => 'transfer',
            'entity_id' => $pendingTransfer->id,
            'action' => 'transfer.cancelled',
        ]);
    }

    public function test_cancel_is_forbidden_for_non_cancellable_statuses(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $transfer = Transfer::factory()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::Completed->value,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$transfer->id}/cancel")
            ->assertUnprocessable()
            ->assertJson([
                'ok' => false,
            ]);

        $this->assertDatabaseHas('transfers', [
            'id' => $transfer->id,
            'status' => TransferStatus::Completed->value,
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'entity_type' => 'transfer',
            'entity_id' => $transfer->id,
            'action' => 'transfer.cancelled',
        ]);
    }

    public function test_audit_log_metadata_is_stored_as_json_for_create_and_cancel(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $createResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfers')
            ->assertCreated();

        $transferId = (string) $createResponse->json('transfer.id');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/transfers/{$transferId}/cancel")
            ->assertOk();

        $createdLog = AuditLog::query()
            ->where('entity_id', $transferId)
            ->where('action', 'transfer.created')
            ->first();
        $cancelledLog = AuditLog::query()
            ->where('entity_id', $transferId)
            ->where('action', 'transfer.cancelled')
            ->first();

        $this->assertNotNull($createdLog);
        $this->assertNotNull($cancelledLog);
        $this->assertSame(TransferStatus::Created->value, $createdLog->metadata['status'] ?? null);
        $this->assertSame(TransferStatus::Cancelled->value, $cancelledLog->metadata['status'] ?? null);
    }
}
