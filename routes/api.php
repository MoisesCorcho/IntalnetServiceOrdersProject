<?php

use App\Http\Controllers\Api\V1\ServiceOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

// Public routes
Route::middleware('guest:sanctum')->prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('service-orders')->group(function () {
        Route::get('/', [ServiceOrderController::class, 'index'])
            ->name('service-orders.index');

        Route::get('{serviceOrder}', [ServiceOrderController::class, 'show'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.show');

        Route::post('{serviceOrder}/advance-state', [ServiceOrderController::class, 'advanceState'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.advance-state');
    });
});
