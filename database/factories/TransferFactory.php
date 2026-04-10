<?php

namespace Database\Factories;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reference' => (string) Str::uuid(),
            'status' => 'PENDING_PAYMENT',
            'provider' => 'ORANGE_MONEY',
            'sender_amount_cad' => 100,
            'fee_cad' => 4,
            'exchange_rate' => 445,
            'recipient_amount_xaf' => 42720,
            'recipient_name' => fake()->name(),
            'recipient_phone' => '+2376'.fake()->numerify('#######'),
            'idempotency_key' => null,
        ];
    }
}
