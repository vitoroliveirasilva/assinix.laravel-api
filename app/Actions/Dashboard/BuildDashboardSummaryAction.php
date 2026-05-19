<?php

namespace App\Actions\Dashboard;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionFinancialCalculator;

class BuildDashboardSummaryAction
{
    public function __construct(
        private readonly SubscriptionFinancialCalculator $calculator,
    ) {}

    public function execute(User $user): array
    {
        $subscriptions = Subscription::query()
            ->with(['category', 'paymentMethod'])
            ->forUser($user)
            ->get();

        $activeSubscriptions = $subscriptions
            ->where('status', SubscriptionStatus::Active);

        $monthlyTotal = $activeSubscriptions
            ->sum(fn (Subscription $subscription): float => $this->calculator->monthlyEstimated($subscription));

        $yearlyTotal = $activeSubscriptions
            ->sum(fn (Subscription $subscription): float => $this->calculator->yearlyEstimated($subscription));

        $today = now()->toDateString();
        $nextThirtyDays = now()->addDays(30)->toDateString();

        return [
            'base_currency' => 'BRL',
            'totals' => [
                'subscriptions' => $subscriptions->count(),
                'active' => $subscriptions->where('status', SubscriptionStatus::Active)->count(),
                'paused' => $subscriptions->where('status', SubscriptionStatus::Paused)->count(),
                'canceled' => $subscriptions->where('status', SubscriptionStatus::Canceled)->count(),
                'expired' => $subscriptions->where('status', SubscriptionStatus::Expired)->count(),
            ],
            'financial' => [
                'monthly_estimated_brl' => round($monthlyTotal, 2),
                'yearly_estimated_brl' => round($yearlyTotal, 2),
                'average_monthly_per_active_subscription_brl' => $activeSubscriptions->count() > 0
                    ? round($monthlyTotal / $activeSubscriptions->count(), 2)
                    : 0.0,
            ],
            'due' => [
                'upcoming_30_days' => $activeSubscriptions
                    ->filter(fn (Subscription $subscription): bool => $subscription->next_billing_at->between($today, $nextThirtyDays))
                    ->count(),
                'overdue' => $activeSubscriptions
                    ->filter(fn (Subscription $subscription): bool => $subscription->next_billing_at->lt($today))
                    ->count(),
            ],
            'currency' => [
                'pending_conversion_count' => $subscriptions
                    ->filter(fn (Subscription $subscription): bool => $this->calculator->hasPendingCurrencyConversion($subscription))
                    ->count(),
            ],
        ];
    }
}
