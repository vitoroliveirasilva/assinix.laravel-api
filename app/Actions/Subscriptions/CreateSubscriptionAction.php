<?php

namespace App\Actions\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;

class CreateSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {
    }

    public function execute(User $user, array $data): Subscription
    {
        return DB::transaction(function () use ($user, $data): Subscription {
            $currency = CurrencyCode::from($data['currency'] ?? CurrencyCode::BRL->value);

            $subscription = $user->subscriptions()->create([
                'category_id' => $data['category_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'currency' => $currency,
                'amount_brl' => $currency->isBaseCurrency() ? $data['amount'] : null,
                'exchange_rate' => $currency->isBaseCurrency() ? 1 : null,
                'exchange_rate_date' => $currency->isBaseCurrency() ? now()->toDateString() : null,
                'status' => $data['status'] ?? SubscriptionStatus::Active,
                'recurrence' => $data['recurrence'],
                'interval' => $data['interval'] ?? 1,
                'interval_in_days' => $data['interval_in_days'] ?? null,
                'starts_at' => $data['starts_at'],
                'next_billing_at' => $data['next_billing_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'last_charged_at' => $data['last_charged_at'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Created,
                newValues: $subscription->getAttributes(),
                description: 'Assinatura criada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}