<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Paused => 'Pausada',
            self::Canceled => 'Cancelada',
            self::Expired => 'Expirada',
        };
    }

    public function entersFutureProjection(): bool
    {
        return $this === self::Active;
    }

    public static function values(): array
    {
        return array_map(
            callback: static fn (self $status): string => $status->value,
            array: self::cases(),
        );
    }
}
