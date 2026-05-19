<?php

namespace App\Actions\Auth;

use App\Models\User;

class UpdateProfileAction
{
    public function execute(User $user, array $data): User
    {
        $originalEmail = $user->email;

        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ]);

        if ($user->email !== $originalEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->refresh();
    }
}
