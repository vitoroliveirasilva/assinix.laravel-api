<?php

namespace App\Services\Currency;

use App\Enums\CurrencyCode;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AwesomeApiCurrencyClient
{
    public function fetchLatest(
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency = CurrencyCode::BRL,
    ): array {
        $pair = "{$baseCurrency->value}-{$targetCurrency->value}";
        $key = "{$baseCurrency->value}{$targetCurrency->value}";

        $response = Http::baseUrl((string) config('services.awesomeapi.base_url'))
            ->acceptJson()
            ->timeout((int) config('services.awesomeapi.timeout', 5))
            ->retry(
                times: (int) config('services.awesomeapi.retry_times', 2),
                sleepMilliseconds: (int) config('services.awesomeapi.retry_sleep_ms', 200),
            )
            ->get("/json/last/{$pair}");

        if (! $response->successful()) {
            throw new RuntimeException("AwesomeAPI request failed for pair {$pair}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload[$key]) || ! is_array($payload[$key])) {
            throw new RuntimeException("AwesomeAPI returned an invalid payload for pair {$pair}.");
        }

        $quote = $payload[$key];

        if (! isset($quote['bid']) || ! is_numeric($quote['bid'])) {
            throw new RuntimeException("AwesomeAPI returned an invalid bid for pair {$pair}.");
        }

        return [
            'base_currency' => $baseCurrency,
            'target_currency' => $targetCurrency,
            'rate' => (string) $quote['bid'],
            'source' => 'awesomeapi',
            'quoted_at' => isset($quote['create_date'])
                ? CarbonImmutable::parse($quote['create_date'])
                : now()->toImmutable(),
            'payload' => $quote,
        ];
    }
}
