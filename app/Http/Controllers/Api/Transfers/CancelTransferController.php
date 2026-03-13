<?php

namespace App\Http\Controllers\Api\Transfers;

use App\Domain\Transfers\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelTransferController extends Controller
{
    public function __invoke(Request $request, Transfer $transfer): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($transfer->user_id !== $user->id) {
            abort(404);
        }

        if (! TransferStatus::canCancel($transfer->status)) {
            return response()->json([
                'ok' => false,
                'message' => 'Transfer cannot be cancelled from its current status.',
            ], 422);
        }

        $previousStatus = $transfer->status;

        $transfer->update([
            'status' => TransferStatus::CANCELLED,
        ]);

        AuditLog::query()->create([
            'actor_type' => 'USER',
            'actor_id' => $user->id,
            'action' => 'TRANSFER_STATUS_CHANGED',
            'entity_type' => 'Transfer',
            'entity_id' => $transfer->id,
            'metadata' => [
                'from' => $previousStatus,
                'to' => $transfer->status,
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
        ]);
    }
}
