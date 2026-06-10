<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'barber_id',
        'month',
        'base_salary',
        'commission',
        'total_appointments',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'commission' => 'decimal:2',
        'total_appointments' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
