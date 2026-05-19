<?php

namespace App\Actions\PaymentMethods;

use App\Models\PaymentMethod;

class UpdatePaymentMethodAction
{
    public function execute(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->fill([
            'name' => $data['name'] ?? $paymentMethod->name,
            'type' => $data['type'] ?? $paymentMethod->type,
            'brand' => array_key_exists('brand', $data)
                ? $data['brand']
                : $paymentMethod->brand,
            'last_four' => array_key_exists('last_four', $data)
                ? $data['last_four']
                : $paymentMethod->last_four,
            'is_active' => array_key_exists('is_active', $data)
                ? $data['is_active']
                : $paymentMethod->is_active,
        ]);

        $paymentMethod->save();

        return $paymentMethod->refresh();
    }
}
