<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function execute(array $data): array
    {
        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (
            !$user
            || !Hash::check($data['password'], $user->password)
            || !$user->isActive()
        ) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas são inválidas.'],
            ]);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill([
                'password' => $data['password'],
            ])->save();
        }

        $token = $user->createToken(
            name: $data['device_name'] ?? 'Assinix API Token',
            abilities: ['*'],
        );

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }
}