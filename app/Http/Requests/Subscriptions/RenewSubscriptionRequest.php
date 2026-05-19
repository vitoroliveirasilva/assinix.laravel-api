<?php

namespace App\Http\Requests\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class RenewSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('renew', $this->route('subscription')) ?? false;
    }

    public function rules(): array
    {
        return [
            'last_charged_at' => ['sometimes', 'required', 'date'],
            'next_billing_at' => ['sometimes', 'required', 'date', 'after:last_charged_at'],
        ];
    }

    public function attributes(): array
    {
        return [
            'last_charged_at' => 'última cobrança',
            'next_billing_at' => 'próximo vencimento',
        ];
    }
}
