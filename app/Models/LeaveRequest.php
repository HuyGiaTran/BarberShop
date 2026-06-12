<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'barber_id',
        'recipient',
        'applicant_name',
        'applicant_dob',
        'applicant_address',
        'applicant_phone',
        'applicant_workplace',
        'applicant_position',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'handover_person',
        'commitment',
        'status',
        'rejection_reason',
        'leave_type',
        'leave_dates',
    ];

    protected $casts = [
        'applicant_dob' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'commitment' => 'boolean',
        'leave_dates' => 'array',
    ];

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function getAffectedDates(): array
    {
        if ($this->leave_dates && is_array($this->leave_dates)) {
            return $this->leave_dates;
        }
        if ($this->start_date && $this->end_date) {
            $dates = [];
            $current = $this->start_date->copy();
            while ($current <= $this->end_date) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
            return $dates;
        }
        return [];
    }

    public function getBlockedTimeSlots(): array
    {
        $allSlots = [
            'morning' => ['start' => '08:00', 'end' => '13:00'],
            'afternoon' => ['start' => '13:00', 'end' => '18:00'],
            'evening' => ['start' => '18:00', 'end' => '22:00'],
        ];

        // Full day: block 1 range duy nhất vì DB chỉ cho 1 record/ngày
        if ($this->leave_type === 'full_day') {
            return [['start' => '08:00', 'end' => '22:00']];
        }

        // Nghỉ theo ca: tính các ca bị block dựa trên start_time/end_time
        if ($this->start_time && $this->end_time) {
            $startH = (int)$this->start_time->format('H');
            $endH = (int)$this->end_time->format('H');
            $blocked = [];

            foreach ($allSlots as $key => $slot) {
                $slotStartH = (int)explode(':', $slot['start'])[0];
                $slotEndH = (int)explode(':', $slot['end'])[0];
                if ($slotStartH < $endH && $slotEndH > $startH) {
                    $blocked[] = $slot;
                }
            }
            return $blocked;
        }

        return [];
    }
}