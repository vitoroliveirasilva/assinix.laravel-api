<?php

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires authentication to access dashboard summary', function (): void {
    $this->getJson('/api/v1/dashboard/summary')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('returns dashboard summary for authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 100.00,
        'amount_brl' => 100.00,
        'next_billing_at' => now()->addDays(10)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Academia',
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Yearly,
        'amount' => 1200.00,
        'amount_brl' => 1200.00,
        'next_billing_at' => now()->subDays(2)->toDateString(),
    ]);

    Subscription::factory()->paused()->create([
        'user_id' => $user->id,
        'name' => 'Pausada',
        'amount' => 300.00,
        'amount_brl' => 300.00,
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'USD sem conversão',
        'currency' => CurrencyCode::USD,
        'amount' => 10.00,
        'amount_brl' => null,
        'next_billing_at' => now()->addDays(60)->toDateString(),
    ]);

    Subscription::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Outro usuário',
        'amount' => 999.00,
        'amount_brl' => 999.00,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard/summary');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Resumo financeiro retornado com sucesso.')
        ->assertJsonPath('data.summary.base_currency', 'BRL')
        ->assertJsonPath('data.summary.totals.subscriptions', 4)
        ->assertJsonPath('data.summary.totals.active', 3)
        ->assertJsonPath('data.summary.totals.paused', 1)
        ->assertJsonPath('data.summary.financial.monthly_estimated_brl', 200)
        ->assertJsonPath('data.summary.financial.yearly_estimated_brl', 2400)
        ->assertJsonPath('data.summary.due.upcoming_30_days', 1)
        ->assertJsonPath('data.summary.due.overdue', 1)
        ->assertJsonPath('data.summary.currency.pending_conversion_count', 1);
});

it('returns dashboard grouped by category', function (): void {
    $user = User::factory()->create();

    $streaming = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Streaming',
    ]);

    $education = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Educação',
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'category_id' => $streaming->id,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 50.00,
        'amount_brl' => 50.00,
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'category_id' => $education->id,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 100.00,
        'amount_brl' => 100.00,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/dashboard/by-category')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.categories.0.category', 'Educação')
        ->assertJsonPath('data.categories.0.monthly_estimated_brl', 100)
        ->assertJsonPath('data.categories.1.category', 'Streaming')
        ->assertJsonPath('data.categories.1.monthly_estimated_brl', 50);
});

it('returns dashboard grouped by payment method', function (): void {
    $user = User::factory()->create();

    $card = PaymentMethod::factory()->creditCard()->create([
        'user_id' => $user->id,
        'name' => 'Cartão principal',
    ]);

    $pix = PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix',
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'payment_method_id' => $card->id,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 150.00,
        'amount_brl' => 150.00,
    ]);

    Subscription::factory()->create([
        'user_id' => $user->id,
        'payment_method_id' => $pix->id,
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 80.00,
        'amount_brl' => 80.00,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/dashboard/by-payment-method')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.payment_methods.0.payment_method', 'Cartão principal')
        ->assertJsonPath('data.payment_methods.0.monthly_estimated_brl', 150)
        ->assertJsonPath('data.payment_methods.1.payment_method', 'Pix')
        ->assertJsonPath('data.payment_methods.1.monthly_estimated_brl', 80);
});

it('returns monthly dashboard projection', function (): void {
    $user = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 100.00,
        'amount_brl' => 100.00,
        'next_billing_at' => now()->addDays(5)->toDateString(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard/monthly?months=2');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Dashboard mensal retornado com sucesso.')
        ->assertJsonCount(2, 'data.months')
        ->assertJsonPath('data.months.0.monthly_estimated_brl', 100);
});

it('returns yearly dashboard projection', function (): void {
    $user = User::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'name' => 'Netflix',
        'status' => SubscriptionStatus::Active,
        'recurrence' => RecurrenceType::Monthly,
        'amount' => 100.00,
        'amount_brl' => 100.00,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard/yearly?years=2');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Dashboard anual retornado com sucesso.')
        ->assertJsonCount(2, 'data.years')
        ->assertJsonPath('data.years.0.yearly_estimated_brl', 1200);
});

it('validates dashboard months limit', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/dashboard/monthly?months=99')
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'months',
            ],
        ]);
});
