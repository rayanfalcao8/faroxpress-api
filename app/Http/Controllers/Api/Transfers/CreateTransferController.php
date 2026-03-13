<?php

namespace App\Http\Controllers\Api\Transfers;

use App\Domain\Transfers\TransferStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequest;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class CreateTransferController extends Controller
{
    public function __invoke(StoreTransferRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $transfer = Transfer::query()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::CREATED,
            'provider' => (string) $request->input('provider'),
            'amount_cad' => $request->input('amount_cad'),
            'fee_cad' => $request->input('fee_cad'),
            'rate' => $request->input('rate'),
            'amount_xaf' => $request->input('amount_xaf'),
            'recipient_name' => (string) $request->input('recipient_name'),
            'recipient_phone' => (string) $request->input('recipient_phone'),
        ]);

        AuditLog::query()->create([
            'actor_type' => 'USER',
            'actor_id' => $user->id,
            'action' => 'TRANSFER_CREATED',
            'entity_type' => 'Transfer',
            'entity_id' => $transfer->id,
            'metadata' => [
                'status' => $transfer->status,
            ],
        ]);

        return response()->json([
            'transfer' => [
                'id' => $transfer->id,
                'status' => $transfer->status,
                'provider' => $transfer->provider,
                'amount_cad' => $transfer->amount_cad,
                'recipient_name' => $transfer->recipient_name,
                'recipient_phone' => $transfer->recipient_phone,
                'created_at' => $transfer->created_at?->toISOString(),
                'updated_at' => $transfer->updated_at?->toISOString(),
            ],
        ], 201);
    }
}
