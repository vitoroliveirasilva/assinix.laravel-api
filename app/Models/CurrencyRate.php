<?php

namespace App\Models;

use App\Enums\CurrencyCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'source',
        'quoted_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'base_currency' => CurrencyCode::class,
            'target_currency' => CurrencyCode::class,
            'rate' => 'decimal:8',
            'quoted_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function scopePair(
        Builder $query,
        CurrencyCode $baseCurrency,
        CurrencyCode $targetCurrency,
    ): Builder {
        return $query
            ->where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('quoted_at')
            ->orderByDesc('created_at');
    }
}