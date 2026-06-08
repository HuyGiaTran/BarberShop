<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BarberController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Barber\BarberDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Public routes (Không yêu cầu đăng nhập)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes (Chỉ dành cho Guest - người chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Logout (dành cho tất cả authenticated users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================================
// ADMIN ROUTES (prefix: /admin, name: admin.*)
// Chỉ user có role = 'admin' mới truy cập được
// ============================================================
Route::middleware(['auth', 'admin'])->prefix('/admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
});

// ============================================================
// BARBER ROUTES (prefix: /barber, name: barber.*)
// Chỉ user có role = 'barber' mới truy cập được
// ============================================================
Route::middleware(['auth', 'barber'])->prefix('/barber')->name('barber.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [BarberDashboardController::class, 'index'])->name('dashboard');

    // Lịch hẹn
    Route::get('/appointments', [BarberDashboardController::class, 'appointments'])->name('appointments');
    Route::patch('/appointments/{appointment}/status', [BarberDashboardController::class, 'updateAppointmentStatus'])->name('appointments.updateStatus');

    // Hồ sơ cá nhân
    Route::get('/profile', [BarberDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [BarberDashboardController::class, 'updateProfile'])->name('profile.update');

    // Trạng thái hoạt động
    Route::patch('/status', [BarberDashboardController::class, 'updateWorkingStatus'])->name('status.update');
});
