<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    /**
     * Một dịch vụ có nhiều lịch hẹn
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}