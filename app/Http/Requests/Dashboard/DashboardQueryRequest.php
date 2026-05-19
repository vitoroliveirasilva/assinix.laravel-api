<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'years' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function attributes(): array
    {
        return [
            'start_date' => 'data inicial',
            'end_date' => 'data final',
            'months' => 'quantidade de meses',
            'years' => 'quantidade de anos',
        ];
    }

    public function months(): int
    {
        return (int) $this->validated('months', 12);
    }

    public function years(): int
    {
        return (int) $this->validated('years', 3);
    }
}
