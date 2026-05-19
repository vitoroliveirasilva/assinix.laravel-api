<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    public function execute(User $user, array $data): User
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $currentTokenId = $user->currentAccessToken()?->id;

        if ($currentTokenId !== null) {
            $user->tokens()
                ->where('id', '!=', $currentTokenId)
                ->delete();
        }

        return $user->refresh();
    }
}
