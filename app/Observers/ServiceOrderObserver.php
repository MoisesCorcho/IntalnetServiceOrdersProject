<?php

namespace App\Observers;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use Illuminate\Support\Carbon;

class ServiceOrderObserver
{
    public function creating(ServiceOrder $serviceOrder): void
    {
        if (
            $serviceOrder->state === EnumServiceOrderStatus::CLOSED->value
            && $serviceOrder->completed_at === null
        ) {
            $serviceOrder->completed_at = Carbon::now();
        }
    }

    public function updating(ServiceOrder $serviceOrder): void
    {
        if (
            $serviceOrder->isDirty('state')
            && $serviceOrder->state === EnumServiceOrderStatus::CLOSED->value
            && $serviceOrder->completed_at === null
        ) {
            $serviceOrder->completed_at = Carbon::now();
        }
    }
}

