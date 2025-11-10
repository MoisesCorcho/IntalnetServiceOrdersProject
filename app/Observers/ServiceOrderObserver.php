<?php
namespace App\Observers;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Services\FcmService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class ServiceOrderObserver
{
    public function __construct(
        private readonly FcmService $fcmService
    ) {
    }

    public function creating(ServiceOrder $serviceOrder): void
    {
        if (
            $serviceOrder->state === EnumServiceOrderStatus::CLOSED->value
            && $serviceOrder->completed_at === null
        ) {
            $serviceOrder->completed_at = Carbon::now();
        }
    }

    public function created(ServiceOrder $serviceOrder): void
    {
        $assignedUser = $serviceOrder->assignedUser;

        if ($assignedUser === null) {
            return;
        }

        if (! $assignedUser->fcmTokens()->exists()) {
            return;
        }

        $orderNumber = $serviceOrder->order_number ?? $serviceOrder->getKey();

        $title = 'Nueva orden de servicio';
        $body = Str::limit($serviceOrder->title ?? 'Se ha registrado una nueva orden', 120);

        try {
            $this->fcmService->sendToUser(
                $assignedUser,
                [
                    'title' => $title,
                    'body' => "Orden #{$orderNumber}: {$body}",
                ],
                [
                    'service_order_id' => (string) $serviceOrder->getKey(),
                    'order_number' => (string) $orderNumber,
                    'state' => (string) $serviceOrder->state,
                ]
            );
        } catch (Throwable $exception) {
            report($exception);
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