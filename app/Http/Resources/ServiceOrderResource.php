<?php

namespace App\Http\Resources;

use App\Enums\EnumServiceOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ServiceOrder
 */
class ServiceOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = EnumServiceOrderStatus::tryFrom($this->state);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'title' => $this->title,
            'description' => $this->description,
            'state' => $this->state,
            'state_label' => $status?->label(),
            'check_in_date' => $this->check_in_date,
            'scheduled_at' => $this->scheduled_at,
            'completed_at' => $this->completed_at,
            'customer' => [
                'name' => $this->customer_name_snapshot,
                'phone' => $this->customer_phone_snapshot,
                'email' => $this->customer_email_snapshot,
                'address' => $this->customer_address_snapshot,
            ],
            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}

