<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'base_currency' => CurrencyCode::USD,
            'target_currency' => CurrencyCode::BRL,
            'rate' => fake()->randomFloat(8, 4, 8),
            'source' => 'awesomeapi',
            'quoted_at' => now(),
            'payload' => [
                'code' => 'USD',
                'codein' => 'BRL',
                'bid' => '5.0000',
            ],
        ];
    }

    public function usdToBrl(float $rate = 5.0): static
    {
        return $this->state(fn (): array => [
            'base_currency' => CurrencyCode::USD,
            'target_currency' => CurrencyCode::BRL,
            'rate' => $rate,
            'source' => 'awesomeapi',
            'quoted_at' => now(),
            'payload' => [
                'code' => 'USD',
                'codein' => 'BRL',
                'bid' => (string) $rate,
            ],
        ]);
    }

    public function eurToBrl(float $rate = 6.0): static
    {
        return $this->state(fn (): array => [
            'base_currency' => CurrencyCode::EUR,
            'target_currency' => CurrencyCode::BRL,
            'rate' => $rate,
            'source' => 'awesomeapi',
            'quoted_at' => now(),
            'payload' => [
                'code' => 'EUR',
                'codein' => 'BRL',
                'bid' => (string) $rate,
            ],
        ]);
    }
}
