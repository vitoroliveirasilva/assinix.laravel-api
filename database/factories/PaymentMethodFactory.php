<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(PaymentMethodType::cases());

        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'type' => $type,
            'brand' => in_array($type, [PaymentMethodType::CreditCard, PaymentMethodType::DebitCard], true)
                ? fake()->randomElement(['Visa', 'Mastercard', 'Elo'])
                : null,
            'last_four' => in_array($type, [PaymentMethodType::CreditCard, PaymentMethodType::DebitCard], true)
                ? fake()->numerify('####')
                : null,
            'is_active' => true,
        ];
    }

    public function creditCard(): static
    {
        return $this->state(fn(array $attributes): array => [
            'type' => PaymentMethodType::CreditCard,
            'brand' => 'Visa',
            'last_four' => '1234',
        ]);
    }

    public function pix(): static
    {
        return $this->state(fn(array $attributes): array => [
            'type' => PaymentMethodType::Pix,
            'brand' => null,
            'last_four' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes): array => [
            'is_active' => false,
        ]);
    }
}