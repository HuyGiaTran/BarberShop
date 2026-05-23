<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'barber_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'string',
    ];

    /**
     * Lịch hẹn thuộc về một user (khách hàng)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lịch hẹn có một barber phục vụ
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    /**
     * Lịch hẹn có một dịch vụ được chọn
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}