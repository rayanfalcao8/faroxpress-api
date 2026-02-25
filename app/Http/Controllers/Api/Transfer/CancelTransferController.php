<?php

namespace App\Http\Controllers\Api\Transfer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\User;
use App\TransferStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelTransferController extends Controller
{
    public function __invoke(Request $request, string $transfer): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ownedTransfer = Transfer::query()
            ->whereKey($transfer)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $ownedTransfer->status->canBeCancelled()) {
            return response()->json([
                'ok' => false,
                'message' => 'Transfer cannot be cancelled from its current status.',
            ], 422);
        }

        $ownedTransfer->update([
            'status' => TransferStatus::Cancelled,
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'entity_type' => 'transfer',
            'entity_id' => $ownedTransfer->id,
            'action' => 'transfer.cancelled',
            'metadata' => [
                'status' => $ownedTransfer->status->value,
            ],
        ]);

        return response()->json([
            'transfer' => [
                'id' => $ownedTransfer->id,
                'status' => $ownedTransfer->status->value,
                'created_at' => $ownedTransfer->created_at?->toISOString(),
                'updated_at' => $ownedTransfer->updated_at?->toISOString(),
            ],
        ]);
    }
}
