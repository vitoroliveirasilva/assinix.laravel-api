<?php

namespace App\Services\Audit;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Logging\SensitiveDataSanitizer;
use App\Support\RequestContext\RequestContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogger
{
    public function __construct(
        private readonly SensitiveDataSanitizer $sanitizer,
    ) {}

    public function record(
        AuditAction $action,
        ?User $user = null,
        ?Model $auditable = null,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        $request ??= request();

        try {
            $auditLog = AuditLog::query()->create([
                'user_id' => $user?->id,
                'action' => $action,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => RequestContext::id($request),
                'metadata' => $this->sanitizer->sanitize($metadata),
            ]);

            Log::info('audit.recorded', [
                'audit_log_id' => $auditLog->id,
                'action' => $action->value,
                'user_id' => $user?->id,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'request_id' => RequestContext::id($request),
            ]);

            return $auditLog;
        } catch (Throwable $exception) {
            Log::warning('audit.record_failed', [
                'action' => $action->value,
                'user_id' => $user?->id,
                'request_id' => RequestContext::id($request),
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
