<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Assinix API is running.',
        'data' => [
            'name' => config('app.name'),
            'version' => config('app.version', 'v1'),
        ],
        'errors' => null,
        'meta' => [
            'request_id' => request()->attributes->get('request_id'),
        ],
    ]);
})->name('api.root');

Route::prefix('auth')
    ->name('auth.')
    ->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:auth.register')
            ->name('register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth.login')
            ->name('login');

        Route::middleware(['auth:sanctum', 'throttle:api.authenticated'])
            ->group(function (): void {
                Route::post('/logout', [AuthController::class, 'logout'])
                    ->name('logout');

                Route::post('/logout-all', [AuthController::class, 'logoutAll'])
                    ->name('logout-all');
            });
    });

Route::middleware(['auth:sanctum', 'active', 'throttle:api.authenticated'])
    ->group(function (): void {
        Route::get('/me', [MeController::class, 'show'])
            ->name('me.show');

        Route::patch('/me', [MeController::class, 'update'])
            ->name('me.update');

        Route::patch('/me/password', [MeController::class, 'updatePassword'])
            ->name('me.password.update');
    });