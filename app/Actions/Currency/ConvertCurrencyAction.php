<?php

namespace App\Actions\Currency;

use App\Enums\CurrencyCode;
use App\Services\Currency\CurrencyConversionService;

class ConvertCurrencyAction
{
    public function __construct(
        private readonly CurrencyConversionService $conversionService,
    ) {}

    public function execute(array $data): array
    {
        return $this->conversionService->convert(
            amount: (float) $data['amount'],
            fromCurrency: CurrencyCode::from($data['from_currency']),
            toCurrency: CurrencyCode::from($data['to_currency'] ?? CurrencyCode::BRL->value),
            forceRefresh: (bool) ($data['force_refresh'] ?? false),
        );
    }
}
