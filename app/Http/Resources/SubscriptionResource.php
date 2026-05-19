<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'category' => $this->whenLoaded(
                'category',
                fn () => $this->category
                ? CategoryResource::make($this->category)->resolve($request)
                : null,
            ),

            'payment_method' => $this->whenLoaded(
                'paymentMethod',
                fn () => $this->paymentMethod
                ? PaymentMethodResource::make($this->paymentMethod)->resolve($request)
                : null,
            ),

            'name' => $this->name,
            'description' => $this->description,

            'amount' => $this->amount,
            'currency' => [
                'value' => $this->currency?->value,
                'label' => $this->currency?->label(),
            ],

            'amount_brl' => $this->amount_brl,
            'exchange_rate' => $this->exchange_rate,
            'exchange_rate_date' => $this->exchange_rate_date?->toDateString(),

            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ],

            'recurrence' => [
                'value' => $this->recurrence?->value,
                'label' => $this->recurrence?->label(),
            ],

            'interval' => $this->interval,
            'interval_in_days' => $this->interval_in_days,

            'starts_at' => $this->starts_at?->toDateString(),
            'next_billing_at' => $this->next_billing_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'last_charged_at' => $this->last_charged_at?->toDateString(),

            'metadata' => $this->metadata,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
