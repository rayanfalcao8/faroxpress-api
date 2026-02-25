<?php

namespace App\Http\Controllers\Api\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequest;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\User;
use App\TransferStatus;
use Illuminate\Http\JsonResponse;

class StoreTransferController extends Controller
{
    public function __invoke(StoreTransferRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $transfer = Transfer::query()->create([
            'user_id' => $user->id,
            'status' => TransferStatus::Created,
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'entity_type' => 'transfer',
            'entity_id' => $transfer->id,
            'action' => 'transfer.created',
            'metadata' => [
                'status' => $transfer->status->value,
            ],
        ]);

        return response()->json([
            'transfer' => [
                'id' => $transfer->id,
                'status' => $transfer->status->value,
                'created_at' => $transfer->created_at?->toISOString(),
                'updated_at' => $transfer->updated_at?->toISOString(),
            ],
        ], 201);
    }
}
