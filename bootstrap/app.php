<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RequestId;
use App\Http\Middleware\SecurityHeaders;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('api')
                ->group(base_path('routes/health.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ForceJsonResponse::class);
        $middleware->append(RequestId::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldReturnJson = static function (Request $request): bool {
            return $request->expectsJson()
                || $request->is('api/*')
                || $request->is('health')
                || $request->is('api/v1/health');
        };

        $exceptions->render(function (ValidationException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Os dados informados são inválidos.',
                errors: $exception->errors(),
                status: 422,
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Autenticação necessária.',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Você não tem permissão para executar esta ação.',
                status: 403,
            );
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Muitas requisições, tente novamente em instantes.',
                status: 429,
            )->withHeaders($exception->getHeaders());
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Recurso não encontrado.',
                status: 404,
            );
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Método HTTP não permitido para este recurso.',
                status: 405,
            );
        });
    })
    ->create();