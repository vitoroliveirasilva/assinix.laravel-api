<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => [
                'value' => $this->action?->value,
                'label' => $this->action?->label(),
            ],
            'auditable' => [
                'type' => $this->auditable_type,
                'id' => $this->auditable_id,
            ],
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'request_id' => $this->request_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}