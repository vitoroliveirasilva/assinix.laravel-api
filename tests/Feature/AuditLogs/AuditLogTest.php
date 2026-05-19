<?php

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates an audit log when user registers', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Vitor Oliveira',
        'email' => 'vitor@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'device_name' => 'Pest Test',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::UserRegistered->value,
    ]);
});

it('creates an audit log when user logs in', function (): void {
    User::factory()->create([
        'email' => 'vitor@example.com',
        'password' => 'Password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'vitor@example.com',
        'password' => 'Password123',
        'device_name' => 'Pest Test',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::UserLoggedIn->value,
    ]);
});

it('creates an audit log for authenticated api mutations', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Streaming',
        'description' => 'Serviços de streaming.',
        'color' => '#EF4444',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => AuditAction::CategoryCreated->value,
    ]);
});

it('does not store sensitive data in audit metadata', function (): void {
    $user = User::factory()->create([
        'password' => 'Password123',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/me/password', [
        'current_password' => 'Password123',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response->assertOk();

    $auditLog = AuditLog::query()
        ->where('user_id', $user->id)
        ->where('action', AuditAction::PasswordChanged)
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();

    $metadata = json_encode($auditLog->metadata);

    expect($metadata)->not->toContain('Password123')
        ->and($metadata)->not->toContain('NewPassword123')
        ->and($metadata)->toContain('[REDACTED]');
});

it('lists only audit logs from authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    AuditLog::factory()->create([
        'user_id' => $user->id,
        'action' => AuditAction::CategoryCreated,
    ]);

    AuditLog::factory()->create([
        'user_id' => $otherUser->id,
        'action' => AuditAction::SubscriptionCreated,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/audit-logs');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logs de auditoria retornados com sucesso.')
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('data.0.action.value', AuditAction::CategoryCreated->value);
});

it('filters audit logs by action', function (): void {
    $user = User::factory()->create();

    AuditLog::factory()->create([
        'user_id' => $user->id,
        'action' => AuditAction::CategoryCreated,
    ]);

    AuditLog::factory()->create([
        'user_id' => $user->id,
        'action' => AuditAction::SubscriptionCreated,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/audit-logs?action='.AuditAction::SubscriptionCreated->value);

    $response
        ->assertOk()
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('data.0.action.value', AuditAction::SubscriptionCreated->value);
});

it('requires authentication to list audit logs', function (): void {
    $this->getJson('/api/v1/audit-logs')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});
