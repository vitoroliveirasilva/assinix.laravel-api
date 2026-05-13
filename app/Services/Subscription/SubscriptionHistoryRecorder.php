<?php

namespace App\Services\Subscription;

use App\Enums\SubscriptionHistoryEvent;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Models\User;
use BackedEnum;

class SubscriptionHistoryRecorder
{
    public function record(
        Subscription $subscription,
        User $user,
        SubscriptionHistoryEvent $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
    ): SubscriptionHistory {
        return SubscriptionHistory::query()->create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'event' => $event,
            'old_values' => $this->normalizeValues($oldValues),
            'new_values' => $this->normalizeValues($newValues),
            'description' => $description,
        ]);
    }

    private function normalizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return collect($values)
            ->map(function (mixed $value): mixed {
                if ($value instanceof BackedEnum) {
                    return $value->value;
                }

                return $value;
            })
            ->all();
    }
}