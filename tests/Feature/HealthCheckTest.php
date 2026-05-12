<?php

it('returns the public health check response', function (): void {
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
            ],
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);
});

it('returns the versioned health check response', function (): void {
    $response = $this->getJson('/api/v1/health');

    $response
        ->assertOk()
        ->assertHeader('X-Request-Id')
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);
});

it('keeps an incoming request id when provided', function (): void {
    $requestId = 'test-request-id-123';

    $response = $this->withHeader('X-Request-Id', $requestId)
        ->getJson('/health');

    $response
        ->assertOk()
        ->assertHeader('X-Request-Id', $requestId)
        ->assertJsonPath('meta.request_id', $requestId);
});