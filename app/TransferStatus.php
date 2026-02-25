<?php

namespace App;

enum TransferStatus: string
{
    case Created = 'CREATED';
    case PendingPayment = 'PENDING_PAYMENT';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Created, self::PendingPayment], true);
    }
}
