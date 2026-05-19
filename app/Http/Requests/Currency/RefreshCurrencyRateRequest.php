<?php

namespace App\Http\Requests\Currency;

use App\Enums\CurrencyCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RefreshCurrencyRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
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
            'currency' => [
                'required',
                Rule::enum(CurrencyCode::class),
                'not_in:'.CurrencyCode::BRL->value,
            ],
            'update_subscriptions' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'currency' => 'moeda',
            'update_subscriptions' => 'atualizar assinaturas',
        ];
    }
}
