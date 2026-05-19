<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->owns($user, $paymentMethod);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->owns($user, $paymentMethod);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->owns($user, $paymentMethod);
    }

    public function restore(User $user, PaymentMethod $paymentMethod): bool
    {
        return false;
    }

    public function forceDelete(User $user, PaymentMethod $paymentMethod): bool
    {
        return false;
    }

    private function owns(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->isActive() && $paymentMethod->user_id === $user->id;
    }
}
