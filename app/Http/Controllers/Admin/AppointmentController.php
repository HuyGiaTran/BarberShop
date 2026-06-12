<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService
    ) {
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['user', 'barber', 'service']);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $users = User::where('role', 'customer')->get();
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();

        return view('appointments.create', compact('users', 'barbers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ], [
            'user_id.required' => 'Vui long chon khach hang.',
            'barber_id.required' => 'Vui long chon barber.',
            'service_id.required' => 'Vui long chon dich vu.',
            'appointment_date.required' => 'Vui long chon ngay hen.',
            'appointment_date.after_or_equal' => 'Ngay hen khong duoc nho hon ngay hom nay.',
            'appointment_time.required' => 'Vui long chon khung gio hen.',
            'appointment_time.date_format' => 'Khung gio hen khong hop le.',
        ]);

        $validated['appointment_time'] = $this->normalizeAppointmentTime($validated['appointment_time']);
        $service = Service::findOrFail($validated['service_id']);

        if ($validated['status'] === 'completed'
            && $this->isAppointmentInFuture($validated['appointment_date'], $validated['appointment_time'])) {
            return back()->withInput()->withErrors([
                'status' => 'Khong the hoan thanh lich hen trong tuong lai.',
            ]);
        }

        // Kiểm tra barber có đang nghỉ phép không
        if ($this->appointmentService->isBarberOnApprovedLeave(
            (int) $validated['barber_id'],
            $validated['appointment_date']
        )) {
            return back()->withInput()->withErrors([
                'appointment_date' => 'Barber nay dang nghi phep trong ngay duoc chon.',
            ]);
        }

        // Kiểm tra trùng lịch
        $isOverlapping = $this->appointmentService->hasConflict(
            (int) $validated['barber_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            (int) $service->duration_minutes
        );

        if ($isOverlapping) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Barber nay da co lich hen bi chong cheo ve thoi gian. Vui long chon gio hoac barber khac.',
            ]);
        }

        $appointment = Appointment::create($validated);

        $appointment->load(['user', 'barber', 'service']);
        $appointment->user->notify(new \App\Notifications\AppointmentBooked($appointment));

        return redirect()->route('admin.appointments.index')->with('success', 'Tao lich hen moi thanh cong!');
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $users = User::where('role', 'customer')->get();
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();

        return view('appointments.edit', compact('appointment', 'users', 'barbers', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ], [
            'user_id.required' => 'Vui long chon khach hang.',
            'barber_id.required' => 'Vui long chon barber.',
            'service_id.required' => 'Vui long chon dich vu.',
            'appointment_date.required' => 'Vui long chon ngay hen.',
            'appointment_time.required' => 'Vui long chon khung gio hen.',
            'appointment_time.date_format' => 'Khung gio hen khong hop le.',
        ]);

        $validated['appointment_time'] = $this->normalizeAppointmentTime($validated['appointment_time']);
        $service = Service::findOrFail($validated['service_id']);

        if ($validated['status'] === 'completed'
            && $this->isAppointmentInFuture($validated['appointment_date'], $validated['appointment_time'])) {
            return back()->withInput()->withErrors([
                'status' => 'Khong the hoan thanh lich hen trong tuong lai.',
            ]);
        }

        // Chỉ kiểm tra nghỉ phép nếu barber, ngày hoặc giờ thay đổi
        if ($validated['barber_id'] != $appointment->barber_id ||
            $validated['appointment_date'] != $appointment->appointment_date->format('Y-m-d') ||
            $validated['appointment_time'] != $appointment->appointment_time) {

            if ($this->appointmentService->isBarberOnApprovedLeave(
                (int) $validated['barber_id'],
                $validated['appointment_date']
            )) {
                return back()->withInput()->withErrors([
                    'appointment_date' => 'Barber nay dang nghi phep trong ngay duoc chon.',
                ]);
            }
        }

        // Kiểm tra trùng lịch
        $isOverlapping = $this->appointmentService->hasConflict(
            (int) $validated['barber_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            (int) $service->duration_minutes,
            $appointment->id
        );

        if ($isOverlapping) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Barber nay da co lich hen bi chong cheo ve thoi gian. Vui long chon gio khac.',
            ]);
        }

        $oldStatus = $appointment->status;
        $appointment->update($validated);

        if ($oldStatus !== 'cancelled' && $appointment->status === 'cancelled') {
            $appointment->load(['user', 'barber', 'service']);
            $appointment->user->notify(new \App\Notifications\AppointmentCancelled($appointment));
        }

        return redirect()->route('admin.appointments.index')->with('success', 'Cap nhat lich hen thanh cong!');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('admin.appointments.index')->with('success', 'Xoa lich hen thanh cong!');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
        ]);

        if ($validated['status'] === 'completed' && $this->isAppointmentInFuture(
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->appointment_time
        )) {
            return back()->with('error', 'Khong the hoan thanh lich hen trong tuong lai!');
        }

        $oldStatus = $appointment->status;
        $appointment->update(['status' => $validated['status']]);

        if ($oldStatus !== 'cancelled' && $appointment->status === 'cancelled') {
            $appointment->load(['user', 'barber', 'service']);
            $appointment->user->notify(new \App\Notifications\AppointmentCancelled($appointment));
        }

        return redirect()->route('admin.appointments.index')->with('success', 'Cap nhat trang thai lich hen thanh cong!');
    }

    private function normalizeAppointmentTime(string $time): string
    {
        return strlen($time) === 5 ? "{$time}:00" : $time;
    }

    private function isAppointmentInFuture(string $date, string $time): bool
    {
        return Carbon::parse("{$date} {$time}", config('app.timezone'))->isFuture();
    }
}