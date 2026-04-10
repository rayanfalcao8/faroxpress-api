<?php

namespace App\Services;

use App\Enums\TransferStatus;
use App\Models\Transfer;
use InvalidArgumentException;

class TransferStatusMachine
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        TransferStatus::CREATED->value => [TransferStatus::PENDING_PAYMENT->value, TransferStatus::CANCELLED->value],
        TransferStatus::PENDING_PAYMENT->value => [TransferStatus::PAID->value, TransferStatus::CANCELLED->value],
        TransferStatus::PAID->value => [TransferStatus::PROCESSING->value, TransferStatus::FAILED->value],
        TransferStatus::PROCESSING->value => [TransferStatus::COMPLETED->value, TransferStatus::FAILED->value],
        TransferStatus::FAILED->value => [TransferStatus::PROCESSING->value],
        TransferStatus::COMPLETED->value => [],
        TransferStatus::CANCELLED->value => [],
    ];

    public function transition(Transfer $transfer, TransferStatus $to): void
    {
        $from = $transfer->status instanceof TransferStatus ? $transfer->status->value : (string) $transfer->status;
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        if (! in_array($to->value, $allowed, true)) {
            throw new InvalidArgumentException("Invalid transition from {$from} to {$to->value}");
        }

        $transfer->status = $to;
    }
}
