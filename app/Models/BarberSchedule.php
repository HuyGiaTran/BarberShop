<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BarberSchedule extends Model
{
    protected $fillable = [
        'barber_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_off',
        'is_available',
        'reason',
        'specific_date',
    ];

    protected $casts = [
        'is_off' => 'boolean',
        'is_available' => 'boolean',
    ];

    /**
     * Block schedule cho barber - 1 record/ngày (do unique key constraint)
     */
    public static function blockForLeave(LeaveRequest $leaveRequest): void
    {
        $dates = $leaveRequest->getAffectedDates();
        $timeSlots = $leaveRequest->getBlockedTimeSlots();

        foreach ($dates as $dateStr) {
            $date = Carbon::parse($dateStr);
            $dayOfWeek = $date->dayOfWeek;

            // Tính start_time nhỏ nhất và end_time lớn nhất
            $minStart = '23:59';
            $maxEnd = '00:00';
            foreach ($timeSlots as $slot) {
                if ($slot['start'] < $minStart) $minStart = $slot['start'];
                if ($slot['end'] > $maxEnd) $maxEnd = $slot['end'];
            }

            // Dùng updateOrCreate với unique key (barber_id, day_of_week)
            // để tránh duplicate key conflict với lịch mặc định
            self::updateOrCreate(
                [
                    'barber_id' => $leaveRequest->barber_id,
                    'day_of_week' => $dayOfWeek,
                ],
                [
                    'start_time' => $minStart,
                    'end_time' => $maxEnd,
                    'is_off' => false,
                    'is_available' => false,
                    'reason' => 'Nghỉ phép: ' . ($leaveRequest->reason ?? 'Không rõ'),
                    'specific_date' => $dateStr,
                ]
            );
        }
    }

    /**
     * Unblock schedule khi hủy duyệt
     */
    public static function unblockForLeave(LeaveRequest $leaveRequest): void
    {
        $dates = $leaveRequest->getAffectedDates();

        foreach ($dates as $dateStr) {
            self::where('barber_id', $leaveRequest->barber_id)
                ->where('specific_date', $dateStr)
                ->where('reason', 'like', 'Nghỉ phép:%')
                ->delete();
        }
    }
}