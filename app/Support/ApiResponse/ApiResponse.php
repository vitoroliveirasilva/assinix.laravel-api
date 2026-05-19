<?php

namespace App\Support\ApiResponse;

use App\Support\RequestContext\RequestContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Request completed successfully.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => self::meta($meta),
        ], $status);
    }

    public static function paginated(
        LengthAwarePaginator $paginator,
        array $data,
        string $message = 'Resources returned successfully.',
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => self::meta($meta),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public static function error(
        string $message = 'Request failed.',
        mixed $errors = null,
        int $status = 400,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => self::meta($meta),
        ], $status);
    }

    private static function meta(array $meta = []): array
    {
        return array_merge([
            'request_id' => RequestContext::id(),
        ], $meta);
    }
}
