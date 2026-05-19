<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'base_currency' => [
                'value' => $this->base_currency?->value,
                'label' => $this->base_currency?->label(),
            ],
            'target_currency' => [
                'value' => $this->target_currency?->value,
                'label' => $this->target_currency?->label(),
            ],
            'rate' => $this->rate,
            'source' => $this->source,
            'quoted_at' => $this->quoted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
