<?php

namespace App\Services;

use App\Models\Transfer;
use InvalidArgumentException;

class TransferStatusMachine
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'CREATED' => ['PENDING_PAYMENT', 'CANCELLED'],
        'PENDING_PAYMENT' => ['PAID', 'CANCELLED'],
        'PAID' => ['PROCESSING', 'FAILED'],
        'PROCESSING' => ['COMPLETED', 'FAILED'],
        'FAILED' => ['PROCESSING'],
        'COMPLETED' => [],
        'CANCELLED' => [],
    ];

    public function transition(Transfer $transfer, string $to): void
    {
        $from = $transfer->status;
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        if (! in_array($to, $allowed, true)) {
            throw new InvalidArgumentException("Invalid transition from {$from} to {$to}");
        }

        $transfer->status = $to;
    }
}
