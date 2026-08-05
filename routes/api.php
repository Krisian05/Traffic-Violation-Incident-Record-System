<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OfficerApiController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

// Unauthenticated PayMongo Webhook Callback Listener
Route::post('/webhooks/paymongo', [\App\Http\Controllers\PayMongoWebhookController::class, 'handle'])->name('webhooks.paymongo');

Route::prefix('v1')->group(function () {
    // Health Check route
    Route::get('/health', [HealthCheckController::class, 'check']);

    // Guest Auth route
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated API routes
    Route::middleware('api.auth')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Officer Specific Operations (requires traffic_officer or admin role)
        Route::middleware('role:traffic_officer,admin')->prefix('officer')->group(function () {
            Route::get('/dashboard', [OfficerApiController::class, 'dashboard']);
            Route::get('/motorists', [OfficerApiController::class, 'motorists']);
            Route::post('/motorists', [OfficerApiController::class, 'storeMotorist']);
            Route::post('/vehicles', [OfficerApiController::class, 'storeVehicle']);
            Route::post('/violations', [OfficerApiController::class, 'storeViolation']);
            Route::post('/incidents', [OfficerApiController::class, 'storeIncident']);
        });
    });
});
