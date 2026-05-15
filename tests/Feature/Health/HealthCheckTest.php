<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('returns complete health check response', function (): void {
    $response = $this->getJson('/health');

    $response
        ->assertOk()
        ->assertHeader('X-Request-Id')
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Health check completed successfully.')
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'service',
                'environment',
                'timestamp',
                'checks' => [
                    'app' => [
                        'status',
                        'message',
                    ],
                    'database' => [
                        'status',
                        'message',
                    ],
                    'cache' => [
                        'status',
                        'message',
                    ],
                ],
            ],
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);
});

it('keeps an incoming request id when provided', function (): void {
    $requestId = '22222222-2222-4222-8222-222222222222';

    $response = $this->withHeader('X-Request-Id', $requestId)
        ->getJson('/health');

    $response
        ->assertOk()
        ->assertHeader('X-Request-Id', $requestId)
        ->assertJsonPath('meta.request_id', $requestId);
});