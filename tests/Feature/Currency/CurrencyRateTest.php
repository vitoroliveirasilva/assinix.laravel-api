<?php

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use App\Models\CurrencyRate;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function awesomeApiUsdResponse(string $bid = '5.0000'): array
{
    return [
        'USDBRL' => [
            'code' => 'USD',
            'codein' => 'BRL',
            'name' => 'Dólar Americano/Real Brasileiro',
            'high' => $bid,
            'low' => $bid,
            'varBid' => '0.0000',
            'pctChange' => '0.00',
            'bid' => $bid,
            'ask' => $bid,
            'timestamp' => '1778662800',
            'create_date' => '2026-05-13 10:00:00',
        ],
    ];
}

it('requires authentication to list currency rates', function (): void {
    $this->getJson('/api/v1/currencies/rates')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('refreshes a currency rate from awesome api and stores it', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(awesomeApiUsdResponse('5.2500'), 200),
    ]);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/currencies/refresh', [
        'currency' => 'USD',
        'update_subscriptions' => false,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Cotação atualizada com sucesso.')
        ->assertJsonPath('data.rate.base_currency.value', 'USD')
        ->assertJsonPath('data.rate.target_currency.value', 'BRL')
        ->assertJsonPath('data.rate.rate', '5.25000000')
        ->assertJsonPath('data.conversion_source', 'api')
        ->assertJsonPath('data.updated_subscriptions_count', 0);

    $this->assertDatabaseHas('currency_rates', [
        'base_currency' => 'USD',
        'target_currency' => 'BRL',
        'source' => 'awesomeapi',
    ]);

    Http::assertSentCount(1);
});

it('lists stored currency rates', function (): void {
    $user = User::factory()->create();

    CurrencyRate::factory()->usdToBrl(5.25)->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/currencies/rates?base_currency=USD')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('data.0.base_currency.value', 'USD')
        ->assertJsonPath('data.0.target_currency.value', 'BRL');
});

it('converts currency using the external api when cache is empty', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(awesomeApiUsdResponse('5.0000'), 200),
    ]);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/currencies/convert?amount=10&from_currency=USD&to_currency=BRL');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Conversão realizada com sucesso.')
        ->assertJsonPath('data.conversion.original_amount', '10.00')
        ->assertJsonPath('data.conversion.converted_amount', '50.00')
        ->assertJsonPath('data.conversion.from_currency', 'USD')
        ->assertJsonPath('data.conversion.to_currency', 'BRL')
        ->assertJsonPath('data.conversion.rate', '5.00000000')
        ->assertJsonPath('data.conversion.conversion_source', 'api');

    Http::assertSentCount(1);
});

it('uses cached currency rate without calling the external api', function (): void {
    Http::preventStrayRequests();
    Http::fake();

    $user = User::factory()->create();

    CurrencyRate::factory()->usdToBrl(5.0)->create([
        'updated_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/currencies/convert?amount=20&from_currency=USD&to_currency=BRL');

    $response
        ->assertOk()
        ->assertJsonPath('data.conversion.converted_amount', '100.00')
        ->assertJsonPath('data.conversion.conversion_source', 'cache');

    Http::assertNothingSent();
});

it('uses latest known rate as fallback when external api fails', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(['status' => 500], 500),
    ]);

    $user = User::factory()->create();

    CurrencyRate::factory()->usdToBrl(4.75)->create([
        'updated_at' => now()->subHours(5),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/currencies/convert?amount=10&from_currency=USD&to_currency=BRL&force_refresh=1');

    $response
        ->assertOk()
        ->assertJsonPath('data.conversion.converted_amount', '47.50')
        ->assertJsonPath('data.conversion.conversion_source', 'fallback');

    Http::assertSentCount(1);
});

it('returns a controlled error when there is no rate and external api fails', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(['status' => 500], 500),
    ]);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/currencies/convert?amount=10&from_currency=USD&to_currency=BRL&force_refresh=1')
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonStructure([
            'errors' => [
                'currency',
            ],
        ]);
});

it('updates user subscriptions when refreshing a currency rate', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(awesomeApiUsdResponse('5.0000'), 200),
    ]);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'GitHub Copilot',
        'amount' => 10.00,
        'currency' => CurrencyCode::USD,
        'amount_brl' => null,
        'exchange_rate' => null,
        'exchange_rate_date' => null,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
    ]);

    Subscription::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Outro usuário USD',
        'amount' => 10.00,
        'currency' => CurrencyCode::USD,
        'amount_brl' => null,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/currencies/refresh', [
        'currency' => 'USD',
        'update_subscriptions' => true,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.updated_subscriptions_count', 1);

    $subscription->refresh();

    expect($subscription->amount_brl)->toBe('50.00')
        ->and($subscription->exchange_rate)->toBe('5.00000000')
        ->and($subscription->exchange_rate_date)->not->toBeNull();
});

it('creates a foreign currency subscription with converted brl amount', function (): void {
    Http::preventStrayRequests();

    Http::fake([
        'https://economia.awesomeapi.com.br/*' => Http::response(awesomeApiUsdResponse('5.0000'), 200),
    ]);

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/subscriptions', [
        'name' => 'GitHub Copilot',
        'amount' => 10.00,
        'currency' => 'USD',
        'status' => SubscriptionStatus::Active->value,
        'recurrence' => RecurrenceType::Monthly->value,
        'interval' => 1,
        'starts_at' => now()->toDateString(),
        'next_billing_at' => now()->addMonth()->toDateString(),
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.subscription.currency.value', 'USD')
        ->assertJsonPath('data.subscription.amount_brl', '50.00')
        ->assertJsonPath('data.subscription.exchange_rate', '5.00000000');

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'name' => 'GitHub Copilot',
        'currency' => 'USD',
        'amount_brl' => 50.00,
    ]);
});