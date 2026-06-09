<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'start_time',
        'end_time',
        'reason',
        'handover_person',
        'commitment',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'applicant_dob' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'commitment' => 'boolean',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
