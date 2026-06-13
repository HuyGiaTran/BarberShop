<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticApiController extends Controller
{
    /**
     * Thống kê doanh thu theo ngày (7 ngày gần nhất)
     */
    public function revenue(): JsonResponse
    {
        $revenue = Invoice::where('payment_status', 'paid')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê doanh thu thành công',
            'data' => $revenue
        ], 200);
    }

    /**
     * Thống kê khung giờ có nhiều lịch nhất (top 5)
     */
    public function peakHours(): JsonResponse
    {
        $peakHours = Appointment::select(
                'appointment_time',
                DB::raw('count(*) as total_bookings')
            )
            ->where('status', '!=', 'cancelled')
            ->groupBy('appointment_time')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê giờ cao điểm thành công',
            'data' => $peakHours
        ], 200);
    }

    /**
     * Thống kê các dịch vụ được sử dụng nhiều nhất (top 5)
     */
    public function popularServices(): JsonResponse
    {
        $services = Appointment::select(
                'service_id',
                DB::raw('count(*) as total_bookings')
            )
            ->with('service:id,name,price')
            ->where('status', '!=', 'cancelled')
            ->groupBy('service_id')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get();

        $data = $services->map(function ($item) {
            return [
                'service_name' => $item->service ? $item->service->name : 'N/A',
                'total_bookings' => $item->total_bookings,
                'price' => $item->service ? $item->service->price : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê dịch vụ phổ biến thành công',
            'data' => $data
        ], 200);
    }
}
