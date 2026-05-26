<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard tổng quan
     */
    public function index()
    {
        // 1. LOGIC TỰ ĐỘNG HỦY LỊCH HẸN QUÁ HẠN
        // Lấy thời gian hiện tại (set cứng múi giờ Việt Nam để so sánh chính xác)
        $now = Carbon::now('Asia/Ho_Chi_Minh'); 
        
        Appointment::where('status', 'pending')
            ->where(function ($query) use ($now) {
                // Trường hợp 1: Ngày hẹn bé hơn ngày hôm nay
                $query->where('appointment_date', '<', $now->toDateString())
                      // Trường hợp 2: Cùng ngày hôm nay nhưng giờ hẹn bé hơn giờ hiện tại
                      ->orWhere(function ($q) use ($now) {
                          $q->where('appointment_date', '=', $now->toDateString())
                            ->where('appointment_time', '<', $now->format('H:i'));
                      });
            })
            ->update(['status' => 'cancelled']); // Đổi trạng thái thành Đã hủy

        // 2. LẤY DỮ LIỆU HIỂN THỊ LÊN DASHBOARD
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $totalBarbers = Barber::count();
        $totalServices = Service::count();

        $recentAppointments = Appointment::with(['user', 'barber', 'service'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'totalBarbers',
            'totalServices',
            'recentAppointments'
        ));
    }
}