<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarberSchedule extends Model
{
    protected $fillable = [
        'barber_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_off'
    ];

    protected $casts = [
        'is_off' => 'boolean',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
