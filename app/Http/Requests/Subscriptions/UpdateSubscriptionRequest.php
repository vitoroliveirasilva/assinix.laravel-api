<?php

namespace App\Http\Requests\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('subscription')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge([
                'currency' => Str::upper(trim((string) $this->input('currency'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'payment_method_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:999999999.99'],
            'currency' => ['sometimes', 'required', Rule::enum(CurrencyCode::class)],
            'status' => ['sometimes', 'required', Rule::enum(SubscriptionStatus::class)],
            'recurrence' => ['sometimes', 'required', Rule::enum(RecurrenceType::class)],
            'interval' => ['sometimes', 'required', 'integer', 'min:1', 'max:120'],
            'interval_in_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:3650'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'next_billing_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],
            'last_charged_at' => ['sometimes', 'nullable', 'date'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $subscription = $this->route('subscription');

                $startsAt = CarbonImmutable::parse(
                    $this->input('starts_at', $subscription->starts_at->toDateString())
                );

                $nextBillingAt = CarbonImmutable::parse(
                    $this->input('next_billing_at', $subscription->next_billing_at->toDateString())
                );

                $endsAt = $this->input('ends_at', $subscription->ends_at?->toDateString());

                $recurrence = $this->input('recurrence', $subscription->recurrence->value);
                $intervalInDays = $this->input('interval_in_days', $subscription->interval_in_days);

                if ($nextBillingAt->lt($startsAt)) {
                    $validator->errors()->add(
                        'next_billing_at',
                        'O próximo vencimento não pode ser anterior à data inicial.'
                    );
                }

                if ($endsAt !== null && CarbonImmutable::parse($endsAt)->lt($startsAt)) {
                    $validator->errors()->add(
                        'ends_at',
                        'A data final não pode ser anterior à data inicial.'
                    );
                }

                if ($recurrence === RecurrenceType::Custom->value && empty($intervalInDays)) {
                    $validator->errors()->add(
                        'interval_in_days',
                        'O intervalo em dias é obrigatório para recorrência personalizada.'
                    );
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'categoria',
            'payment_method_id' => 'forma de pagamento',
            'name' => 'nome',
            'description' => 'descrição',
            'amount' => 'valor',
            'currency' => 'moeda',
            'status' => 'status',
            'recurrence' => 'recorrência',
            'interval' => 'intervalo',
            'interval_in_days' => 'intervalo em dias',
            'starts_at' => 'data inicial',
            'next_billing_at' => 'próximo vencimento',
            'ends_at' => 'data final',
            'last_charged_at' => 'última cobrança',
            'metadata' => 'metadados',
        ];
    }
}
