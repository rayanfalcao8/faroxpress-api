<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Services\RateFeeCalculator;
use App\Services\TransferStatusMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function __construct(
        private readonly RateFeeCalculator $calculator,
        private readonly TransferStatusMachine $statusMachine,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $transfers = Transfer::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($transfers);
    }

    public function show(Request $request, Transfer $transfer): JsonResponse
    {
        abort_if($transfer->user_id !== $request->user()->id, 403, 'Forbidden');

        return response()->json($transfer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_amount_cad' => ['required', 'numeric', 'min:10'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
        ]);

        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey) {
            $existing = Transfer::where('user_id', $request->user()->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return response()->json($existing);
            }
        }

        $pricing = $this->calculator->calculate((float) $validated['sender_amount_cad']);

        $transfer = Transfer::create([
            'user_id' => $request->user()->id,
            'reference' => (string) Str::uuid(),
            'status' => TransferStatus::CREATED,
            'provider' => 'ORANGE_MONEY',
            'sender_amount_cad' => $validated['sender_amount_cad'],
            'fee_cad' => $pricing['fee_cad'],
            'exchange_rate' => $pricing['exchange_rate'],
            'recipient_amount_xaf' => $pricing['recipient_amount_xaf'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->statusMachine->transition($transfer, TransferStatus::PENDING_PAYMENT);
        $transfer->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'transfer.created',
            'auditable_type' => Transfer::class,
            'auditable_id' => $transfer->id,
            'metadata' => ['status' => $transfer->status->value],
        ]);

        return response()->json($transfer, 201);
    }
}
