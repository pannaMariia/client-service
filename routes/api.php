<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
//маршрут для тестирования
Route::get('/hello', fn() => ['message' => 'hello']);
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
// Тестовый webhook для отладки
Route::post('/webhook/user-created', [App\Http\Controllers\WebhookController::class, 'userCreated']);
