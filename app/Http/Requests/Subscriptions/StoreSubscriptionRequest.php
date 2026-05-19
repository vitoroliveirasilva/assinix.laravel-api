<?php

namespace App\Http\Requests\Subscriptions;

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Subscription::class) ?? false;
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
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'currency' => ['required', Rule::enum(CurrencyCode::class)],
            'status' => ['sometimes', 'required', Rule::enum(SubscriptionStatus::class)],
            'recurrence' => ['required', Rule::enum(RecurrenceType::class)],
            'interval' => ['sometimes', 'required', 'integer', 'min:1', 'max:120'],
            'interval_in_days' => [
                Rule::requiredIf(fn (): bool => $this->input('recurrence') === RecurrenceType::Custom->value),
                'nullable',
                'integer',
                'min:1',
                'max:3650',
            ],
            'starts_at' => ['required', 'date'],
            'next_billing_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'last_charged_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
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
