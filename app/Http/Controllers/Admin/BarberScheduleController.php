<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\BarberSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BarberScheduleController extends Controller
{
    public function index(Request $request)
    {
        $barbers = Barber::with('schedules')->get();
        
        // Xác định tuần hiển thị
        $weekOffset = (int) $request->input('week', 0);
        $weekStart = Carbon::now()->startOfWeek()->addWeeks($weekOffset);
        $weekEnd = $weekStart->copy()->addDays(6);
        
        // Tạo mảng 7 ngày trong tuần
        $days = [];
        $dayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'label' => $dayNames[$i],
                'day_of_week' => $i,
                'date_str' => $date->format('Y-m-d'),
            ];
        }
        
        // Lấy schedules cho từng barber trong tuần này
        $schedulesByBarber = [];
        $slotNames = ['morning', 'afternoon', 'evening'];
        $slotTimes = ['morning' => '08:00-13:00', 'afternoon' => '13:00-18:00', 'evening' => '18:00-22:00'];
        
        foreach ($barbers as $barber) {
            $barberData = [];
            foreach ($days as $day) {
                // Lấy tất cả schedules của barber trong ngày này (không filter start_time)
                $daySchedules = BarberSchedule::where('barber_id', $barber->id)
                    ->where(function ($q) use ($day) {
                        $q->where('specific_date', $day['date_str'])
                          ->orWhere(function ($q2) use ($day) {
                              $q2->whereNull('specific_date')
                                 ->where('day_of_week', $day['day_of_week']);
                          });
                    })
                    ->get();
                
                // Chuyển thành mảng các slot bị block
                $blocked = [];
                foreach ($daySchedules as $sched) {
                    if (!$sched->is_available || $sched->is_off) {
                        $blocked[] = [
                            'status' => 'blocked',
                            'start' => \Carbon\Carbon::parse($sched->start_time)->format('H:i'),
                            'end' => \Carbon\Carbon::parse($sched->end_time)->format('H:i'),
                            'reason' => $sched->reason ?? 'Nghỉ phép',
                        ];
                    }
                }
                $barberData[$day['day_of_week']] = $blocked;
            }
            $schedulesByBarber[$barber->id] = $barberData;
        }

        return view('admin.schedules.index', compact(
            'barbers', 'days', 'weekStart', 'weekEnd', 'weekOffset', 'schedulesByBarber'
        ));
    }

    public function update(Request $request, Barber $barber)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i',
            'schedules.*.is_off' => 'sometimes|boolean',
        ]);

        foreach ($request->schedules as $scheduleData) {
            BarberSchedule::updateOrCreate(
                [
                    'barber_id' => $barber->id,
                    'day_of_week' => $scheduleData['day_of_week'],
                    'specific_date' => null,
                ],
                [
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'is_off' => isset($scheduleData['is_off']) ? true : false,
                    'is_available' => !isset($scheduleData['is_off']),
                ]
            );
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Đã cập nhật lịch làm việc cho thợ ' . $barber->name);
    }
}