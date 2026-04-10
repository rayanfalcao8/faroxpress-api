<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Services\ProviderStubService;
use App\Services\TransferStatusMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransferPaymentController extends Controller
{
    public function __construct(
        private readonly TransferStatusMachine $statusMachine,
        private readonly ProviderStubService $provider,
    ) {
    }

    public function markPaid(Request $request, Transfer $transfer): JsonResponse
    {
        $validated = $request->validate([
            'simulate_failure' => ['nullable', 'boolean'],
        ]);

        try {
            if ($transfer->status === 'PENDING_PAYMENT' || $transfer->status === 'CREATED') {
                if ($transfer->status === 'CREATED') {
                    $this->statusMachine->transition($transfer, 'PENDING_PAYMENT');
                }

                $this->statusMachine->transition($transfer, 'PAID');
                $transfer->paid_at = now();

                $this->statusMachine->transition($transfer, 'PROCESSING');

                $result = $this->provider->payout(
                    $transfer,
                    (bool) ($validated['simulate_failure'] ?? false)
                );

                if ($result['ok']) {
                    $this->statusMachine->transition($transfer, 'COMPLETED');
                    $transfer->processed_at = now();
                    $transfer->failure_reason = null;
                } else {
                    $this->statusMachine->transition($transfer, 'FAILED');
                    $transfer->failure_reason = $result['failure_reason'];
                }

                $transfer->save();
            }
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'transfer.mark_paid',
            'auditable_type' => Transfer::class,
            'auditable_id' => $transfer->id,
            'metadata' => ['status' => $transfer->status],
        ]);

        return response()->json($transfer);
    }

    public function retry(Request $request, Transfer $transfer): JsonResponse
    {
        if ($transfer->status !== 'FAILED') {
            return response()->json([
                'message' => 'Only failed transfers can be retried.',
            ], 422);
        }

        $this->statusMachine->transition($transfer, 'PROCESSING');

        $result = $this->provider->payout($transfer, false);

        if ($result['ok']) {
            $this->statusMachine->transition($transfer, 'COMPLETED');
            $transfer->processed_at = now();
            $transfer->failure_reason = null;
        } else {
            $this->statusMachine->transition($transfer, 'FAILED');
            $transfer->failure_reason = $result['failure_reason'];
        }

        $transfer->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'transfer.retry',
            'auditable_type' => Transfer::class,
            'auditable_id' => $transfer->id,
            'metadata' => ['status' => $transfer->status],
        ]);

        return response()->json($transfer);
    }
}
