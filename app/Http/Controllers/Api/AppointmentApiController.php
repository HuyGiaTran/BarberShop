<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AppointmentApiController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService
    ) {
    }

    /**
     * GET /api/appointments - Danh sách lịch hẹn (có filter theo status)
     */
    public function index(Request $request): JsonResponse
{
    $query = Appointment::with(['user', 'barber', 'service']);

    // Filter theo trạng thái
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter theo ngày
    if ($request->filled('date')) {
        $query->whereDate('appointment_date', $request->date);
    }

    // Filter theo barber
    if ($request->filled('barber_id')) {
        $query->where('barber_id', $request->barber_id);
    }

    // Filter theo user
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    $appointments = $query
        ->orderBy('appointment_date', 'asc')
        ->orderBy('appointment_time', 'asc')
        ->get()
        ->groupBy('user_id');

    $data = $appointments->map(function ($items) {
        $user = $items->first()->user;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],

            'appointments' => $items->map(function ($a) {
                return [
                    'id' => $a->id,
                    'date' => $a->appointment_date,
                    'time' => $a->appointment_time,
                    'status' => $a->status,
                    'notes' => $a->notes,

                    'barber' => [
                        'id' => $a->barber->id,
                        'name' => $a->barber->name,
                        'phone' => $a->barber->phone,
                    ],

                    'service' => [
                        'id' => $a->service->id,
                        'name' => $a->service->name,
                        'price' => $a->service->price,
                    ],
                ];
            })->values(),
        ];
    })->values();

    return response()->json([
    'success' => true,
    'message' => 'Danh sách lịch hẹn theo từng người dùng',
    'data' => $data,
    'total' => $appointments->flatten()->count(),
], 200, [], JSON_PRETTY_PRINT);
}
    /**
     * POST /api/appointments - Đặt lịch hẹn mới (validate trùng giờ)
     */
    public function store(Request $request): JsonResponse
{
    // Validate dữ liệu
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'barber_id' => 'required|exists:barbers,id',
        'service_id' => 'required|exists:services,id',
        'appointment_date' => 'required|date|after_or_equal:today',
        'appointment_time' => 'required|string',
        'status' => 'required|in:pending,confirmed,completed,cancelled',
        'notes' => 'nullable|string',
    ], [
        'user_id.required' => 'Vui lòng chọn khách hàng.',
        'user_id.exists' => 'Khách hàng không tồn tại.',
        'barber_id.required' => 'Vui lòng chọn thợ cắt tóc.',
        'barber_id.exists' => 'Thợ cắt tóc không tồn tại.',
        'service_id.required' => 'Vui lòng chọn dịch vụ.',
        'service_id.exists' => 'Dịch vụ không tồn tại.',
        'appointment_date.required' => 'Vui lòng chọn ngày hẹn.',
        'appointment_date.after_or_equal' => 'Ngày hẹn không được nhỏ hơn ngày hôm nay.',
        'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',
        'status.required' => 'Vui lòng chọn trạng thái.',
        'status.in' => 'Trạng thái không hợp lệ.',
    ]);

    $service = Service::findOrFail($validated['service_id']);

    // Logic chặn hoàn thành lịch hẹn trong tương lai
    if ($request->status === 'completed' && Carbon::parse($request->appointment_date)->isFuture()) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể hoàn thành lịch hẹn trong tương lai.',
            'error' => 'completed_future_appointment',
        ], 422, [], JSON_UNESCAPED_UNICODE);
    }

    // Kiểm tra xem thợ cắt tóc đã có lịch trùng giờ chưa
    if ($this->appointmentService->isBarberOnApprovedLeave(
        (int) $validated['barber_id'],
        $validated['appointment_date']
    )) {
        return response()->json([
            'success' => false,
            'message' => 'Thợ cắt tóc đang nghỉ phép trong ngày bạn chọn. Vui lòng chọn ngày khác.',
            'error' => 'barber_on_leave',
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    $isOverlapping = $this->appointmentService->hasConflict(
        (int) $validated['barber_id'],
        $validated['appointment_date'],
        $validated['appointment_time'],
        (int) $service->duration_minutes
    );

    if ($isOverlapping) {
        return response()->json([
            'success' => false,
            'message' => 'Thợ cắt tóc đã có lịch hẹn bị chồng chéo trong khoảng thời gian này. Vui lòng chọn giờ hoặc thợ khác.',
            'error' => 'appointment_time_conflict',
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    // Tạo lịch hẹn mới
    $appointment = Appointment::create($validated);
    $appointment->load(['user', 'barber', 'service']);

    // Gửi email thông báo
    $appointment->user->notify(new \App\Notifications\AppointmentBooked($appointment));

    return response()->json([
        'success' => true,
        'message' => 'Đặt lịch hẹn thành công!',
        'data' => [
            'id' => $appointment->id,
            'date' => $appointment->appointment_date,
            'time' => $appointment->appointment_time,
            'status' => $appointment->status,
            'notes' => $appointment->notes,

            'user' => [
                'id' => $appointment->user->id,
                'name' => $appointment->user->name,
            ],

            'barber' => [
                'id' => $appointment->barber->id,
                'name' => $appointment->barber->name,
            ],

            'service' => [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'price' => $appointment->service->price,
            ],
        ],
    ], 201, [], JSON_UNESCAPED_UNICODE);
}
    /**
     * GET /api/appointments/{id} - Chi tiết lịch hẹn
     */
    public function show(string $id): JsonResponse
    {
        $appointment = Appointment::with(['user', 'barber', 'service'])->find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch hẹn.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết lịch hẹn',
            'data' => $appointment,
        ], 200);
    }

    /**
     * GET /api/barbers/{id}/slots - Danh sách khung giờ trống
     */
    public function slots(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $available = $this->appointmentService->availableSlotsForBarber((int) $id, $request->date);

        return response()->json([
            'success' => true,
            'data' => $available,
            'meta' => [
                'is_on_leave' => $this->appointmentService->isBarberOnApprovedLeave((int) $id, $request->date),
            ],
        ]);
    }

    /**
     * PUT /api/appointments/{id} - Cập nhật lịch hẹn
     */
    public function update(Request $request, string $id): JsonResponse
{
    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy lịch hẹn.',
        ], 404);
    }

    // VALIDATE
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'barber_id' => 'required|exists:barbers,id',
        'service_id' => 'required|exists:services,id',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required|string',
        'status' => 'required|in:pending,confirmed,completed,cancelled',
        'notes' => 'nullable|string',
    ], [
        'user_id.required' => 'Vui lòng chọn khách hàng.',
        'user_id.exists' => 'Khách hàng không tồn tại.',

        'barber_id.required' => 'Vui lòng chọn thợ cắt tóc.',
        'barber_id.exists' => 'Thợ cắt tóc không tồn tại.',

        'service_id.required' => 'Vui lòng chọn dịch vụ.',
        'service_id.exists' => 'Dịch vụ không tồn tại.',

        'appointment_date.required' => 'Vui lòng chọn ngày hẹn.',
        'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',

        'status.required' => 'Vui lòng chọn trạng thái.',
        'status.in' => 'Trạng thái không hợp lệ.',
    ]);

    $service = Service::findOrFail($validated['service_id']);

    // ❌ Không cho hoàn thành lịch hẹn trong tương lai
    if (
        $validated['status'] === 'completed'
        && Carbon::parse($validated['appointment_date'])->isFuture()
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể hoàn thành lịch hẹn trong tương lai.',
            'error' => 'completed_future_appointment',
        ], 422);
    }

    // ❌ Kiểm tra trùng lịch (loại trừ chính appointment hiện tại)
    if ($this->appointmentService->isBarberOnApprovedLeave(
        (int) $validated['barber_id'],
        $validated['appointment_date']
    )) {
        return response()->json([
            'success' => false,
            'message' => 'Thợ cắt tóc đang nghỉ phép trong ngày bạn chọn. Vui lòng chọn ngày khác.',
            'error' => 'barber_on_leave',
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    $isOverlapping = $this->appointmentService->hasConflict(
        (int) $validated['barber_id'],
        $validated['appointment_date'],
        $validated['appointment_time'],
        (int) $service->duration_minutes,
        $appointment->id
    );

    if ($isOverlapping) {
        return response()->json([
            'success' => false,
            'message' => 'Khung giờ này đang bị chồng chéo với lịch hẹn khác. Vui lòng chọn thời gian khác.',
            'error' => 'appointment_time_conflict',
        ], 409);
    }

    // ✔ CẬP NHẬT
    $oldStatus = $appointment->status;
    $appointment->update($validated);
    $appointment->load(['user', 'barber', 'service']);

    if ($oldStatus !== 'cancelled' && $appointment->status === 'cancelled') {
        $appointment->user->notify(new \App\Notifications\AppointmentCancelled($appointment));
    }

    // ✔ RESPONSE CLEAN
    return response()->json([
        'success' => true,
        'message' => 'Cập nhật lịch hẹn thành công!',
        'data' => [
            'id' => $appointment->id,
            'date' => $appointment->appointment_date,
            'time' => $appointment->appointment_time,
            'status' => $appointment->status,
            'notes' => $appointment->notes,

            'user' => [
                'id' => $appointment->user->id,
                'name' => $appointment->user->name,
            ],

            'barber' => [
                'id' => $appointment->barber->id,
                'name' => $appointment->barber->name,
            ],

            'service' => [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'price' => $appointment->service->price,
            ],
        ],
    ], 200);
}
    /**
     * DELETE /api/appointments/{id} - Xóa lịch hẹn
     */
    public function destroy(string $id): JsonResponse
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch hẹn.',
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa lịch hẹn thành công!',
        ], 200);
    }
}
