<?php

namespace App\Http\Requests\PaymentMethods;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PaymentMethod::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('payment_methods', 'name')
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->whereNull('deleted_at')),
            ],
            'type' => ['required', Rule::enum(PaymentMethodType::class)],
            'brand' => [
                'nullable',
                'string',
                'max:50',
                'required_if:type,credit_card,debit_card',
            ],
            'last_four' => [
                'nullable',
                'digits:4',
                'required_if:type,credit_card,debit_card',
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'type' => 'tipo',
            'brand' => 'bandeira',
            'last_four' => 'últimos quatro dígitos',
            'is_active' => 'ativo',
        ];
    }
}
