<?php

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionFinancialCalculator;

function fakeSubscriptionForCalculation(array $attributes): Subscription
{
    $subscription = new Subscription();

    $subscription->forceFill(array_merge([
        'amount' => 120.00,
        'amount_brl' => 120.00,
        'currency' => CurrencyCode::BRL,
        'recurrence' => RecurrenceType::Monthly,
        'interval' => 1,
        'interval_in_days' => null,
    ], $attributes));

    return $subscription;
}

it('calculates monthly estimated amount for weekly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 30.00,
        'recurrence' => RecurrenceType::Weekly,
        'interval' => 1,
    ]);

    expect($calculator->monthlyEstimated($subscription))->toBe(130.0);
});

it('calculates yearly estimated amount for weekly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 30.00,
        'recurrence' => RecurrenceType::Weekly,
        'interval' => 1,
    ]);

    expect($calculator->yearlyEstimated($subscription))->toBe(1560.0);
});

it('calculates monthly estimated amount for monthly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 120.00,
        'recurrence' => RecurrenceType::Monthly,
        'interval' => 1,
    ]);

    expect($calculator->monthlyEstimated($subscription))->toBe(120.0);
});

it('calculates yearly estimated amount for monthly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 120.00,
        'recurrence' => RecurrenceType::Monthly,
        'interval' => 1,
    ]);

    expect($calculator->yearlyEstimated($subscription))->toBe(1440.0);
});

it('calculates monthly estimated amount for yearly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 1200.00,
        'recurrence' => RecurrenceType::Yearly,
        'interval' => 1,
    ]);

    expect($calculator->monthlyEstimated($subscription))->toBe(100.0);
});

it('calculates yearly estimated amount for yearly recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 1200.00,
        'recurrence' => RecurrenceType::Yearly,
        'interval' => 1,
    ]);

    expect($calculator->yearlyEstimated($subscription))->toBe(1200.0);
});

it('calculates monthly estimated amount for custom recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 50.00,
        'recurrence' => RecurrenceType::Custom,
        'interval_in_days' => 10,
    ]);

    expect($calculator->monthlyEstimated($subscription))->toBe(150.0);
});

it('calculates yearly estimated amount for custom recurrence', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount_brl' => 50.00,
        'recurrence' => RecurrenceType::Custom,
        'interval_in_days' => 10,
    ]);

    expect($calculator->yearlyEstimated($subscription))->toBe(1825.0);
});

it('returns zero for foreign currency without converted amount', function (): void {
    $calculator = new SubscriptionFinancialCalculator();

    $subscription = fakeSubscriptionForCalculation([
        'amount' => 10.00,
        'amount_brl' => null,
        'currency' => CurrencyCode::USD,
        'recurrence' => RecurrenceType::Monthly,
    ]);

    expect($calculator->monthlyEstimated($subscription))->toBe(0.0)
        ->and($calculator->yearlyEstimated($subscription))->toBe(0.0)
        ->and($calculator->hasPendingCurrencyConversion($subscription))->toBeTrue();
});