<?php

namespace App\Actions\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\SubscriptionHistoryEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Currency\CurrencyConversionService;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;

class UpdateSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
        private readonly CurrencyConversionService $currencyConversionService,
    ) {
    }

    public function execute(Subscription $subscription, User $user, array $data): Subscription
    {
        return DB::transaction(function () use ($subscription, $user, $data): Subscription {
            $fields = array_keys($data);

            $oldValues = collect($subscription->getRawOriginal())
                ->only($fields)
                ->all();

            $currency = array_key_exists('currency', $data)
                ? CurrencyCode::from($data['currency'])
                : $subscription->currency;

            $amount = array_key_exists('amount', $data)
                ? (float) $data['amount']
                : (float) $subscription->amount;

            if (array_key_exists('currency', $data) || array_key_exists('amount', $data)) {
                $conversion = $this->currencyConversionService->convert(
                    amount: $amount,
                    fromCurrency: $currency,
                    toCurrency: CurrencyCode::BRL,
                );

                $data['amount_brl'] = $conversion['converted_amount'];
                $data['exchange_rate'] = $conversion['rate'];
                $data['exchange_rate_date'] = $conversion['quoted_at']?->toDateString() ?? now()->toDateString();
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