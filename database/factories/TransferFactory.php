<?php

namespace Database\Factories;

use App\Domain\Transfers\TransferStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => TransferStatus::CREATED,
            'provider' => fake()->randomElement(['ORANGE_MONEY', 'MTN_MOMO']),
            'amount_cad' => fake()->randomFloat(2, 10, 1000),
            'fee_cad' => fake()->randomFloat(2, 0, 20),
            'rate' => fake()->randomFloat(6, 300, 700),
            'amount_xaf' => fake()->randomFloat(2, 5000, 500000),
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->e164PhoneNumber(),
            'external_ref' => null,
            'failure_reason' => null,
        ];
    }
}
