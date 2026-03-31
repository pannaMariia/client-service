<?php

use App\Http\Controllers\API\HealthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index']);
    Route::apiResource('users', UserController::class);
});
