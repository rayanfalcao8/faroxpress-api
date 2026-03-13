<?php

namespace App\Http\Controllers\Api\Transfers;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowTransferController extends Controller
{
    public function __invoke(Request $request, Transfer $transfer): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($transfer->user_id !== $user->id) {
            abort(404);
        }

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
