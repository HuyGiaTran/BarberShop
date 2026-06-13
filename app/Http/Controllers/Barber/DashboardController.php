<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $barber = Barber::where('user_id', Auth::id())->first();

        if (!$barber) {
            return view('barber.dashboard', compact('barber'));
        }

        // Lịch hẹn hôm nay
        $todayAppointments = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('appointment_time')
            ->get();

        $todayCount = $todayAppointments->count();
        $pendingCount = $todayAppointments->where('status', 'pending')->count();
        $completedToday = $todayAppointments->where('status', 'completed')->count();
        $completedCount = $completedToday;

        // Doanh thu tháng này
        $totalRevenue = Appointment::where('barber_id', $barber->id)
            ->where('status', 'completed')
            ->whereMonth('appointment_date', Carbon::now()->month)
            ->whereYear('appointment_date', Carbon::now()->year)
            ->with('service')
            ->get()
            ->sum(function ($apt) {
                return $apt->service ? $apt->service->price : 0;
            });

        // Lịch sắp tới (sau hôm nay)
        $upcomingAppointments = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', '>', Carbon::today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        // Thống kê
        $totalCompleted = Appointment::where('barber_id', $barber->id)
            ->where('status', 'completed')
            ->count();

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weekCount = Appointment::where('barber_id', $barber->id)
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])
            ->count();

        // Dịch vụ của barber
        $services = $barber->services;

        return view('barber.dashboard', compact(
            'barber',
            'todayAppointments',
            'todayCount',
            'pendingCount',
            'completedToday',
            'completedCount',
            'totalRevenue',
            'upcomingAppointments',
            'totalCompleted',
            'weekCount',
            'services',
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

    public function updateWorkingStatus(Request $request): RedirectResponse
    {
        $barber = Barber::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'working_status' => 'required|in:active,busy,off',
        ]);

        $barber->update(['working_status' => $validated['working_status']]);

        return back()->with('success', 'Đã cập nhật trạng thái hoạt động.');
    }
}