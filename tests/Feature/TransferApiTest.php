<?php

namespace Tests\Feature;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list_and_show_transfers(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)
            ->postJson('/api/transfers', [
                'sender_amount_cad' => 100,
                'recipient_name' => 'Paul M.',
                'recipient_phone' => '+237612345678',
            ]);

        $create->assertCreated()
            ->assertJsonPath('status', 'PENDING_PAYMENT')
            ->assertJsonStructure(['id', 'reference', 'fee_cad', 'exchange_rate', 'recipient_amount_xaf']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'transfer.created',
            'auditable_id' => $create->json('id'),
        ]);

        $list = $this->actingAs($user)->getJson('/api/transfers');
        $list->assertOk()->assertJsonPath('data.0.id', $create->json('id'));

        $show = $this->actingAs($user)->getJson('/api/transfers/'.$create->json('id'));
        $show->assertOk()->assertJsonPath('id', $create->json('id'));
    }

    public function test_create_transfer_is_idempotent_with_header(): void
    {
        $user = User::factory()->create();

        $payload = [
            'sender_amount_cad' => 120,
            'recipient_name' => 'A B',
            'recipient_phone' => '+237699000111',
        ];

        $first = $this->actingAs($user)
            ->withHeader('Idempotency-Key', 'same-key-1')
            ->postJson('/api/transfers', $payload);

        $second = $this->actingAs($user)
            ->withHeader('Idempotency-Key', 'same-key-1')
            ->postJson('/api/transfers', $payload);

        $first->assertCreated();
        $second->assertOk();
        $this->assertSame($first->json('id'), $second->json('id'));

        $this->assertDatabaseCount('transfers', 1);
    }

    public function test_operator_can_mark_paid_with_failure_then_retry_to_completed(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $transfer = Transfer::factory()->create(['status' => 'PENDING_PAYMENT']);

        $markPaid = $this->actingAs($operator)
            ->postJson('/api/transfers/'.$transfer->id.'/mark-paid', ['simulate_failure' => true]);

        $markPaid->assertOk()->assertJsonPath('status', 'FAILED');

        $retry = $this->actingAs($operator)
            ->postJson('/api/transfers/'.$transfer->id.'/retry');

        $retry->assertOk()->assertJsonPath('status', 'COMPLETED');

        $this->assertDatabaseHas('audit_logs', ['action' => 'transfer.mark_paid']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'transfer.retry']);
    }

    public function test_regular_user_cannot_mark_paid(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $transfer = Transfer::factory()->create(['status' => 'PENDING_PAYMENT']);

        $response = $this->actingAs($user)
            ->postJson('/api/transfers/'.$transfer->id.'/mark-paid');

        $response->assertForbidden();
    }
}
