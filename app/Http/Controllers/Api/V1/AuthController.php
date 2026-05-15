<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Audit\AuditLogger;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        RegisterUserAction $action,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        $auditLogger->record(
            action: AuditAction::UserRegistered,
            user: $result['user'],
            metadata: [
                'email' => $result['user']->email,
                'device_name' => $request->input('device_name'),
            ],
            request: $request,
        );

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
        AuditLogger $auditLogger,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        $auditLogger->record(
            action: AuditAction::UserLoggedIn,
            user: $result['user'],
            metadata: [
                'email' => $result['user']->email,
                'device_name' => $request->input('device_name'),
            ],
            request: $request,
        );

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

    public function logout(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $user = $request->user();

        $auditLogger->record(
            action: AuditAction::UserLoggedOut,
            user: $user,
            metadata: [
                'token_id' => $user?->currentAccessToken()?->id,
            ],
            request: $request,
        );

        $user?->currentAccessToken()?->delete();

        return ApiResponse::success(
            message: 'Logout realizado com sucesso.',
        );
    }

    public function logoutAll(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $user = $request->user();

        $auditLogger->record(
            action: AuditAction::UserLoggedOutAll,
            user: $user,
            metadata: [
                'tokens_count' => $user?->tokens()->count(),
            ],
            request: $request,
        );

        $user?->tokens()->delete();

        return ApiResponse::success(
            message: 'Logout realizado em todos os dispositivos.',
        );
    }
}