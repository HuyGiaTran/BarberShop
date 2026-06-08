<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BarberDashboardController extends Controller
{
    /**
     * Lấy Barber profile dựa trên user đang đăng nhập
     */
    private function getBarber()
    {
        return Barber::where('user_id', Auth::id())->first();
    }

    /**
     * Trang Dashboard chính của Barber
     */
    public function index()
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return view('barber.dashboard', [
                'barber' => null,
                'todayAppointments' => collect(),
                'upcomingAppointments' => collect(),
                'todayCount' => 0,
                'weekCount' => 0,
                'completedToday' => 0,
                'pendingCount' => 0,
                'totalCompleted' => 0,
                'totalRevenue' => 0,
                'services' => collect(),
            ]);
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $now->toDateString();
        $weekEnd = $now->copy()->endOfWeek()->toDateString();

        // Tự động hủy lịch hẹn quá hạn của barber này
        Appointment::where('barber_id', $barber->id)
            ->where('status', 'pending')
            ->where(function ($query) use ($now) {
                $query->where('appointment_date', '<', $now->toDateString())
                      ->orWhere(function ($q) use ($now) {
                          $q->where('appointment_date', '=', $now->toDateString())
                            ->where('appointment_time', '<', $now->format('H:i'));
                      });
            })
            ->update(['status' => 'cancelled']);

        // Lịch hẹn hôm nay
        $todayAppointments = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Lịch hẹn sắp tới (7 ngày tới, không tính hôm nay)
        $upcomingAppointments = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', '>', $today)
            ->whereDate('appointment_date', '<=', $weekEnd)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(10)
            ->get();

        // Thống kê
        $todayCount = $todayAppointments->count();
        $weekCount = Appointment::where('barber_id', $barber->id)
            ->whereDate('appointment_date', '>=', $today)
            ->whereDate('appointment_date', '<=', $weekEnd)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        $completedToday = $todayAppointments->where('status', 'completed')->count();
        $pendingCount = $todayAppointments->whereIn('status', ['pending', 'confirmed'])->count();

        // Tổng đã hoàn thành (tất cả thời gian)
        $totalCompleted = Appointment::where('barber_id', $barber->id)
            ->where('status', 'completed')
            ->count();

        // Doanh thu tháng này (tính từ dịch vụ đã hoàn thành)
        $totalRevenue = Appointment::where('appointments.barber_id', $barber->id)
            ->where('appointments.status', 'completed')
            ->whereMonth('appointments.appointment_date', $now->month)
            ->whereYear('appointments.appointment_date', $now->year)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        // Dịch vụ của barber
        $services = Service::where('barber_id', $barber->id)->get();

        return view('barber.dashboard', compact(
            'barber',
            'todayAppointments',
            'upcomingAppointments',
            'todayCount',
            'weekCount',
            'completedToday',
            'pendingCount',
            'totalCompleted',
            'totalRevenue',
            'services'
        ));
    }

    /**
     * Danh sách tất cả lịch hẹn của Barber (có lọc)
     */
    public function appointments(Request $request)
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        $query = Appointment::with(['user', 'service'])
            ->where('barber_id', $barber->id);

        // Lọc theo ngày
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo tuần/tháng
        if ($request->filled('period')) {
            $now = Carbon::now('Asia/Ho_Chi_Minh');
            switch ($request->period) {
                case 'today':
                    $query->whereDate('appointment_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereDate('appointment_date', '>=', $now->startOfWeek()->toDateString())
                          ->whereDate('appointment_date', '<=', $now->endOfWeek()->toDateString());
                    break;
                case 'month':
                    $query->whereMonth('appointment_date', $now->month)
                          ->whereYear('appointment_date', $now->year);
                    break;
            }
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                              ->orderBy('appointment_time', 'asc')
                              ->paginate(15);

        return view('barber.appointments', compact('barber', 'appointments'));
    }

    /**
     * Cập nhật nhanh trạng thái lịch hẹn (Xác nhận / Hoàn thành / Hủy)
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        $barber = $this->getBarber();

        if (!$barber || $appointment->barber_id !== $barber->id) {
            abort(403, 'Bạn không có quyền thao tác trên lịch hẹn này.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,completed,cancelled',
        ]);

        // Chặn hoàn thành lịch hẹn trong tương lai
        if ($request->status === 'completed' && $appointment->appointment_date->isFuture()) {
            return back()->with('error', 'Không thể hoàn thành lịch hẹn trong tương lai!');
        }

        $appointment->update(['status' => $validated['status']]);

        $statusLabels = [
            'confirmed' => 'xác nhận',
            'completed' => 'hoàn thành',
            'cancelled' => 'hủy',
        ];

        return back()->with('success', 'Đã ' . ($statusLabels[$validated['status']] ?? 'cập nhật') . ' lịch hẹn thành công!');
    }

    /**
     * Trang hồ sơ cá nhân của Barber
     */
    public function profile()
    {
        $barber = $this->getBarber();
        $totalCompleted = 0;
        $totalRevenue = 0;
        $services = collect();

        if ($barber) {
            $totalCompleted = Appointment::where('barber_id', $barber->id)
                ->where('status', 'completed')
                ->count();
            $totalRevenue = Appointment::where('appointments.barber_id', $barber->id)
                ->where('appointments.status', 'completed')
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->sum('services.price');
            $services = Service::where('barber_id', $barber->id)->get();
        }

        return view('barber.profile', compact('barber', 'totalCompleted', 'totalRevenue', 'services'));
    }

    /**
     * Cập nhật hồ sơ cá nhân
     */
    public function updateProfile(Request $request)
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return back()->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ]);

        $barber->update($validated);

        // Cập nhật tên User nếu có
        if ($request->filled('name')) {
            Auth::user()->update(['name' => $request->name]);
            $barber->update(['name' => $request->name]);
        }

        return back()->with('success', 'Cập nhật hồ sơ thành công!');
    }
}
