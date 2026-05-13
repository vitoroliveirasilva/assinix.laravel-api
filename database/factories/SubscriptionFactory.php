<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-3 months', 'now');
        $nextBillingAt = fake()->dateTimeBetween('now', '+2 months');

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'payment_method_id' => null,
            'name' => fake()->randomElement(['Netflix', 'Spotify', 'Internet', 'Academia']),
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'currency' => CurrencyCode::BRL,
            'amount_brl' => fake()->randomFloat(2, 10, 500),
            'exchange_rate' => 1,
            'exchange_rate_date' => now()->toDateString(),
            'status' => SubscriptionStatus::Active,
            'recurrence' => RecurrenceType::Monthly,
            'interval' => 1,
            'interval_in_days' => null,
            'starts_at' => $startsAt,
            'next_billing_at' => $nextBillingAt,
            'ends_at' => null,
            'last_charged_at' => null,
            'metadata' => null,
        ];
    }

    public function forUserWithRelations(User $user): static
    {
        return $this->state(fn(array $attributes): array => [
            'user_id' => $user->id,
            'category_id' => Category::factory()->for($user)->create()->id,
            'payment_method_id' => PaymentMethod::factory()->pix()->for($user)->create()->id,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => SubscriptionStatus::Paused,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => SubscriptionStatus::Canceled,
            'ends_at' => now()->toDateString(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()->subDay()->toDateString(),
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn(array $attributes): array => [
            'recurrence' => RecurrenceType::Weekly,
            'interval' => 1,
            'interval_in_days' => null,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn(array $attributes): array => [
            'recurrence' => RecurrenceType::Yearly,
            'interval' => 1,
            'interval_in_days' => null,
        ]);
    }

    public function customEvery(int $days): static
    {
        return $this->state(fn(array $attributes): array => [
            'recurrence' => RecurrenceType::Custom,
            'interval' => 1,
            'interval_in_days' => $days,
        ]);
    }
}