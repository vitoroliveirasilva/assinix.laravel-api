<?php

it('exposes the openapi json documentation', function (): void {
    $response = $this->getJson('/docs/api.json');

    $response
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0')
        ->assertJsonStructure([
            'openapi',
            'info',
            'paths',
        ]);
});

it('exposes the interactive api documentation page', function (): void {
    $this->get('/docs/api')
        ->assertOk();
});
