<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'full_name' => $this->whenHasFullName(),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'phone' => $this->phone,
            'secondary_phone' => $this->secondary_phone,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }

    protected function whenHasFullName(): ?string
    {
        $fullName = $this->resource->full_name ?? null;

        return $fullName !== '' ? $fullName : null;
    }
}
