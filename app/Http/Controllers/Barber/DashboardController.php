<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $barber = Barber::where('user_id', Auth::id())->firstOrFail();

        $todayAppointments = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('appointment_time')
            ->get();

        $todayCount = $todayAppointments->count();
        $completedCount = $todayAppointments->where('status', 'completed')->count();

        return view('barber.dashboard', compact(
            'barber',
            'todayAppointments',
            'todayCount',
            'completedCount',
        ));
    }

    public function toggleStatus(): RedirectResponse
    {
        $barber = Barber::where('user_id', Auth::id())->firstOrFail();

        $barber->update([
            'is_active' => ! $barber->is_active,
        ]);

        return back()->with(
            'success',
            $barber->is_active
                ? 'Đã bật trạng thái sẵn sàng nhận lịch.'
                : 'Đã chuyển sang chế độ bận.'
        );
    }
}
