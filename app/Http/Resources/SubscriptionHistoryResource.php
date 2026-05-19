<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => [
                'value' => $this->event?->value,
                'label' => $this->event?->label(),
            ],
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
