<?php

namespace App\Actions\Currency;

use App\Enums\CurrencyCode;
use App\Models\User;
use App\Services\Currency\CurrencyConversionService;

class RefreshCurrencyRateAction
{
    public function __construct(
        private readonly CurrencyConversionService $conversionService,
    ) {
    }

    public function execute(User $user, array $data): array
    {
        $baseCurrency = CurrencyCode::from($data['currency']);
        $targetCurrency = CurrencyCode::BRL;

        $rateResult = $this->conversionService->resolveRate(
            baseCurrency: $baseCurrency,
            targetCurrency: $targetCurrency,
            forceRefresh: true,
        );

        $updatedSubscriptions = 0;

        if (($data['update_subscriptions'] ?? true) === true) {
            $updatedSubscriptions = $this->conversionService->refreshUserSubscriptions(
                userId: $user->id,
                baseCurrency: $baseCurrency,
                targetCurrency: $targetCurrency,
            );
        }

        return [
            'rate' => $rateResult['rate'],
            'conversion_source' => $rateResult['source'],
            'updated_subscriptions_count' => $updatedSubscriptions,
        ];
    }
}