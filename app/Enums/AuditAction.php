<?php

namespace App\Enums;

enum AuditAction: string
{
    case UserRegistered = 'user_registered';
    case UserLoggedIn = 'user_logged_in';
    case UserLoggedOut = 'user_logged_out';
    case UserLoggedOutAll = 'user_logged_out_all';
    case ProfileUpdated = 'profile_updated';
    case PasswordChanged = 'password_changed';

    case CategoryCreated = 'category_created';
    case CategoryUpdated = 'category_updated';
    case CategoryDeleted = 'category_deleted';

    case PaymentMethodCreated = 'payment_method_created';
    case PaymentMethodUpdated = 'payment_method_updated';
    case PaymentMethodDeleted = 'payment_method_deleted';

    case SubscriptionCreated = 'subscription_created';
    case SubscriptionUpdated = 'subscription_updated';
    case SubscriptionDeleted = 'subscription_deleted';
    case SubscriptionPaused = 'subscription_paused';
    case SubscriptionResumed = 'subscription_resumed';
    case SubscriptionCanceled = 'subscription_canceled';
    case SubscriptionRenewed = 'subscription_renewed';

    case CurrencyRateRefreshed = 'currency_rate_refreshed';

    case ApiMutationPerformed = 'api_mutation_performed';

    public function label(): string
    {
        return match ($this) {
            self::UserRegistered => 'Usuário cadastrado',
            self::UserLoggedIn => 'Login realizado',
            self::UserLoggedOut => 'Logout realizado',
            self::UserLoggedOutAll => 'Logout realizado em todos os dispositivos',
            self::ProfileUpdated => 'Perfil atualizado',
            self::PasswordChanged => 'Senha alterada',

            self::CategoryCreated => 'Categoria criada',
            self::CategoryUpdated => 'Categoria atualizada',
            self::CategoryDeleted => 'Categoria removida',

            self::PaymentMethodCreated => 'Forma de pagamento criada',
            self::PaymentMethodUpdated => 'Forma de pagamento atualizada',
            self::PaymentMethodDeleted => 'Forma de pagamento removida',

            self::SubscriptionCreated => 'Assinatura criada',
            self::SubscriptionUpdated => 'Assinatura atualizada',
            self::SubscriptionDeleted => 'Assinatura removida',
            self::SubscriptionPaused => 'Assinatura pausada',
            self::SubscriptionResumed => 'Assinatura retomada',
            self::SubscriptionCanceled => 'Assinatura cancelada',
            self::SubscriptionRenewed => 'Assinatura renovada',

            self::CurrencyRateRefreshed => 'Cotação atualizada',

            self::ApiMutationPerformed => 'Mutação de API executada',
        };
    }

    public static function fromRouteName(?string $routeName): self
    {
        return match ($routeName) {
            'me.update' => self::ProfileUpdated,
            'me.password.update' => self::PasswordChanged,

            'categories.store' => self::CategoryCreated,
            'categories.update' => self::CategoryUpdated,
            'categories.destroy' => self::CategoryDeleted,

            'payment-methods.store' => self::PaymentMethodCreated,
            'payment-methods.update' => self::PaymentMethodUpdated,
            'payment-methods.destroy' => self::PaymentMethodDeleted,

            'subscriptions.store' => self::SubscriptionCreated,
            'subscriptions.update' => self::SubscriptionUpdated,
            'subscriptions.destroy' => self::SubscriptionDeleted,
            'subscriptions.pause' => self::SubscriptionPaused,
            'subscriptions.resume' => self::SubscriptionResumed,
            'subscriptions.cancel' => self::SubscriptionCanceled,
            'subscriptions.renew' => self::SubscriptionRenewed,

            'currencies.refresh' => self::CurrencyRateRefreshed,

            default => self::ApiMutationPerformed,
        };
    }

    public static function values(): array
    {
        return array_map(
            callback: static fn (self $action): string => $action->value,
            array: self::cases(),
        );
    }
}
