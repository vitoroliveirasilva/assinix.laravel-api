<?php

namespace App\Actions\Dashboard;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionFinancialCalculator;
use Carbon\CarbonImmutable;

class BuildDashboardMonthlyAction
{
    public function __construct(
        private readonly SubscriptionFinancialCalculator $calculator,
    ) {}

    public function execute(User $user, int $months = 12): array
    {
        $subscriptions = Subscription::query()
            ->forUser($user)
            ->where('status', SubscriptionStatus::Active)
            ->get();

        $start = CarbonImmutable::now()->startOfMonth();

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start, $subscriptions): array {
                $month = $start->addMonthsNoOverflow($offset);

                $monthlyTotal = $subscriptions->sum(
                    fn (Subscription $subscription): float => $this->calculator->monthlyEstimated($subscription)
                );

                $dueInMonth = $subscriptions
                    ->filter(fn (Subscription $subscription): bool => $subscription->next_billing_at->isSameMonth($month))
                    ->values();

                $dueTotal = $dueInMonth->sum(
                    fn (Subscription $subscription): float => $this->calculator->amountInBrl($subscription)
                );

                return [
                    'month' => $month->format('Y-m'),
                    'label' => $month->translatedFormat('F/Y'),
                    'monthly_estimated_brl' => round($monthlyTotal, 2),
                    'due_total_brl' => round($dueTotal, 2),
                    'due_subscriptions_count' => $dueInMonth->count(),
                ];
            })
            ->all();
    }
}
