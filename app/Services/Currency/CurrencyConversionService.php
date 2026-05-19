<?php

namespace App\Services\Currency;

use App\Enums\CurrencyCode;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CurrencyConversionService
{
    public function __construct(
        private readonly AwesomeApiCurrencyClient $client,
    ) {}

    public function convert(
        float $amount,
        CurrencyCode $fromCurrency,
        CurrencyCode $toCurrency = CurrencyCode::BRL,
        bool $forceRefresh = false,
    ): array {
        if ($fromCurrency === $toCurrency) {
            return [
                'original_amount' => round($amount, 2),
                'converted_amount' => round($amount, 2),
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'rate' => 1.0,
                'rate_model' => null,
                'conversion_source' => 'base_currency',
                'quoted_at' => now(),
            ];
        }

        $rateResult = $this->resolveRate(
            baseCurrency: $fromCurrency,
            targetCurrency: $toCurrency,
            forceRefresh: $forceRefresh,
        );

        $rateValue = (float) $rateResult['rate']->rate;

        return [
            'original_amount' => round($amount, 2),
            'converted_amount' => round($amount * $rateValue, 2),
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'rate' => $rateValue,
            'rate_model' => $rateResult['rate'],
            'conversion_source' => $rateResult['source'],
            'quoted_at' => $rateResult['rate']->quoted_at,
        ];
    }

    public function resolveRate(
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency = CurrencyCode::BRL,
        bool $forceRefresh = false,
    ): array {
        if ($baseCurrency === $targetCurrency) {
            throw ValidationException::withMessages([
                'currency' => ['Não é necessário buscar cotação entre moedas iguais.'],
            ]);
        }

        if (! $forceRefresh) {
            $freshRate = $this->freshCachedRate($baseCurrency, $targetCurrency);

            if ($freshRate !== null) {
                return [
                    'rate' => $freshRate,
                    'source' => 'cache',
                ];
            }
        }

        try {
            $payload = $this->client->fetchLatest($baseCurrency, $targetCurrency);

            return [
                'rate' => $this->storeRate($payload),
                'source' => 'api',
            ];
        } catch (Throwable) {
            $fallback = $this->latestKnownRate($baseCurrency, $targetCurrency);

            if ($fallback !== null) {
                return [
                    'rate' => $fallback,
                    'source' => 'fallback',
                ];
            }

            throw ValidationException::withMessages([
                'currency' => [
                    "Não há cotação disponível para {$baseCurrency->value}-{$targetCurrency->value}, tente atualizar novamente em instantes.",
                ],
            ]);
        }
    }

    public function latestKnownRate(
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency = CurrencyCode::BRL,
    ): ?CurrencyRate {
        return CurrencyRate::query()
            ->pair($baseCurrency, $targetCurrency)
            ->latestFirst()
            ->first();
    }

    public function refreshUserSubscriptions(
        int $userId,
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency = CurrencyCode::BRL,
    ): int {
        if ($baseCurrency === $targetCurrency) {
            return 0;
        }

        $rateResult = $this->resolveRate(
            baseCurrency: $baseCurrency,
            targetCurrency: $targetCurrency,
            forceRefresh: true,
        );

        /** @var CurrencyRate $rate */
        $rate = $rateResult['rate'];
        $rateValue = (float) $rate->rate;
        $updated = 0;

        DB::table('subscriptions')
            ->where('user_id', $userId)
            ->where('currency', $baseCurrency->value)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use ($rate, $rateValue, &$updated): void {
                foreach ($subscriptions as $subscription) {
                    DB::table('subscriptions')
                        ->where('id', $subscription->id)
                        ->update([
                            'amount_brl' => round((float) $subscription->amount * $rateValue, 2),
                            'exchange_rate' => $rate->rate,
                            'exchange_rate_date' => $rate->quoted_at?->toDateString() ?? now()->toDateString(),
                            'updated_at' => now(),
                        ]);

                    $updated++;
                }
            });

        return $updated;
    }

    private function freshCachedRate(
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency,
    ): ?CurrencyRate {
        $cacheMinutes = (int) config('services.awesomeapi.cache_minutes', 60);

        return CurrencyRate::query()
            ->pair($baseCurrency, $targetCurrency)
            ->where('updated_at', '>=', now()->subMinutes($cacheMinutes))
            ->latestFirst()
            ->first();
    }

    private function storeRate(array $payload): CurrencyRate
    {
        return CurrencyRate::query()->updateOrCreate(
            [
                'base_currency' => $payload['base_currency'],
                'target_currency' => $payload['target_currency'],
                'source' => $payload['source'],
                'quoted_at' => $payload['quoted_at'],
            ],
            [
                'rate' => $payload['rate'],
                'payload' => $payload['payload'],
            ],
        );
    }
}
