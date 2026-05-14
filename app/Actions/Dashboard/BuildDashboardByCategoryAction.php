<?php

namespace App\Actions\Dashboard;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionFinancialCalculator;

class BuildDashboardByCategoryAction
{
    public function __construct(
        private readonly SubscriptionFinancialCalculator $calculator,
    ) {
    }

    public function execute(User $user): array
    {
        return Subscription::query()
            ->with('category')
            ->forUser($user)
            ->where('status', SubscriptionStatus::Active)
            ->get()
            ->groupBy(fn(Subscription $subscription): string => $subscription->category?->name ?? 'Sem categoria')
            ->map(function ($subscriptions, string $categoryName): array {
                $monthlyTotal = $subscriptions->sum(
                    fn(Subscription $subscription): float => $this->calculator->monthlyEstimated($subscription)
                );

                $yearlyTotal = $subscriptions->sum(
                    fn(Subscription $subscription): float => $this->calculator->yearlyEstimated($subscription)
                );

                return [
                    'category' => $categoryName,
                    'subscriptions_count' => $subscriptions->count(),
                    'monthly_estimated_brl' => round($monthlyTotal, 2),
                    'yearly_estimated_brl' => round($yearlyTotal, 2),
                ];
            })
            ->sortByDesc('monthly_estimated_brl')
            ->values()
            ->all();
    }
}