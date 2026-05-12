<?php

namespace App\Http\Middleware;

use App\Support\RequestContext\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = RequestContext::id($request);

        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}