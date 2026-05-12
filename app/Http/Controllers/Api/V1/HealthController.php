<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success(
            data: [
                'status' => 'ok',
                'service' => config('app.name'),
                'environment' => config('app.env'),
                'timestamp' => now()->toISOString(),
            ],
            message: 'Health check completed successfully.',
        );
    }
}