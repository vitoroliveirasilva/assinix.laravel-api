<?php

namespace App\Http\Requests\Currency;

use App\Enums\CurrencyCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CurrencyRateIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['base_currency', 'target_currency'] as $field) {
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
            'base_currency' => ['nullable', Rule::enum(CurrencyCode::class)],
            'target_currency' => ['nullable', Rule::enum(CurrencyCode::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
