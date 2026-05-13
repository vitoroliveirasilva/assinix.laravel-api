<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (!$user) {
            return;
        }

        $paymentMethods = [
            [
                'name' => 'Cartão principal',
                'type' => PaymentMethodType::CreditCard,
                'brand' => 'Visa',
                'last_four' => '1234',
            ],
            [
                'name' => 'Pix',
                'type' => PaymentMethodType::Pix,
                'brand' => null,
                'last_four' => null,
            ],
            [
                'name' => 'Boleto',
                'type' => PaymentMethodType::BankSlip,
                'brand' => null,
                'last_four' => null,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            $user->paymentMethods()->firstOrCreate(
                ['name' => $paymentMethod['name']],
                $paymentMethod,
            );
        }
    }
}