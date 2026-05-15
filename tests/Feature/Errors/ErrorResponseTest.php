<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns standardized not found response', function (): void {
    $this->getJson('/api/v1/not-existing-route')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Recurso não encontrado.')
        ->assertJsonPath('data', null)
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

it('returns standardized method not allowed response', function (): void {
    $this->postJson('/api/v1/health')
        ->assertStatus(405)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Método HTTP não permitido para este recurso.');
});

it('returns standardized forbidden response', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Você não tem permissão para executar esta ação.');
});

it('returns security headers', function (): void {
    $this->getJson('/health')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
});