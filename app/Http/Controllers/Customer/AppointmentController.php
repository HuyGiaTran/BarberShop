<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['barber', 'service'])
            ->where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(10);

        return view('customer.my-schedules', compact('appointments'));
    }

    public function show(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem lịch hẹn này.');
        }

        $appointment->load(['barber', 'service']);
        return view('customer.my-schedule-detail', compact('appointment'));
    }

    public function deposit(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }

        $depositAmount = 50000;
        $appointment->load(['barber', 'service']);

        return view('customer.my-schedule-detail', compact('appointment', 'depositAmount'));
    }

    public function processDeposit(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }

        $appointment->update(['status' => 'confirmed']);

        return redirect()->route('customer.appointments.show', $appointment)
            ->with('success', '✅ Đặt cọc 50.000đ thành công! Lịch hẹn đã được xác nhận.');
    }
}