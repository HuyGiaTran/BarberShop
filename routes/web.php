<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BarberController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\BarberScheduleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('/admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/barbers')->name('barbers.')->group(function () {
        Route::get('/', [BarberController::class, 'index'])->name('index');
        Route::get('/create', [BarberController::class, 'create'])->name('create');
        Route::post('/', [BarberController::class, 'store'])->name('store');
        Route::get('/{barber}', [BarberController::class, 'show'])->name('show');
        Route::get('/{barber}/edit', [BarberController::class, 'edit'])->name('edit');
        Route::put('/{barber}', [BarberController::class, 'update'])->name('update');
        Route::delete('/{barber}', [BarberController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('/schedules')->name('schedules.')->group(function () {
        Route::get('/', [BarberScheduleController::class, 'index'])->name('index');
        Route::put('/{barber}', [BarberScheduleController::class, 'update'])->name('update');
    });

    Route::prefix('/services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/create', [ServiceController::class, 'create'])->name('create');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('show');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
    });

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

    Route::prefix('/invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::patch('/{invoice}/mark-cash-paid', [InvoiceController::class, 'markCashPaid'])->name('markCashPaid');
    });

    Route::prefix('/reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('/leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::patch('/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus'])->name('updateStatus');
    });

    Route::prefix('/payrolls')->name('payrolls.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/calculate', [PayrollController::class, 'calculate'])->name('calculate');
        Route::patch('/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('markPaid');
    });
});

Route::middleware(['auth', 'barber'])->prefix('/barber')->name('barber.')->group(function () {
    Route::get('/dashboard', [BarberDashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-status', [BarberDashboardController::class, 'toggleStatus'])->name('toggleStatus');
});
