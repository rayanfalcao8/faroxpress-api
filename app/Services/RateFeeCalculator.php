<?php

namespace App\Services;

class RateFeeCalculator
{
    public function calculate(float $senderAmountCad): array
    {
        $rate = (float) config('services.transfer.rate_cad_to_xaf', 445.0);
        $fixedFee = (float) config('services.transfer.fee_cad_fixed', 2.0);
        $percentFee = (float) config('services.transfer.fee_cad_percent', 0.02);

        $feeCad = round($fixedFee + ($senderAmountCad * $percentFee), 2);
        $recipientAmountXaf = round(max($senderAmountCad - $feeCad, 0) * $rate, 2);

        return [
            'fee_cad' => $feeCad,
            'exchange_rate' => $rate,
            'recipient_amount_xaf' => $recipientAmountXaf,
        ];
    }
}
