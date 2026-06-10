<?php

use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BarberApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\VnpayController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/services/{id}', [ServiceApiController::class, 'show']);

Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/barbers/{id}', [BarberApiController::class, 'show']);
Route::get('/barbers/{id}/reviews', [App\Http\Controllers\Api\ReviewApiController::class, 'index']);

Route::post('/chatbot/ask', [App\Http\Controllers\Api\ChatbotController::class, 'ask']);
Route::match(['GET', 'POST'], '/vnpay/callback', [VnpayController::class, 'callback']);
Route::match(['GET', 'POST'], '/vnpay/ipn', [VnpayController::class, 'ipn']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'user']);

    Route::middleware('admin')->group(function () {
        Route::post('/services', [ServiceApiController::class, 'store']);
        Route::put('/services/{id}', [ServiceApiController::class, 'update']);
        Route::delete('/services/{id}', [ServiceApiController::class, 'destroy']);

        Route::post('/barbers', [BarberApiController::class, 'store']);
        Route::put('/barbers/{id}', [BarberApiController::class, 'update']);
        Route::delete('/barbers/{id}', [BarberApiController::class, 'destroy']);

        Route::get('/users', [UserApiController::class, 'index']);
    });

    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentApiController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentApiController::class, 'destroy']);

    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::put('/users/{id}', [UserApiController::class, 'update']);

    Route::get('/barbers/{id}/slots', [AppointmentApiController::class, 'slots']);
    Route::post('/vnpay/create-payment', [VnpayController::class, 'createPayment']);
    Route::post('/reviews', [App\Http\Controllers\Api\ReviewApiController::class, 'store']);
    Route::get('/loyalty', [App\Http\Controllers\Api\LoyaltyApiController::class, 'show']);
    Route::get('/statistics/revenue', [App\Http\Controllers\Api\StatisticApiController::class, 'revenue']);
    Route::get('/statistics/peak-hours', [App\Http\Controllers\Api\StatisticApiController::class, 'peakHours']);
    Route::get('/statistics/services', [App\Http\Controllers\Api\StatisticApiController::class, 'popularServices']);

    // Barber specific routes
    Route::get('/barber/leave-requests', [App\Http\Controllers\Api\LeaveRequestApiController::class, 'index']);
    Route::post('/barber/leave-requests', [App\Http\Controllers\Api\LeaveRequestApiController::class, 'store']);
});
