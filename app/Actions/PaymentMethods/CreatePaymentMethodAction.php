<?php

namespace App\Actions\PaymentMethods;

use App\Models\PaymentMethod;
use App\Models\User;

class CreatePaymentMethodAction
{
    public function execute(User $user, array $data): PaymentMethod
    {
        return $user->paymentMethods()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'brand' => $data['brand'] ?? null,
            'last_four' => $data['last_four'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
