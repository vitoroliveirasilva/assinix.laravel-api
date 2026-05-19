<?php

use App\Http\Middleware\AuditRequest;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RequestId;
use App\Http\Middleware\SecurityHeaders;
use App\Support\ApiResponse\ApiResponse;
use App\Support\RequestContext\RequestContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
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
        $middleware->append(AuditRequest::class);

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

        $exceptions->report(function (Throwable $exception): void {
            Log::error('exception.reported', [
                'request_id' => RequestContext::id(),
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Os dados informados são inválidos.',
                errors: $exception->errors(),
                status: 422,
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Autenticação necessária.',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Você não tem permissão para executar esta ação.',
                status: 403,
            );
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Você não tem permissão para executar esta ação.',
                status: 403,
            );
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $message = match ($request->route()?->getName()) {
                'auth.login' => 'Muitas tentativas de login, tente novamente em instantes.',
                'auth.register' => 'Muitas tentativas de cadastro, tente novamente em instantes.',
                default => 'Muitas requisições, tente novamente em instantes.',
            };

            return ApiResponse::error(
                message: $message,
                status: 429,
            )->withHeaders($exception->getHeaders());
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Recurso não encontrado.',
                status: 404,
            );
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Método HTTP não permitido para este recurso.',
                status: 405,
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            if ($exception instanceof HttpExceptionInterface) {
                return null;
            }

            return ApiResponse::error(
                message: config('app.debug')
                ? $exception->getMessage()
                : 'Erro interno no servidor.',
                status: 500,
            );
        });

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) use ($shouldReturnJson) {
            if (! $shouldReturnJson($request)) {
                return $response;
            }

            if ($response->getStatusCode() === 403) {
                return ApiResponse::error(
                    message: 'Você não tem permissão para executar esta ação.',
                    status: 403,
                );
            }

            return $response;
        });
    })
    ->create();
