<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BarberController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Trang chủ - Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// CRUD Barbers
Route::prefix('/barbers')->name('barbers.')->group(function () {
    Route::get('/', [BarberController::class, 'index'])->name('index');
    Route::get('/create', [BarberController::class, 'create'])->name('create');
    Route::post('/', [BarberController::class, 'store'])->name('store');
    Route::get('/{barber}', [BarberController::class, 'show'])->name('show');
    Route::get('/{barber}/edit', [BarberController::class, 'edit'])->name('edit');
    Route::put('/{barber}', [BarberController::class, 'update'])->name('update');
    Route::delete('/{barber}', [BarberController::class, 'destroy'])->name('destroy');
});

// CRUD Services
Route::prefix('/services')->name('services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/create', [ServiceController::class, 'create'])->name('create');
    Route::post('/', [ServiceController::class, 'store'])->name('store');
    Route::get('/{service}', [ServiceController::class, 'show'])->name('show');
    Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
    Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
});

// CRUD Appointments
Route::prefix('/appointments')->name('appointments.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::get('/create', [AppointmentController::class, 'create'])->name('create');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
    Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
    Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
    Route::patch('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('updateStatus');
});