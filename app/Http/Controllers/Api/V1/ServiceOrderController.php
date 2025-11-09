<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EnumServiceOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceOrderResource;
use App\Services\ServiceOrderService;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ServiceOrderController extends Controller
{
    use ApiResponder;

    public function __construct(
        private readonly ServiceOrderService $service,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Models\User $technician */
        $technician = $request->user();

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 50));

        $status = $request->query('status');
        $statusEnum = filled($status) ? EnumServiceOrderStatus::tryFrom((string) $status) : null;

        $orders = $this->service->listForTechnician(
            technician: $technician,
            perPage: $perPage,
            status: $statusEnum,
        );

        return ServiceOrderResource::collection($orders);
    }

    public function show(Request $request, int $serviceOrderId): ServiceOrderResource|JsonResponse
    {
        /** @var \App\Models\User $technician */
        $technician = $request->user();

        $order = $this->service->findForTechnician($technician, $serviceOrderId);

        if (! $order) {
            return $this->errorResponse('La orden de servicio no existe o no pertenece al técnico autenticado.', Response::HTTP_NOT_FOUND);
        }

        return ServiceOrderResource::make($order);
    }

    public function advanceState(Request $request, int $serviceOrderId): ServiceOrderResource
    {
        /** @var \App\Models\User $technician */
        $technician = $request->user();

        $order = $this->service->advanceState($technician, $serviceOrderId);

        return ServiceOrderResource::make($order);
    }
}

