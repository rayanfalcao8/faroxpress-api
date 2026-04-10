<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@faroxpress.test',
            'role' => UserRole::ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Operator User',
            'email' => 'operator@faroxpress.test',
            'role' => UserRole::OPERATOR,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::USER,
        ]);
    }
}
