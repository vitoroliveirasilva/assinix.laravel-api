<?php

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionHistoryRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResumeSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionHistoryRecorder $historyRecorder,
    ) {
    }

    public function execute(Subscription $subscription, User $user): Subscription
    {
        if (!$subscription->isPaused()) {
            throw ValidationException::withMessages([
                'status' => ['Apenas assinaturas pausadas podem ser retomadas.'],
            ]);
        }

        return DB::transaction(function () use ($subscription, $user): Subscription {
            $oldStatus = $subscription->status;

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
            ])->save();

            $subscription->refresh();

            $this->historyRecorder->record(
                subscription: $subscription,
                user: $user,
                event: SubscriptionHistoryEvent::Resumed,
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => $subscription->status->value],
                description: 'Assinatura retomada.',
            );

            return $subscription->load(['category', 'paymentMethod']);
        });
    }
}