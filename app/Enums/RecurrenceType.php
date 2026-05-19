<?php

namespace App\Enums;

enum RecurrenceType: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Monthly => 'Mensal',
            self::Yearly => 'Anual',
            self::Custom => 'Personalizada',
        };
    }

    public static function values(): array
    {
        return array_map(
            callback: static fn (self $recurrence): string => $recurrence->value,
            array: self::cases(),
        );
    }
}
