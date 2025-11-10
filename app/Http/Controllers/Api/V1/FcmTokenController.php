<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FcmTokenController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly FcmService $service) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $token = $this->service->registerToken(
            user: $user,
            token: $validated['token'],
            deviceName: $validated['device_name'] ?? null,
        );

        return $this->successResponse([
            'id' => $token->getKey(),
            'token' => $token->token,
            'device_name' => $token->device_name,
        ], 'Token registrado correctamente.', Response::HTTP_CREATED);
    }
}
