<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\BarberSchedule;
use Illuminate\Http\Request;

class BarberScheduleController extends Controller
{
    public function index()
    {
        $barbers = Barber::with('schedules')->get();
        return view('admin.schedules.index', compact('barbers'));
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
                ],
                [
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'is_off' => isset($scheduleData['is_off']) ? true : false,
                ]
            );
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Đã cập nhật lịch làm việc cho thợ ' . $barber->name);
    }
}
