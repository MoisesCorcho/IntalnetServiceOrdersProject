<?php

namespace App\Services;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ServiceOrderService
{
    public function listForTechnician(User $technician, int $perPage = 15): LengthAwarePaginator
    {
        return ServiceOrder::query()
            ->where('assigned_user_id', $technician->getKey())
            ->orderByDesc('check_in_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForTechnician(User $technician, int $serviceOrderId): ?ServiceOrder
    {
        return ServiceOrder::query()
            ->where('assigned_user_id', $technician->getKey())
            ->find($serviceOrderId);
    }

    public function advanceState(User $technician, int $serviceOrderId): ServiceOrder
    {
        $serviceOrder = $this->findForTechnician($technician, $serviceOrderId);

        if (! $serviceOrder) {
            throw ValidationException::withMessages([
                'service_order_id' => 'La orden de servicio no existe o no pertenece al técnico autenticado.',
            ]);
        }

        $status = EnumServiceOrderStatus::tryFrom($serviceOrder->state);

        if (! $status) {
            throw ValidationException::withMessages([
                'state' => 'Estado actual inválido.',
            ]);
        }

        $nextStatus = $status->next();

        if (! $nextStatus) {
            throw ValidationException::withMessages([
                'state' => 'La orden ya se encuentra en su último estado.',
            ]);
        }

        $serviceOrder->update([
            'state' => $nextStatus->value,
        ]);

        return $serviceOrder->refresh();
    }
}

