<?php

namespace App\Domain\Transfers;

final class TransferStatus
{
    public const CREATED = 'CREATED';

    public const PENDING_PAYMENT = 'PENDING_PAYMENT';

    public const PAID = 'PAID';

    public const PROCESSING = 'PROCESSING';

    public const COMPLETED = 'COMPLETED';

    public const FAILED = 'FAILED';

    public const CANCELLED = 'CANCELLED';

    public static function canCancel(string $status): bool
    {
        return in_array($status, [self::CREATED, self::PENDING_PAYMENT], true);
    }

    public static function isFinal(string $status): bool
    {
        return in_array($status, [self::COMPLETED, self::FAILED, self::CANCELLED], true);
    }
}
