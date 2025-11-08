<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceOrderResource;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceOrderController extends Controller
{
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

        $orders = $this->service->listForTechnician($technician, $perPage);

        return ServiceOrderResource::collection($orders);
    }

    public function show(Request $request, int $serviceOrderId): ServiceOrderResource
    {
        /** @var \App\Models\User $technician */
        $technician = $request->user();

        $order = $this->service->findForTechnician($technician, $serviceOrderId);

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

