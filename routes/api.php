<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\BarberApiController;
use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public API Routes
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/services/{id}', [ServiceApiController::class, 'show']);

Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/barbers/{id}', [BarberApiController::class, 'show']);

// Protected API Routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'user']);

    // Services (full CRUD)
    Route::post('/services', [ServiceApiController::class, 'store']);
    Route::put('/services/{id}', [ServiceApiController::class, 'update']);
    Route::delete('/services/{id}', [ServiceApiController::class, 'destroy']);

    // Barbers (full CRUD)
    Route::post('/barbers', [BarberApiController::class, 'store']);
    Route::put('/barbers/{id}', [BarberApiController::class, 'update']);
    Route::delete('/barbers/{id}', [BarberApiController::class, 'destroy']);

    // Appointments
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentApiController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentApiController::class, 'destroy']);

    // Users
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::put('/users/{id}', [UserApiController::class, 'update']);
});