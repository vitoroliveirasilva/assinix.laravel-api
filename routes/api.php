<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SubscriptionDueController;
use App\Http\Controllers\Api\V1\SubscriptionHistoryController;
use App\Http\Controllers\Api\V1\CurrencyController;
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

        Route::apiResource('categories', CategoryController::class);

        Route::apiResource('payment-methods', PaymentMethodController::class)
            ->parameters([
                'payment-methods' => 'payment_method',
            ]);

        Route::prefix('dashboard')
            ->name('dashboard.')
            ->group(function (): void {
                Route::get('/summary', [DashboardController::class, 'summary'])
                    ->name('summary');

                Route::get('/monthly', [DashboardController::class, 'monthly'])
                    ->name('monthly');

                Route::get('/yearly', [DashboardController::class, 'yearly'])
                    ->name('yearly');

                Route::get('/by-category', [DashboardController::class, 'byCategory'])
                    ->name('by-category');

                Route::get('/by-payment-method', [DashboardController::class, 'byPaymentMethod'])
                    ->name('by-payment-method');
            });

        Route::prefix('currencies')
            ->name('currencies.')
            ->group(function (): void {
                Route::get('/rates', [CurrencyController::class, 'rates'])
                    ->name('rates');

                Route::post('/refresh', [CurrencyController::class, 'refresh'])
                    ->name('refresh');

                Route::get('/convert', [CurrencyController::class, 'convert'])
                    ->name('convert');
            });

        Route::get('/subscriptions/upcoming', [SubscriptionDueController::class, 'upcoming'])
            ->name('subscriptions.upcoming');

        Route::get('/subscriptions/overdue', [SubscriptionDueController::class, 'overdue'])
            ->name('subscriptions.overdue');

        Route::get('/subscriptions/{subscription}/history', [SubscriptionHistoryController::class, 'index'])
            ->name('subscriptions.history');

        Route::patch('/subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])
            ->name('subscriptions.pause');

        Route::patch('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])
            ->name('subscriptions.resume');

        Route::patch('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])
            ->name('subscriptions.cancel');

        Route::patch('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])
            ->name('subscriptions.renew');

        Route::apiResource('subscriptions', SubscriptionController::class);
    });