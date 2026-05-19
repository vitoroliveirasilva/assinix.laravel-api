<?php

namespace App\Actions\Dashboard;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionFinancialCalculator;
use Carbon\CarbonImmutable;

class BuildDashboardYearlyAction
{
    public function __construct(
        private readonly SubscriptionFinancialCalculator $calculator,
    ) {}

    public function execute(User $user, int $years = 3): array
    {
        $subscriptions = Subscription::query()
            ->forUser($user)
            ->where('status', SubscriptionStatus::Active)
            ->get();

        $startYear = CarbonImmutable::now()->year;

        return collect(range(0, $years - 1))
            ->map(function (int $offset) use ($startYear, $subscriptions): array {
                $year = $startYear + $offset;

                $yearlyTotal = $subscriptions->sum(
                    fn (Subscription $subscription): float => $this->calculator->yearlyEstimated($subscription)
                );

                $dueInYear = $subscriptions
                    ->filter(fn (Subscription $subscription): bool => (int) $subscription->next_billing_at->format('Y') === $year)
                    ->values();

                $dueTotal = $dueInYear->sum(
                    fn (Subscription $subscription): float => $this->calculator->amountInBrl($subscription)
                );

                return [
                    'year' => $year,
                    'yearly_estimated_brl' => round($yearlyTotal, 2),
                    'due_total_brl' => round($dueTotal, 2),
                    'due_subscriptions_count' => $dueInYear->count(),
                ];
            })
            ->all();
    }
}
