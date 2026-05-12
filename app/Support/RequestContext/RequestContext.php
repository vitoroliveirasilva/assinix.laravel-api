<?php

namespace App\Support\RequestContext;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;

class RequestContext
{
    public static function id(?Request $request = null): string
    {
        $request ??= RequestFacade::instance();

        $existingRequestId = $request->attributes->get('request_id');

        if (is_string($existingRequestId) && $existingRequestId !== '') {
            return $existingRequestId;
        }

        $requestId = $request->headers->get('X-Request-Id');

        if (!is_string($requestId) || $requestId === '') {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        return $requestId;
    }
}