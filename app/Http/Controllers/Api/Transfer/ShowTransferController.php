<?php

namespace App\Http\Controllers\Api\Transfer;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowTransferController extends Controller
{
    public function __invoke(Request $request, string $transfer): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ownedTransfer = Transfer::query()
            ->whereKey($transfer)
            ->where('user_id', $user->id)
            ->firstOrFail();

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
