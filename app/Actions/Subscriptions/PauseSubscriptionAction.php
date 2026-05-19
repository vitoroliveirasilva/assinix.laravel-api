<?php

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PauseSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {}

    public function execute(Subscription $subscription, User $user): Subscription
    {
        if (! $subscription->isActive()) {
            throw ValidationException::withMessages([
                'status' => ['Apenas assinaturas ativas podem ser pausadas.'],
            ]);
        }

        return DB::transaction(function () use ($subscription, $user): Subscription {
            $oldStatus = $subscription->status;

            $subscription->forceFill([
                'status' => SubscriptionStatus::Paused,
            ])->save();

            $subscription->refresh();

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Paused,
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => $subscription->status->value],
                description: 'Assinatura pausada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}
