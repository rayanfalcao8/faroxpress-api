<?php

namespace App\Http\Controllers\Api\Transfer;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTransfersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $transfers = Transfer::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function (Transfer $transfer): array {
                return [
                    'id' => $transfer->id,
                    'status' => $transfer->status->value,
                    'created_at' => $transfer->created_at?->toISOString(),
                    'updated_at' => $transfer->updated_at?->toISOString(),
                ];
            })
            ->all();

        return response()->json([
            'transfers' => $transfers,
        ]);
    }
}
