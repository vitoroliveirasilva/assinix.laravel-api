<?php

namespace App\Actions\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\SubscriptionHistoryEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;

class UpdateSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {
    }

    public function execute(Subscription $subscription, User $user, array $data): Subscription
    {
        return DB::transaction(function () use ($subscription, $user, $data): Subscription {
            $fields = array_keys($data);

            $oldValues = collect($subscription->getRawOriginal())
                ->only($fields)
                ->all();

            if (array_key_exists('currency', $data)) {
                $currency = CurrencyCode::from($data['currency']);

                $data['amount_brl'] = $currency->isBaseCurrency()
                    ? ($data['amount'] ?? $subscription->amount)
                    : null;

                $data['exchange_rate'] = $currency->isBaseCurrency()
                    ? 1
                    : null;

                $data['exchange_rate_date'] = $currency->isBaseCurrency()
                    ? now()->toDateString()
                    : null;
            } elseif (array_key_exists('amount', $data) && $subscription->currency->isBaseCurrency()) {
                $data['amount_brl'] = $data['amount'];
                $data['exchange_rate'] = 1;
                $data['exchange_rate_date'] = now()->toDateString();
            }

            $subscription->fill($data);
            $subscription->save();

            $subscription->refresh();

            $newValues = collect($subscription->getAttributes())
                ->only(array_unique(array_merge($fields, [
                    'amount_brl',
                    'exchange_rate',
                    'exchange_rate_date',
                ])))
                ->all();

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Updated,
                oldValues: $oldValues,
                newValues: $newValues,
                description: 'Assinatura atualizada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}