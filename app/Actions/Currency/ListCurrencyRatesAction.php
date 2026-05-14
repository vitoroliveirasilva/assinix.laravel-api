<?php

namespace App\Actions\Currency;

use App\Enums\CurrencyCode;
use App\Models\CurrencyRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCurrencyRatesAction
{
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return CurrencyRate::query()
            ->when(
                isset($filters['base_currency']),
                fn($query) => $query->where('base_currency', CurrencyCode::from($filters['base_currency'])),
            )
            ->when(
                isset($filters['target_currency']),
                fn($query) => $query->where('target_currency', CurrencyCode::from($filters['target_currency'])),
            )
            ->latestFirst()
            ->paginate(min(max($perPage, 1), 50))
            ->withQueryString();
    }
}