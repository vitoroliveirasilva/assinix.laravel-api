<?php

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires authentication to list upcoming subscriptions', function (): void {
    $this->getJson('/api/v1/subscriptions/upcoming')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('lists only active upcoming subscriptions from authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDays(10)->toDateString(),
    ]);

    Subscription::factory()->paused()->create([
        'user_id' => $user->id,
        'name' => 'Pausada',
        'next_billing_at' => now()->addDays(10)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Outra pessoa',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDays(10)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Fora do período',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDays(60)->toDateString(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/subscriptions/upcoming?days=30');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Próximos vencimentos retornados com sucesso.')
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonFragment(['name' => 'Netflix'])
        ->assertJsonMissing(['name' => 'Pausada'])
        ->assertJsonMissing(['name' => 'Outra pessoa'])
        ->assertJsonMissing(['name' => 'Fora do período']);
});

it('lists only active overdue subscriptions from authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Internet',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->subDays(3)->toDateString(),
    ]);

    Subscription::factory()->paused()->create([
        'user_id' => $user->id,
        'name' => 'Pausada vencida',
        'next_billing_at' => now()->subDays(3)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Vencida do outro usuário',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->subDays(3)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Ainda não venceu',
        'status' => SubscriptionStatus::Active,
        'next_billing_at' => now()->addDays(3)->toDateString(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/subscriptions/overdue');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Assinaturas vencidas retornadas com sucesso.')
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonFragment(['name' => 'Internet'])
        ->assertJsonMissing(['name' => 'Pausada vencida'])
        ->assertJsonMissing(['name' => 'Vencida do outro usuário'])
        ->assertJsonMissing(['name' => 'Ainda não venceu']);
});

it('validates upcoming days query parameter', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/subscriptions/upcoming?days=999')
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'days',
            ],
        ]);
});