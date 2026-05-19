<?php

namespace App\Http\Requests\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionDueQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
    }

    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'days' => 'dias',
            'per_page' => 'itens por página',
        ];
    }

    public function days(): int
    {
        return (int) $this->validated('days', 30);
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }
}
