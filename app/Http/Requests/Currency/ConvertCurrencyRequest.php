<?php

namespace App\Http\Requests\Currency;

use App\Enums\CurrencyCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConvertCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['from_currency', 'to_currency'] as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => Str::upper(trim((string) $this->input($field))),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'from_currency' => ['required', Rule::enum(CurrencyCode::class)],
            'to_currency' => ['sometimes', 'required', Rule::enum(CurrencyCode::class)],
            'force_refresh' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'valor',
            'from_currency' => 'moeda de origem',
            'to_currency' => 'moeda de destino',
            'force_refresh' => 'forçar atualização',
        ];
    }
}