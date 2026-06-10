<?php

namespace App\Services;

use App\Models\Appointment;
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

    public function allSlots(): array
    {
        $allSlots = [];

        for ($hour = 8; $hour <= 19; $hour++) {
            $allSlots[] = sprintf('%02d:00', $hour);

            if ($hour < 19) {
                $allSlots[] = sprintf('%02d:30', $hour);
            }
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
            return $this->allSlots();
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
        return array_values(array_diff(
            $this->allSlots(),
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
