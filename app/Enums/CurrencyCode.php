<?php

namespace App\Enums;

enum CurrencyCode: string
{
    case BRL = 'BRL';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case ARS = 'ARS';
    case CAD = 'CAD';

    public function label(): string
    {
        return match ($this) {
            self::BRL => 'Real brasileiro',
            self::USD => 'Dólar americano',
            self::EUR => 'Euro',
            self::GBP => 'Libra esterlina',
            self::ARS => 'Peso argentino',
            self::CAD => 'Dólar canadense',
        };
    }

    public function isBaseCurrency(): bool
    {
        return $this === self::BRL;
    }

    public static function values(): array
    {
        return array_map(
            callback: static fn(self $currency): string => $currency->value,
            array: self::cases(),
        );
    }
}