<?php

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {}

    public function execute(Subscription $subscription, User $user): Subscription
    {
        if ($subscription->isCanceled()) {
            throw ValidationException::withMessages([
                'status' => ['Esta assinatura já está cancelada.'],
            ]);
        }

        return DB::transaction(function () use ($subscription, $user): Subscription {
            $oldStatus = $subscription->status;

            $subscription->forceFill([
                'status' => SubscriptionStatus::Canceled,
                'ends_at' => $subscription->ends_at ?? now()->toDateString(),
            ])->save();

            $subscription->refresh();

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Canceled,
                oldValues: ['status' => $oldStatus->value],
                newValues: [
                    'status' => $subscription->status->value,
                    'ends_at' => $subscription->ends_at?->toDateString(),
                ],
                description: 'Assinatura cancelada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}
