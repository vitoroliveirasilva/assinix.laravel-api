<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('auth.login', function (Request $request): array {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute((int) env('AUTH_LOGIN_MAX_ATTEMPTS', 5))
                    ->by('login-email:' . $email . '|ip:' . $request->ip()),

                Limit::perMinute((int) env('AUTH_LOGIN_IP_MAX_ATTEMPTS', 20))
                    ->by('login-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('auth.register', function (Request $request): Limit {
            return Limit::perMinute((int) env('AUTH_REGISTER_MAX_ATTEMPTS', 5))
                ->by('register-ip:' . $request->ip());
        });

        RateLimiter::for('api.authenticated', function (Request $request): Limit {
            return Limit::perMinute((int) env('AUTH_API_MAX_ATTEMPTS', 120))
                ->by('api-user:' . ($request->user()?->id ?: $request->ip()));
        });
    }
}