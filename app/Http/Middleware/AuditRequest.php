<?php

namespace App\Http\Middleware;

use App\Enums\AuditAction;
use App\Services\Audit\AuditLogger;
use App\Support\RequestContext\RequestContext;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditRequest
{
    private const SKIPPED_ROUTE_NAMES = [
        'auth.register',
        'auth.login',
        'auth.logout',
        'auth.logout-all',
    ];

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldAudit($request, $response)) {
            return $response;
        }

        $this->auditLogger->record(
            action: AuditAction::fromRouteName($request->route()?->getName()),
            user: $request->user(),
            auditable: $this->resolveAuditable($request),
            metadata: [
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'input' => $request->all(),
                'query' => $request->query(),
                'request_id' => RequestContext::id($request),
            ],
            request: $request,
        );

        return $response;
    }

    private function shouldAudit(Request $request, Response $response): bool
    {
        if (!$request->is('api/*')) {
            return false;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (in_array($routeName, self::SKIPPED_ROUTE_NAMES, true)) {
            return false;
        }

        return $request->user() !== null;
    }

    private function resolveAuditable(Request $request): ?Model
    {
        foreach (['subscription', 'category', 'payment_method'] as $parameter) {
            $value = $request->route($parameter);

            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }
}