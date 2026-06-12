<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
    if (!$request->has('service_ids') && $request->filled('service_id')) {
        $request->merge([
            'service_ids' => [(int) $request->input('service_id')],
        ]);
    }

    // Validate dữ liệu
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'barber_id' => 'required|exists:barbers,id',
        'service_ids' => 'required|array|min:1',
        'service_ids.*' => 'exists:services,id',
        'appointment_date' => 'required|date|after_or_equal:today',
        'appointment_time' => 'required|string',
        'status' => 'required|in:pending,confirmed,completed,cancelled',
        'notes' => 'nullable|string',
        'promo_code' => 'nullable|string',
    ], [
        'user_id.required' => 'Vui lòng chọn khách hàng.',
        'user_id.exists' => 'Khách hàng không tồn tại.',
        'barber_id.required' => 'Vui lòng chọn thợ cắt tóc.',
        'barber_id.exists' => 'Thợ cắt tóc không tồn tại.',
        'service_ids.required' => 'Vui lòng chọn ít nhất một dịch vụ.',
        'service_ids.array' => 'Dịch vụ không hợp lệ.',
        'service_ids.*.exists' => 'Dịch vụ không tồn tại.',
        'appointment_date.required' => 'Vui lòng chọn ngày hẹn.',
        'appointment_date.after_or_equal' => 'Ngày hẹn không được nhỏ hơn ngày hôm nay.',
        'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',
        'status.required' => 'Vui lòng chọn trạng thái.',
        'status.in' => 'Trạng thái không hợp lệ.',
    ]);

    $services = Service::whereIn('id', $validated['service_ids'])->get();
    $totalDuration = $services->sum('duration_minutes');
    $selectedTime = substr((string) $validated['appointment_time'], 0, 5);

    // Logic chặn hoàn thành lịch hẹn trong tương lai
    if ($request->status === 'completed' && Carbon::parse($request->appointment_date)->isFuture()) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể hoàn thành lịch hẹn trong tương lai.',
            'error' => 'completed_future_appointment',
        ], 422, [], JSON_UNESCAPED_UNICODE);
    }

    $availability = $this->appointmentService->getBookingAvailability(
        (int) $validated['barber_id'],
        $validated['appointment_date']
    );

    if (! $availability['bookable']) {
        return response()->json([
            'success' => false,
            'message' => $this->bookingAvailabilityMessage((string) $availability['reason']),
            'error' => $availability['reason'],
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    $availableSlots = $this->appointmentService->availableSlotsForDuration(
        (int) $validated['barber_id'],
        $validated['appointment_date'],
        (int) $totalDuration
    );

    if (! in_array($selectedTime, $availableSlots, true)) {
        $isOverlapping = $this->appointmentService->hasConflict(
            (int) $validated['barber_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            (int) $totalDuration
        );

        return response()->json([
            'success' => false,
            'message' => $isOverlapping
                ? 'Thợ cắt tóc đã có lịch hẹn bị chồng chéo trong khoảng thời gian này. Vui lòng chọn giờ hoặc thợ khác.'
                : 'Barber không làm việc hoặc không còn đủ thời lượng trống cho khung giờ bạn chọn.',
            'error' => $isOverlapping ? 'appointment_time_conflict' : 'barber_unavailable_slot',
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    $currentTime = Carbon::createFromFormat('H:i', substr($validated['appointment_time'], 0, 5));
    $createdAppointments = [];
    $firstAppointment = null;
    $bookingReference = $this->generateBookingReference();

    $promoCode = isset($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null;
    $promoCodeError = null;

    if ($promoCode === 'BARBERVIP') {
        if (!Carbon::parse($validated['appointment_date'])->isWeekend()) {
            $promoCodeError = 'Mã BARBERVIP chỉ áp dụng vào Thứ 7 và Chủ Nhật.';
        } else {
            $user = User::find($validated['user_id']);
            $loyaltyService = app(LoyaltyService::class);
            $tier = $loyaltyService->summaryForUser($user)['tier'];
            if (!in_array($tier, ['silver', 'gold', 'platinum'])) {
                $promoCodeError = 'Mã BARBERVIP chỉ dành cho hạng thành viên Silver trở lên.';
            }
        }
    } elseif ($promoCode && $promoCode !== 'REVIEW5K') {
        $promoCodeError = 'Mã giảm giá không hợp lệ.';
    }

    if ($promoCodeError) {
        return response()->json([
            'success' => false,
            'message' => $promoCodeError,
            'error' => 'invalid_promo_code',
        ], 400, [], JSON_UNESCAPED_UNICODE);
    }

    foreach ($validated['service_ids'] as $index => $serviceId) {
        $service = $services->firstWhere('id', $serviceId);
        if (!$service) continue;

        $discountAmount = 0;
        if ($promoCode === 'REVIEW5K' && $index === 0) {
            $discountAmount = 5000;
        } elseif ($promoCode === 'BARBERVIP') {
            $discountAmount = $service->price * 0.10;
        }

        $appointment = Appointment::create([
            'user_id' => $validated['user_id'],
            'barber_id' => $validated['barber_id'],
            'service_id' => $service->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => $index + 1,
            'is_booking_primary' => $index === 0,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $currentTime->format('H:i'),
            'status' => $validated['status'],
            'notes' => count($validated['service_ids']) > 1 ? ($validated['notes'] . ' (Gộp nhiều dịch vụ)') : $validated['notes'],
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount,
            'deposit_amount' => $index === 0 ? 50000 : 0,
            'deposit_status' => 'unpaid',
        ]);
        
        $appointment->load(['user', 'barber', 'service']);
        $createdAppointments[] = $appointment;
        if (!$firstAppointment) $firstAppointment = $appointment;

        $currentTime->addMinutes($service->duration_minutes);
    }

    if (count($createdAppointments) > 0) {
        // Gửi email thông báo cho lịch hẹn đầu tiên đại diện
        $firstAppointment->user->notify(new \App\Notifications\AppointmentBooked($firstAppointment));
    }

    return response()->json([
        'success' => true,
        'message' => 'Đặt lịch hẹn thành công!',
        'data' => [
            'booking_reference' => $bookingReference,
            'total_appointments_created' => count($createdAppointments),
            'first_appointment_id' => $firstAppointment ? $firstAppointment->id : null,
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
            'duration_minutes' => 'nullable|integer|min:1|max:480',
        ]);

        $durationMinutes = (int) $request->integer('duration_minutes', 30);
        $availability = $this->appointmentService->getBookingAvailability((int) $id, $request->date);
        $available = $availability['bookable']
            ? $this->appointmentService->availableSlotsForDuration((int) $id, $request->date, $durationMinutes)
            : [];

        return response()->json([
            'success' => true,
            'data' => $available,
            'meta' => [
                'is_on_leave' => $this->appointmentService->isBarberOnApprovedLeave((int) $id, $request->date),
                'availability_reason' => $availability['reason'],
                'working_status' => $availability['barber']?->working_status ?? null,
                'is_active' => $availability['barber']?->is_active ?? false,
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
    $selectedTime = substr((string) $validated['appointment_time'], 0, 5);

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

    $availability = $this->appointmentService->getBookingAvailability(
        (int) $validated['barber_id'],
        $validated['appointment_date']
    );

    if (! $availability['bookable']) {
        return response()->json([
            'success' => false,
            'message' => $this->bookingAvailabilityMessage((string) $availability['reason']),
            'error' => $availability['reason'],
        ], 409, [], JSON_UNESCAPED_UNICODE);
    }

    $availableSlots = $this->appointmentService->availableSlotsForDuration(
        (int) $validated['barber_id'],
        $validated['appointment_date'],
        (int) $service->duration_minutes,
        $appointment->id
    );

    if (! in_array($selectedTime, $availableSlots, true)) {
        $isOverlapping = $this->appointmentService->hasConflict(
            (int) $validated['barber_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            (int) $service->duration_minutes,
            $appointment->id
        );

        return response()->json([
            'success' => false,
            'message' => $isOverlapping
                ? 'Khung giờ này đang bị chồng chéo với lịch hẹn khác. Vui lòng chọn thời gian khác.'
                : 'Barber không làm việc hoặc không còn đủ thời lượng trống cho khung giờ bạn chọn.',
            'error' => $isOverlapping ? 'appointment_time_conflict' : 'barber_unavailable_slot',
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

    private function bookingAvailabilityMessage(string $reason): string
    {
        return match ($reason) {
            'barber_inactive' => 'Barber này hiện đang ngưng nhận khách.',
            'barber_busy' => 'Barber này đang bận và tạm thời không nhận lịch mới.',
            'barber_off' => 'Barber này hiện không làm việc.',
            'barber_on_leave' => 'Thợ cắt tóc đang nghỉ phép trong ngày bạn chọn. Vui lòng chọn ngày khác.',
            'no_schedule' => 'Barber này chưa mở lịch làm việc cho ngày bạn chọn.',
            'blocked_schedule' => 'Barber này không làm việc trong khung ngày đã chọn.',
            default => 'Barber hiện không sẵn sàng để nhận lịch vào thời điểm này.',
        };
    }

    private function generateBookingReference(): string
    {
        return 'BKG-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }
}
