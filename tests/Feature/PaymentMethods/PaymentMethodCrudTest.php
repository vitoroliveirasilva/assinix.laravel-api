<?php

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires authentication to list payment methods', function (): void {
    $this->getJson('/api/v1/payment-methods')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('creates a pix payment method for the authenticated user', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/payment-methods', [
        'name' => 'Pix',
        'type' => PaymentMethodType::Pix->value,
        'is_active' => true,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Forma de pagamento criada com sucesso.')
        ->assertJsonPath('data.payment_method.name', 'Pix')
        ->assertJsonPath('data.payment_method.type.value', 'pix')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'payment_method' => [
                    'id',
                    'name',
                    'type' => [
                        'value',
                        'label',
                    ],
                    'brand',
                    'last_four',
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

    $this->assertDatabaseHas('payment_methods', [
        'user_id' => $user->id,
        'name' => 'Pix',
        'type' => 'pix',
    ]);
});

it('creates a credit card payment method when card data is provided', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/payment-methods', [
        'name' => 'Cartão principal',
        'type' => PaymentMethodType::CreditCard->value,
        'brand' => 'Visa',
        'last_four' => '1234',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.payment_method.type.value', 'credit_card')
        ->assertJsonPath('data.payment_method.brand', 'Visa')
        ->assertJsonPath('data.payment_method.last_four', '1234');

    $this->assertDatabaseHas('payment_methods', [
        'user_id' => $user->id,
        'name' => 'Cartão principal',
        'type' => 'credit_card',
        'brand' => 'Visa',
        'last_four' => '1234',
    ]);
});

it('requires card data when payment method type is card', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/payment-methods', [
        'name' => 'Cartão principal',
        'type' => PaymentMethodType::CreditCard->value,
    ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'brand',
                'last_four',
            ],
        ]);
});

it('validates payment method type enum', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/payment-methods', [
        'name' => 'Forma estranha',
        'type' => 'banana_card',
    ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'type',
            ],
        ]);
});

it('does not accept a duplicated payment method name for the same user', function (): void {
    $user = User::factory()->create();

    PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/payment-methods', [
        'name' => 'Pix',
        'type' => PaymentMethodType::Pix->value,
    ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'errors' => [
                'name',
            ],
        ]);
});

it('lists only payment methods from the authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix',
    ]);

    PaymentMethod::factory()->creditCard()->create([
        'user_id' => $otherUser->id,
        'name' => 'Cartão do outro usuário',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/payment-methods');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonFragment(['name' => 'Pix'])
        ->assertJsonMissing(['name' => 'Cartão do outro usuário']);
});

it('shows a payment method owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/payment-methods/{$paymentMethod->id}")
        ->assertOk()
        ->assertJsonPath('data.payment_method.name', 'Pix');
});

it('forbids showing another user payment method', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/payment-methods/{$paymentMethod->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('updates a payment method owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
        'name' => 'Pix antigo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/payment-methods/{$paymentMethod->id}", [
        'name' => 'Pix principal',
        'is_active' => false,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Forma de pagamento atualizada com sucesso.')
        ->assertJsonPath('data.payment_method.name', 'Pix principal')
        ->assertJsonPath('data.payment_method.is_active', false);

    $this->assertDatabaseHas('payment_methods', [
        'id' => $paymentMethod->id,
        'name' => 'Pix principal',
        'is_active' => false,
    ]);
});

it('forbids updating another user payment method', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/payment-methods/{$paymentMethod->id}", [
        'name' => 'Attempt',
    ])->assertForbidden();
});

it('soft deletes a payment method owned by the authenticated user', function (): void {
    $user = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/payment-methods/{$paymentMethod->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Forma de pagamento removida com sucesso.');

    $this->assertSoftDeleted('payment_methods', [
        'id' => $paymentMethod->id,
    ]);
});

it('forbids deleting another user payment method', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $paymentMethod = PaymentMethod::factory()->pix()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/payment-methods/{$paymentMethod->id}")
        ->assertForbidden();
});