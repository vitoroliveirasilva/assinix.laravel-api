<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        RegisterUserAction $action,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve(),
                'token' => [
                    'token_type' => 'Bearer',
                    'access_token' => $result['token'],
                ],
            ],
            message: 'Usuário cadastrado com sucesso.',
            status: 201,
        );
    }

    public function login(
        LoginRequest $request,
        LoginUserAction $action,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve(),
                'token' => [
                    'token_type' => 'Bearer',
                    'access_token' => $result['token'],
                ],
            ],
            message: 'Login realizado com sucesso.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(
            message: 'Logout realizado com sucesso.',
        );
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return ApiResponse::success(
            message: 'Logout realizado em todos os dispositivos.',
        );
    }
}