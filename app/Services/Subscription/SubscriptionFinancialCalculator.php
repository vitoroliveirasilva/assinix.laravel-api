<?php

namespace App\Services\Subscription;

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Models\Subscription;

class SubscriptionFinancialCalculator
{
    public function monthlyEstimated(Subscription $subscription): float
    {
        $amount = $this->amountInBrl($subscription);

        if ($amount <= 0.0) {
            return 0.0;
        }

        $interval = max((int) $subscription->interval, 1);

        $value = match ($subscription->recurrence) {
            RecurrenceType::Weekly => $amount * 52 / 12 / $interval,
            RecurrenceType::Monthly => $amount / $interval,
            RecurrenceType::Yearly => $amount / 12 / $interval,
            RecurrenceType::Custom => $this->monthlyCustom($amount, $subscription),
        };

        return round($value, 2);
    }

    public function yearlyEstimated(Subscription $subscription): float
    {
        $amount = $this->amountInBrl($subscription);

        if ($amount <= 0.0) {
            return 0.0;
        }

        $interval = max((int) $subscription->interval, 1);

        $value = match ($subscription->recurrence) {
            RecurrenceType::Weekly => $amount * 52 / $interval,
            RecurrenceType::Monthly => $amount * 12 / $interval,
            RecurrenceType::Yearly => $amount / $interval,
            RecurrenceType::Custom => $this->yearlyCustom($amount, $subscription),
        };

        return round($value, 2);
    }

    public function amountInBrl(Subscription $subscription): float
    {
        if ($subscription->amount_brl !== null) {
            return (float) $subscription->amount_brl;
        }

        if ($subscription->currency === CurrencyCode::BRL) {
            return (float) $subscription->amount;
        }

        return 0.0;
    }

    public function hasPendingCurrencyConversion(Subscription $subscription): bool
    {
        return $subscription->currency !== CurrencyCode::BRL
            && $subscription->amount_brl === null;
    }

    private function monthlyCustom(float $amount, Subscription $subscription): float
    {
        $days = max((int) $subscription->interval_in_days, 1);

        return $amount * 30 / $days;
    }

    private function yearlyCustom(float $amount, Subscription $subscription): float
    {
        $days = max((int) $subscription->interval_in_days, 1);

        return $amount * 365 / $days;
    }
}
