<?php

use App\Enums\RecurrenceType;
use App\Enums\SubscriptionHistoryEvent;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('pauses an active subscription', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'status' => SubscriptionStatus::Active,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/pause")
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura pausada com sucesso.')
        ->assertJsonPath('data.subscription.status.value', 'paused');

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'paused',
    ]);

    $this->assertDatabaseHas('subscription_histories', [
        'subscription_id' => $subscription->id,
        'event' => SubscriptionHistoryEvent::Paused->value,
    ]);
});

it('does not pause a non active subscription', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->paused()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/pause")
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'status',
            ],
        ]);
});

it('resumes a paused subscription', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->paused()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/resume")
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura retomada com sucesso.')
        ->assertJsonPath('data.subscription.status.value', 'active');

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('subscription_histories', [
        'subscription_id' => $subscription->id,
        'event' => SubscriptionHistoryEvent::Resumed->value,
    ]);
});

it('cancels a subscription', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'status' => SubscriptionStatus::Active,
        'ends_at' => null,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/cancel")
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura cancelada com sucesso.')
        ->assertJsonPath('data.subscription.status.value', 'canceled');

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'canceled',
    ]);

    expect($subscription->refresh()->ends_at)->not->toBeNull();

    $this->assertDatabaseHas('subscription_histories', [
        'subscription_id' => $subscription->id,
        'event' => SubscriptionHistoryEvent::Canceled->value,
    ]);
});

it('does not renew a canceled subscription', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->canceled()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/renew")
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'status',
            ],
        ]);
});

it('renews a monthly subscription and calculates next billing date', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'interval' => 1,
        'starts_at' => '2026-01-01',
        'next_billing_at' => '2026-05-12',
        'last_charged_at' => null,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/renew")
        ->assertOk()
        ->assertJsonPath('message', 'Assinatura renovada com sucesso.')
        ->assertJsonPath('data.subscription.last_charged_at', '2026-05-12')
        ->assertJsonPath('data.subscription.next_billing_at', '2026-06-12');

    $this->assertDatabaseHas('subscription_histories', [
        'subscription_id' => $subscription->id,
        'event' => SubscriptionHistoryEvent::Renewed->value,
    ]);
});

it('renews a custom recurrence subscription using interval in days', function (): void {
    $user = User::factory()->create();

    $subscription = Subscription::factory()
        ->customEvery(45)
        ->create([
            'user_id' => $user->id,
            'next_billing_at' => '2026-05-12',
        ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/renew")
        ->assertOk()
        ->assertJsonPath('data.subscription.next_billing_at', '2026-06-26');
});

it('forbids status actions on another user subscription', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/pause")
        ->assertForbidden()
        ->assertJsonPath('success', false);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/resume")
        ->assertForbidden()
        ->assertJsonPath('success', false);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/cancel")
        ->assertForbidden()
        ->assertJsonPath('success', false);

    $this->patchJson("/api/v1/subscriptions/{$subscription->id}/renew")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});
