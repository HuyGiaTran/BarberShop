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
| Here is where you can register API routes for your application.
|
*/

// ========================================================================
// PUBLIC API (Không yêu cầu đăng nhập)
// ========================================================================

// Auth
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// Services
Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/services/{id}', [ServiceApiController::class, 'show']);

// Barbers
Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/barbers/{id}', [BarberApiController::class, 'show']);

// Reviews của Barber (ai cũng xem được)
// Route::get('/barbers/{id}/reviews', [App\Http\Controllers\Api\ReviewApiController::class, 'index']);

// Chatbot AI - Hỏi đáp (ai cũng hỏi được)
// Route::post('/chatbot/ask', [App\Http\Controllers\Api\ChatbotController::class, 'ask']);

// VNPAY Callback (cổng thanh toán gọi về, không cần auth)
// Route::post('/vnpay/callback', [App\Http\Controllers\Api\VnpayController::class, 'callback']);
// Route::post('/vnpay/ipn', [App\Http\Controllers\Api\VnpayController::class, 'ipn']);

// ========================================================================
// PROTECTED API (Yêu cầu đăng nhập qua Sanctum)
// ========================================================================
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

    // ====================================================================
    // API MỚI - Booking nâng cao (Member 3)
    // ====================================================================

    // Xem khung giờ trống của Barber theo ngày
    // Route::get('/barbers/{id}/slots', [AppointmentApiController::class, 'slots']);

    // Tạo URL thanh toán VNPAY
    // Route::post('/vnpay/create-payment', [App\Http\Controllers\Api\VnpayController::class, 'createPayment']);

    // ====================================================================
    // API MỚI - Đánh giá & Loyalty (Member 2)
    // ====================================================================

    // Gửi đánh giá mới
    // Route::post('/reviews', [App\Http\Controllers\Api\ReviewApiController::class, 'store']);

    // Kiểm tra điểm thưởng & hạng thành viên
    // Route::get('/loyalty', [App\Http\Controllers\Api\LoyaltyApiController::class, 'show']);

    // ====================================================================
    // API MỚI - Thống kê (Member 5)
    // ====================================================================

    // Doanh thu theo ngày/tuần/tháng
    // Route::get('/statistics/revenue', [App\Http\Controllers\Api\StatisticApiController::class, 'revenue']);

    // Khung giờ cao điểm trong tuần
    // Route::get('/statistics/peak-hours', [App\Http\Controllers\Api\StatisticApiController::class, 'peakHours']);

    // Dịch vụ được ưa chuộng nhất
    // Route::get('/statistics/services', [App\Http\Controllers\Api\StatisticApiController::class, 'popularServices']);
});