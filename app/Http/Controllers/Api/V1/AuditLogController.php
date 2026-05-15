<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogs\AuditLogIndexRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $auditLogs = AuditLog::query()
            ->where('user_id', $request->user()->id)
            ->when(
                isset($validated['action']),
                fn($query) => $query->where('action', $validated['action']),
            )
            ->when(
                isset($validated['from']),
                fn($query) => $query->whereDate('created_at', '>=', $validated['from']),
            )
            ->when(
                isset($validated['to']),
                fn($query) => $query->whereDate('created_at', '<=', $validated['to']),
            )
            ->latest('created_at')
            ->paginate($request->perPage())
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $auditLogs,
            data: AuditLogResource::collection($auditLogs->getCollection())->resolve($request),
            message: 'Logs de auditoria retornados com sucesso.',
        );
    }
}