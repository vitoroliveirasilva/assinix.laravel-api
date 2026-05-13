<?php

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function subscriptionPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Netflix',
        'description' => 'Plano mensal de streaming.',
        'amount' => 55.90,
        'currency' => CurrencyCode::BRL->value,
        'status' => SubscriptionStatus::Active->value,
        'recurrence' => RecurrenceType::Monthly->value,
        'interval' => 1,
        'starts_at' => now()->toDateString(),
        'next_billing_at' => now()->addMonth()->toDateString(),
        'metadata' => [
            'notes' => 'Conta compartilhada.',
        ],
    ], $overrides);
}

it('requires authentication to list subscriptions', function (): void {
    $this->getJson('/api/v1/subscriptions')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('creates a subscription for the authenticated user', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Streaming',
    ]);

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'category_id' => $category->id,
        'payment_method_id' => $paymentMethod->id,
    ]));

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Assinatura criada com sucesso.')
        ->assertJsonPath('data.subscription.name', 'Netflix')
        ->assertJsonPath('data.subscription.amount', '55.90')
        ->assertJsonPath('data.subscription.currency.value', 'BRL')
        ->assertJsonPath('data.subscription.status.value', 'active')
        ->assertJsonPath('data.subscription.recurrence.value', 'monthly')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'subscription' => [
                    'id',
                    'category',
                    'payment_method',
                    'name',
                    'description',
                    'amount',
                    'currency' => [
                        'value',
                        'label',
                    ],
                    'amount_brl',
                    'exchange_rate',
                    'exchange_rate_date',
                    'status' => [
                        'value',
                        'label',
                    ],
                    'recurrence' => [
                        'value',
                        'label',
                    ],
                    'interval',
                    'interval_in_days',
                    'starts_at',
                    'next_billing_at',
                    'ends_at',
                    'last_charged_at',
                    'metadata',
                    'created_at',
                    'updated_at',
                ],
            ],
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'payment_method_id' => $paymentMethod->id,
        'name' => 'Netflix',
        'currency' => 'BRL',
        'status' => 'active',
        'recurrence' => 'monthly',
    ]);

    $this->assertDatabaseHas('subscription_histories', [
        'user_id' => $user->id,
        'event' => SubscriptionHistoryEvent::Created->value,
        'description' => 'Assinatura criada.',
    ]);
});

it('does not allow the client to create subscription for another user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'user_id' => $otherUser->id,
    ]));

    $response->assertCreated();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'name' => 'Netflix',
    ]);

    $this->assertDatabaseMissing('subscriptions', [
        'user_id' => $otherUser->id,
        'name' => 'Netflix',
    ]);
});

it('does not allow using another user category', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'category_id' => $otherCategory->id,
    ]))
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'category_id',
            ],
        ]);
});

it('does not allow using another user payment method', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherPaymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'payment_method_id' => $otherPaymentMethod->id,
    ]))
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'payment_method_id',
            ],
        ]);
});

it('validates amount greater than zero', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'amount' => 0,
    ]))
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'amount',
            ],
        ]);
});

it('validates next billing date cannot be before start date', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'starts_at' => '2026-05-10',
        'next_billing_at' => '2026-05-09',
    ]))
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'next_billing_at',
            ],
        ]);
});

it('requires interval in days for custom recurrence', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/subscriptions', subscriptionPayload([
        'recurrence' => RecurrenceType::Custom->value,
        'interval_in_days' => null,
    ]))
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'interval_in_days',
            ],
        ]);
});

it('lists only subscriptions from the authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
    ]);

    Subscription::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Spotify do outro usuário',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/subscriptions');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonFragment(['name' => 'Netflix'])
        ->assertJsonMissing(['name' => 'Spotify do outro usuário']);
});

it('shows a subscription owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/subscriptions/{$subscription->id}")
        ->assertOk()
        ->assertJsonPath('data.subscription.name', 'Netflix');
});

it('forbids showing another user subscription', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/subscriptions/{$subscription->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('updates a subscription owned by the authenticated user and records history', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
        'amount' => 55.90,
        'amount_brl' => 55.90,
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/subscriptions/{$subscription->id}", [
        'name' => 'Netflix Premium',
        'amount' => 69.90,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura atualizada com sucesso.')
        ->assertJsonPath('data.subscription.name', 'Netflix Premium')
        ->assertJsonPath('data.subscription.amount', '69.90');

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'name' => 'Netflix Premium',
        'amount' => 69.90,
    ]);

    $this->assertDatabaseHas('subscription_histories', [
        'subscription_id' => $subscription->id,
        'user_id' => $user->id,
        'event' => SubscriptionHistoryEvent::Updated->value,
    ]);
});

it('forbids updating another user subscription', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}", [
        'name' => 'Attempt',
    ])->assertForbidden();
});

it('soft deletes a subscription owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/subscriptions/{$subscription->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura removida com sucesso.');

    $this->assertSoftDeleted('subscriptions', [
        'id' => $subscription->id,
    ]);
});

it('returns subscription history for the owner', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
    ]);

    $subscription->histories()->create([
        'user_id' => $user->id,
        'event' => SubscriptionHistoryEvent::Created,
        'old_values' => null,
        'new_values' => ['name' => 'Netflix'],
        'description' => 'Assinatura criada.',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/subscriptions/{$subscription->id}/history")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('data.0.event.value', 'subscription_created');
});