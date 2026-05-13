<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function pause(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function resume(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function renew(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function viewHistory(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return false;
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return false;
    }

    private function owns(User $user, Subscription $subscription): bool
    {
        return $user->isActive() && $subscription->user_id === $user->id;
    }
}