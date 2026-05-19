<?php

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionBillingDateService;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RenewSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionBillingDateService $billingDateService,
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {}

    public function execute(Subscription $subscription, User $user, array $data = []): Subscription
    {
        if ($subscription->isCanceled()) {
            throw ValidationException::withMessages([
                'status' => ['Assinaturas canceladas não podem ser renovadas.'],
            ]);
        }

        return DB::transaction(function () use ($subscription, $user, $data): Subscription {
            $oldValues = [
                'status' => $subscription->status->value,
                'last_charged_at' => $subscription->last_charged_at?->toDateString(),
                'next_billing_at' => $subscription->next_billing_at?->toDateString(),
            ];

            $lastChargedAt = $data['last_charged_at']
                ?? $subscription->next_billing_at->toDateString();

            $nextBillingAt = $data['next_billing_at']
                ?? $this->billingDateService
                    ->calculateNextBillingDate($subscription)
                    ->toDateString();

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
                'last_charged_at' => $lastChargedAt,
                'next_billing_at' => $nextBillingAt,
            ])->save();

            $subscription->refresh();

            $newValues = [
                'status' => $subscription->status->value,
                'last_charged_at' => $subscription->last_charged_at?->toDateString(),
                'next_billing_at' => $subscription->next_billing_at?->toDateString(),
            ];

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Renewed,
                oldValues: $oldValues,
                newValues: $newValues,
                description: 'Assinatura renovada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}
