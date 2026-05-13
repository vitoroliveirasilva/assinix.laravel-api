<?php

namespace Database\Factories;

use App\Enums\SubscriptionHistoryEvent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'user_id' => User::factory(),
            'event' => fake()->randomElement(SubscriptionHistoryEvent::cases()),
            'old_values' => null,
            'new_values' => null,
            'description' => fake()->sentence(),
        ];
    }
}