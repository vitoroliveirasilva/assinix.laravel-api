<?php

namespace App\Support\RequestContext;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class RequestContext
{
    public static function id(?Request $request = null): string
    {
        if ($request instanceof Request) {
            return self::fromRequest($request);
        }

        try {
            if (function_exists('app') && app()->bound('request')) {
                $boundRequest = app('request');

                if ($boundRequest instanceof Request) {
                    return self::fromRequest($boundRequest);
                }
            }
        } catch (Throwable) {
            return self::consoleId();
        }

        return self::consoleId();
    }

    private static function fromRequest(Request $request): string
    {
        $existingRequestId = $request->attributes->get('request_id');

        if (is_string($existingRequestId) && $existingRequestId !== '') {
            return $existingRequestId;
        }

        $requestId = $request->headers->get('X-Request-Id');

        if (! is_string($requestId) || $requestId === '') {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        return $requestId;
    }

    private static function consoleId(): string
    {
        static $consoleRequestId = null;

        if ($consoleRequestId === null) {
            $consoleRequestId = 'console-' . (string) Str::uuid();
        }

        return $consoleRequestId;
    }
}
