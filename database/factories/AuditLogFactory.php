<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(AuditAction::cases()),
            'auditable_type' => null,
            'auditable_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'request_id' => fake()->uuid(),
            'metadata' => [
                'route_name' => 'test.route',
            ],
            'created_at' => now(),
        ];
    }
}
