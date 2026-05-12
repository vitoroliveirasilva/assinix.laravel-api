<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $token = $user->createToken(
                name: $data['device_name'] ?? 'Assinix API Token',
                abilities: ['*'],
            );

            return [
                'user' => $user,
                'token' => $token->plainTextToken,
            ];
        });
    }
}