<?php

namespace App\Services;

use App\Models\Transfer;

class ProviderStubService
{
    public function payout(Transfer $transfer, bool $simulateFailure = false): array
    {
        if ($simulateFailure) {
            return [
                'ok' => false,
                'provider_reference' => null,
                'failure_reason' => 'Simulated provider failure',
            ];
        }

        return [
            'ok' => true,
            'provider_reference' => 'ORANGE-'.str_pad((string) $transfer->id, 8, '0', STR_PAD_LEFT),
            'failure_reason' => null,
        ];
    }
}
