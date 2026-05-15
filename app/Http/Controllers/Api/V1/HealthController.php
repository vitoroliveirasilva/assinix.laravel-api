<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $isHealthy = collect($checks)
            ->every(fn(array $check): bool => $check['status'] === 'ok');

        return ApiResponse::success(
            data: [
                'status' => $isHealthy ? 'ok' : 'degraded',
                'service' => config('app.name'),
                'environment' => config('app.env'),
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
            ],
            message: $isHealthy
            ? 'Health check completed successfully.'
            : 'Health check completed with degraded services.',
            status: $isHealthy ? 200 : 503,
        );
    }

    private function checkApp(): array
    {
        return [
            'status' => 'ok',
            'message' => 'Application is running.',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');

            return [
                'status' => 'ok',
                'message' => 'Database connection is available.',
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed.',
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check:' . now()->timestamp;

            Cache::put($key, 'ok', 10);

            $value = Cache::get($key);

            Cache::forget($key);

            if ($value !== 'ok') {
                return [
                    'status' => 'error',
                    'message' => 'Cache read/write check failed.',
                ];
            }

            return [
                'status' => 'ok',
                'message' => 'Cache connection is available.',
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'error',
                'message' => 'Cache connection failed.',
            ];
        }
    }
}