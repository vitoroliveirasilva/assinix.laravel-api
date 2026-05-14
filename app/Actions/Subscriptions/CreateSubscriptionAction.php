<?php

namespace App\Actions\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Currency\CurrencyConversionService;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;

class CreateSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
        private readonly CurrencyConversionService $currencyConversionService,
    ) {
    }

    public function execute(User $user, array $data): Subscription
    {
        return DB::transaction(function () use ($user, $data): Subscription {
            $currency = CurrencyCode::from($data['currency'] ?? CurrencyCode::BRL->value);

            $conversion = $this->currencyConversionService->convert(
                amount: (float) $data['amount'],
                fromCurrency: $currency,
                toCurrency: CurrencyCode::BRL,
            );

            $subscription = $user->subscriptions()->create([
                'category_id' => $data['category_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'currency' => $currency,
                'amount_brl' => $conversion['converted_amount'],
                'exchange_rate' => $conversion['rate'],
                'exchange_rate_date' => $conversion['quoted_at']?->toDateString() ?? now()->toDateString(),
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