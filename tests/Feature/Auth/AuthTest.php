<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registers a new active user and returns a bearer token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Vitor Oliveira',
        'email' => 'VITOR@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'device_name' => 'Pest Test',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Usuário cadastrado com sucesso.')
        ->assertJsonPath('data.user.email', 'vitor@example.com')
        ->assertJsonPath('data.user.is_active', true)
        ->assertJsonPath('data.token.token_type', 'Bearer')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_active',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                'token' => [
                    'token_type',
                    'access_token',
                ],
            ],
            'errors',
            'meta' => [
                'request_id',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'vitor@example.com',
        'is_active' => true,
    ]);

    $this->assertNotEmpty($response->json('data.token.access_token'));
});

it('logs in an active user and returns a bearer token', function (): void {
    User::factory()->create([
        'email' => 'vitor@example.com',
        'password' => 'Password123',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'vitor@example.com',
        'password' => 'Password123',
        'device_name' => 'Pest Test',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login realizado com sucesso.')
        ->assertJsonPath('data.user.email', 'vitor@example.com')
        ->assertJsonPath('data.token.token_type', 'Bearer');

    $this->assertNotEmpty($response->json('data.token.access_token'));
});

it('does not reveal whether the email exists when login fails', function (): void {
    User::factory()->create([
        'email' => 'vitor@example.com',
        'password' => 'Password123',
    ]);

    $wrongPasswordResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'vitor@example.com',
        'password' => 'WrongPassword123',
    ]);

    $unknownEmailResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'unknown@example.com',
        'password' => 'WrongPassword123',
    ]);

    $wrongPasswordResponse
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Os dados informados são inválidos.')
        ->assertJsonPath('errors.email.0', 'As credenciais informadas são inválidas.');

    $unknownEmailResponse
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Os dados informados são inválidos.')
        ->assertJsonPath('errors.email.0', 'As credenciais informadas são inválidas.');
});

it('does not allow inactive users to login', function (): void {
    User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
        'password' => 'Password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'inactive@example.com',
        'password' => 'Password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.email.0', 'As credenciais informadas são inválidas.');
});

it('returns the authenticated user', function (): void {
    $user = User::factory()->create([
        'email' => 'vitor@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'vitor@example.com');
});

it('does not allow unauthenticated access to me endpoint', function (): void {
    $response = $this->getJson('/api/v1/me');

    $response
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Autenticação necessária.');
});

it('blocks inactive authenticated users from protected endpoints', function (): void {
    $user = User::factory()->inactive()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Sua conta está inativa.');
});

it('updates the authenticated user profile', function (): void {
    $user = User::factory()->create([
        'name' => 'Vitor',
        'email' => 'vitor@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/me', [
        'name' => 'Vitor Oliveira',
        'email' => 'VITOR.OLIVEIRA@example.com',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Perfil atualizado com sucesso.')
        ->assertJsonPath('data.user.name', 'Vitor Oliveira')
        ->assertJsonPath('data.user.email', 'vitor.oliveira@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Vitor Oliveira',
        'email' => 'vitor.oliveira@example.com',
    ]);
});

it('updates the authenticated user password', function (): void {
    $user = User::factory()->create([
        'password' => 'Password123',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/me/password', [
        'current_password' => 'Password123',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Senha alterada com sucesso.');

    expect(Hash::check('NewPassword123', $user->refresh()->password))->toBeTrue();
});

it('does not update password when current password is wrong', function (): void {
    $user = User::factory()->create([
        'password' => 'Password123',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/me/password', [
        'current_password' => 'WrongPassword123',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.current_password.0', 'A senha atual está incorreta.');
});

it('logs out the current token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('Pest Token')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout realizado com sucesso.');

    expect($user->tokens()->count())->toBe(0);
});

it('logs out all tokens', function (): void {
    $user = User::factory()->create();

    $token = $user->createToken('Current Token')->plainTextToken;
    $user->createToken('Another Token');

    expect($user->tokens()->count())->toBe(2);

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout-all');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout realizado em todos os dispositivos.');

    expect($user->tokens()->count())->toBe(0);
});

it('rate limits login attempts', function (): void {
    Cache::flush();

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'WrongPassword123',
        ])->assertUnprocessable();
    }

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'limited@example.com',
        'password' => 'WrongPassword123',
    ]);

    $response
        ->assertTooManyRequests()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Muitas tentativas de login, tente novamente em instantes.');
});