<?php

namespace App\Services\Subscription;

use App\Enums\RecurrenceType;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class SubscriptionBillingDateService
{
    public function calculateNextBillingDate(
        Subscription $subscription,
        ?CarbonInterface $from = null,
    ): CarbonImmutable {
        $baseDate = CarbonImmutable::parse(
            $from?->toDateString() ?? $subscription->next_billing_at->toDateString()
        );

        return match ($subscription->recurrence) {
            RecurrenceType::Weekly => $baseDate->addWeeks($subscription->interval),
            RecurrenceType::Monthly => $baseDate->addMonthsNoOverflow($subscription->interval),
            RecurrenceType::Yearly => $baseDate->addYearsNoOverflow($subscription->interval),
            RecurrenceType::Custom => $baseDate->addDays($subscription->interval_in_days),
        };
    }
}