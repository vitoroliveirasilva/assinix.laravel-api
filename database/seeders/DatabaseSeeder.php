<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'demo@assinix.local'],
            [
                'name' => 'Demo Assinix',
                'password' => 'Password123',
                'is_active' => true,
            ],
        );

        $this->call([
            CategorySeeder::class,
            PaymentMethodSeeder::class,
        ]);
    }
}