<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires authentication to list categories', function (): void {
    $this->getJson('/api/v1/categories')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('creates a category for the authenticated user', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Streaming',
        'description' => 'Serviços recorrentes de streaming.',
        'color' => '#EF4444',
        'is_active' => true,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Categoria criada com sucesso.')
        ->assertJsonPath('data.category.name', 'Streaming')
        ->assertJsonPath('data.category.color', '#EF4444')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'category' => [
                    'id',
                    'name',
                    'description',
                    'color',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Streaming',
        'color' => '#EF4444',
    ]);
});

it('does not accept a duplicated category name for the same user', function (): void {
    $user = User::factory()->create();

    Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Streaming',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Streaming',
        'color' => '#EF4444',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonStructure([
            'errors' => [
                'name',
            ],
        ]);
});

it('allows the same category name for different users', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    Category::factory()->create([
        'user_id' => $firstUser->id,
        'name' => 'Streaming',
    ]);

    Sanctum::actingAs($secondUser);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Streaming',
        'color' => '#EF4444',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('categories', [
        'user_id' => $secondUser->id,
        'name' => 'Streaming',
    ]);
});

it('lists only categories from the authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Streaming',
    ]);

    Category::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Educação',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/categories');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonFragment(['name' => 'Streaming'])
        ->assertJsonMissing(['name' => 'Educação']);
});

it('shows a category owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Streaming',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.category.name', 'Streaming');
});

it('forbids showing another user category', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('updates a category owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old name',
        'color' => '#111111',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/categories/{$category->id}", [
        'name' => 'Streaming',
        'description' => 'Nova descrição.',
        'color' => '#222222',
        'is_active' => false,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Categoria atualizada com sucesso.')
        ->assertJsonPath('data.category.name', 'Streaming')
        ->assertJsonPath('data.category.is_active', false);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Streaming',
        'color' => '#222222',
        'is_active' => false,
    ]);
});

it('forbids updating another user category', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/categories/{$category->id}", [
        'name' => 'Attempt',
    ])->assertForbidden();
});

it('soft deletes a category owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Categoria removida com sucesso.');

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

it('forbids deleting another user category', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/categories/{$category->id}")
        ->assertForbidden();
});

it('validates category color format', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/categories', [
        'name' => 'Streaming',
        'color' => 'red',
    ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'color',
            ],
        ]);
});