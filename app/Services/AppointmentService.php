<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AppointmentService
{
    public function getBookingAvailability(int $barberId, string $appointmentDate): array
    {
        $barber = Barber::find($barberId);

        if (! $barber) {
            return [
                'bookable' => false,
                'reason' => 'barber_not_found',
                'barber' => null,
                'schedule' => null,
            ];
        }

        if (! $barber->is_active) {
            return [
                'bookable' => false,
                'reason' => 'barber_inactive',
                'barber' => $barber,
                'schedule' => null,
            ];
        }

        $workingStatus = (string) ($barber->working_status ?? 'active');

        if ($workingStatus !== 'active') {
            return [
                'bookable' => false,
                'reason' => "barber_{$workingStatus}",
                'barber' => $barber,
                'schedule' => null,
            ];
        }

        if ($this->isBarberOnApprovedLeave($barberId, $appointmentDate)) {
            return [
                'bookable' => false,
                'reason' => 'barber_on_leave',
                'barber' => $barber,
                'schedule' => null,
            ];
        }

        $schedule = $this->resolveScheduleForDate($barberId, $appointmentDate);

        if (! $schedule) {
            return [
                'bookable' => false,
                'reason' => 'no_schedule',
                'barber' => $barber,
                'schedule' => null,
            ];
        }

        if ($schedule->is_off || ! $schedule->is_available) {
            return [
                'bookable' => false,
                'reason' => 'blocked_schedule',
                'barber' => $barber,
                'schedule' => $schedule,
            ];
        }

        return [
            'bookable' => true,
            'reason' => null,
            'barber' => $barber,
            'schedule' => $schedule,
        ];
    }

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
        $endHour = 18;
        $endMin = 0;

        if ($barberId && $appointmentDate) {
            $availability = $this->getBookingAvailability($barberId, $appointmentDate);

            if (! $availability['bookable'] || ! $availability['schedule']) {
                return [];
            }

            $start = Carbon::parse($availability['schedule']->start_time);
            $end = Carbon::parse($availability['schedule']->end_time);

            $startHour = $start->hour;
            $startMin = $start->minute;
            $endHour = $end->hour;
            $endMin = $end->minute;
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
        return $this->availableSlotsForDuration($barberId, $appointmentDate);
    }

    public function availableSlotsForDuration(
        int $barberId,
        string $appointmentDate,
        int $durationMinutes = 30,
        ?int $ignoreAppointmentId = null
    ): array {
        $allSlots = $this->allSlots($barberId, $appointmentDate);

        if (empty($allSlots)) {
            return [];
        }

        $availability = $this->getBookingAvailability($barberId, $appointmentDate);

        if (! $availability['bookable'] || ! $availability['schedule']) {
            return [];
        }

        $appointments = Appointment::with('service:id,duration_minutes')
            ->where('barber_id', $barberId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreAppointmentId, function ($query) use ($ignoreAppointmentId) {
                $query->where('id', '!=', $ignoreAppointmentId);
            })
            ->get();

        $duration = max($durationMinutes, 1);

        return array_values(array_filter($allSlots, function (string $slot) use ($availability, $appointments, $duration): bool {
            if (! $this->fitsWithinSchedule($availability['schedule'], $slot, $duration)) {
                return false;
            }

            $newStart = Carbon::createFromFormat('H:i:s', $this->normalizeTimeValue($slot));
            $newEnd = (clone $newStart)->addMinutes($duration);

            return ! $appointments->contains(function (Appointment $appointment) use ($newStart, $newEnd): bool {
                $existingStart = Carbon::createFromFormat(
                    'H:i:s',
                    $this->normalizeTimeValue((string) $appointment->getRawOriginal('appointment_time'))
                );
                $existingDuration = max((int) ($appointment->service?->duration_minutes ?? 30), 1);
                $existingEnd = (clone $existingStart)->addMinutes($existingDuration);

                return $this->timeRangesOverlap($newStart, $newEnd, $existingStart, $existingEnd);
            });
        }));
    }

    private function normalizeTimeValue(string $time): string
    {
        $normalizedTime = trim($time);
        return strlen($normalizedTime) === 5 ? "{$normalizedTime}:00" : $normalizedTime;
    }

    private function resolveScheduleForDate(int $barberId, string $appointmentDate): ?BarberSchedule
    {
        $date = Carbon::parse($appointmentDate);
        $specificDate = $date->format('Y-m-d');

        return BarberSchedule::where('barber_id', $barberId)
            ->where('specific_date', $specificDate)
            ->first()
            ?? BarberSchedule::where('barber_id', $barberId)
                ->whereNull('specific_date')
                ->where('day_of_week', $date->dayOfWeek)
                ->first();
    }

    private function fitsWithinSchedule(BarberSchedule $schedule, string $appointmentTime, int $durationMinutes): bool
    {
        $start = Carbon::createFromFormat('H:i:s', $this->normalizeTimeValue($appointmentTime));
        $end = (clone $start)->addMinutes(max($durationMinutes, 1));
        $scheduleStart = Carbon::createFromFormat('H:i:s', $this->normalizeTimeValue((string) $schedule->start_time));
        $scheduleEnd = Carbon::createFromFormat('H:i:s', $this->normalizeTimeValue((string) $schedule->end_time));

        return $start >= $scheduleStart && $end <= $scheduleEnd;
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
