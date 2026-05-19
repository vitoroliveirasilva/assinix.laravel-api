<?php

namespace App\Http\Requests\AuditLogs;

use App\Enums\AuditAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', Rule::enum(AuditAction::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'ação',
            'from' => 'data inicial',
            'to' => 'data final',
            'per_page' => 'itens por página',
        ];
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }
}
