<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BarberSchedule;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AppointmentService
{
    public function isBarberOnApprovedLeave(int $barberId, string $appointmentDate): bool
    {
        return LeaveRequest::where('barber_id', $barberId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $appointmentDate)
            ->whereDate('end_date', '>=', $appointmentDate)
            ->exists();
    }

    /**
     * Lấy danh sách khung giờ trống trong một ngày theo lịch làm việc của Barber
     */
    public function allSlots(int $barberId = null, string $appointmentDate = null): array
    {
        $allSlots = [];

        // Nếu không truyền thông tin, dùng lịch mặc định (cho các chức năng cũ nếu có)
        $startHour = 8;
        $startMin = 0;
        $endHour = 19;
        $endMin = 30;

        if ($barberId && $appointmentDate) {
            $date = Carbon::parse($appointmentDate);
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) -> 6 (Saturday)

            $schedule = BarberSchedule::where('barber_id', $barberId)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($schedule) {
                // Kiểm tra schedule có bị block không (nghỉ phép, bận)
                if ($schedule->is_off || !$schedule->is_available) {
                    return []; // Nghỉ làm hoặc bị block
                }

                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
                
                $startHour = $start->hour;
                $startMin = $start->minute;
                $endHour = $end->hour;
                $endMin = $end->minute;
            }
            
            // Kiểm tra nếu có schedule bị block theo specific_date cho ngày này
            $blockedSchedules = BarberSchedule::where('barber_id', $barberId)
                ->where('specific_date', $date->format('Y-m-d'))
                ->where(function ($q) {
                    $q->where('is_off', true)
                      ->orWhere('is_available', false);
                })
                ->get();
                
            if ($blockedSchedules->isNotEmpty()) {
                return []; // Có schedule bị block trong ngày này
            }
        }

        $cursor = Carbon::createFromTime($startHour, $startMin);
        $endTime = Carbon::createFromTime($endHour, $endMin);

        while ($cursor <= $endTime) {
            $allSlots[] = $cursor->format('H:i');
            $cursor->addMinutes(30);
        }

        return $allSlots;
    }

    /**
     * Check if a barber has a scheduling conflict or is on leave
     */
    public function hasConflict(
        int $barberId,
        string $appointmentDate,
        string $appointmentTime,
        int $durationMinutes,
        ?int $ignoreAppointmentId = null
    ): bool {
        if ($this->isBarberOnApprovedLeave($barberId, $appointmentDate)) {
            return true;
        }

        $newStart = Carbon::createFromFormat('H:i:s', $this->normalizeTimeValue($appointmentTime));
        $newEnd = (clone $newStart)->addMinutes(max($durationMinutes, 1));

        $appointments = Appointment::with('service:id,duration_minutes')
            ->where('barber_id', $barberId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreAppointmentId, function ($query) use ($ignoreAppointmentId) {
                $query->where('id', '!=', $ignoreAppointmentId);
            })
            ->get();

        return $appointments->contains(function (Appointment $appointment) use ($newStart, $newEnd): bool {
            $existingStart = Carbon::createFromFormat(
                'H:i:s',
                $this->normalizeTimeValue((string) $appointment->getRawOriginal('appointment_time'))
            );
            $existingDuration = max((int) ($appointment->service?->duration_minutes ?? 30), 1);
            $existingEnd = (clone $existingStart)->addMinutes($existingDuration);

            return $this->timeRangesOverlap($newStart, $newEnd, $existingStart, $existingEnd);
        });
    }

    /**
     * Get all unavailable slots for a given barber on a specific date
     */
    public function unavailableSlotsForBarber(int $barberId, string $appointmentDate): array
    {
        if ($this->isBarberOnApprovedLeave($barberId, $appointmentDate)) {
            return $this->allSlots($barberId, $appointmentDate);
        }

        $appointments = Appointment::with('service:id,duration_minutes')
            ->where('barber_id', $barberId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        $blockedSlots = [];

        foreach ($appointments as $appointment) {
            $start = Carbon::createFromFormat(
                'H:i:s',
                $this->normalizeTimeValue((string) $appointment->getRawOriginal('appointment_time'))
            );
            $duration = max((int) ($appointment->service?->duration_minutes ?? 30), 1);
            $end = (clone $start)->addMinutes($duration);
            $cursor = $start->copy();

            while ($cursor < $end) {
                $blockedSlots[] = $cursor->format('H:i');
                $cursor->addMinutes(30);
            }
        }

        return array_values(array_unique($blockedSlots));
    }

    public function availableSlotsForBarber(int $barberId, string $appointmentDate): array
    {
        $allSlots = $this->allSlots($barberId, $appointmentDate);
        
        if (empty($allSlots)) {
            return []; // Ngày nghỉ của thợ
        }

        return array_values(array_diff(
            $allSlots,
            $this->unavailableSlotsForBarber($barberId, $appointmentDate)
        ));
    }

    private function normalizeTimeValue(string $time): string
    {
        $normalizedTime = trim($time);
        return strlen($normalizedTime) === 5 ? "{$normalizedTime}:00" : $normalizedTime;
    }

    private function timeRangesOverlap(
        Carbon $rangeAStart,
        Carbon $rangeAEnd,
        Carbon $rangeBStart,
        Carbon $rangeBEnd
    ): bool {
        return $rangeAStart < $rangeBEnd && $rangeBStart < $rangeAEnd;
    }
}
