<?php

namespace App\Enums;

enum SubscriptionHistoryEvent: string
{
    case Created = 'subscription_created';
    case Updated = 'subscription_updated';
    case Deleted = 'subscription_deleted';
    case Paused = 'subscription_paused';
    case Resumed = 'subscription_resumed';
    case Canceled = 'subscription_canceled';
    case Renewed = 'subscription_renewed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Assinatura criada',
            self::Updated => 'Assinatura atualizada',
            self::Deleted => 'Assinatura removida',
            self::Paused => 'Assinatura pausada',
            self::Resumed => 'Assinatura retomada',
            self::Canceled => 'Assinatura cancelada',
            self::Renewed => 'Assinatura renovada',
        };
    }
}
