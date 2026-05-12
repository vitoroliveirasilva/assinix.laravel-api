<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\UpdatePasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            data: [
                'user' => UserResource::make($request->user())->resolve(),
            ],
            message: 'Usuário autenticado retornado com sucesso.',
        );
    }

    public function update(
        UpdateProfileRequest $request,
        UpdateProfileAction $action,
    ): JsonResponse {
        $user = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($user)->resolve(),
            ],
            message: 'Perfil atualizado com sucesso.',
        );
    }

    public function updatePassword(
        UpdatePasswordRequest $request,
        UpdatePasswordAction $action,
    ): JsonResponse {
        $user = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($user)->resolve(),
            ],
            message: 'Senha alterada com sucesso.',
        );
    }
}