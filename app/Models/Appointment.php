<?php

namespace App\Models;

use App\Services\AppointmentService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    ];

    protected function appointmentTime(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? substr($value, 0, 5) : null,
            set: fn (?string $value) => $value
                ? (strlen($value) === 5 ? "{$value}:00" : $value)
                : null,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public static function hasConflict(
        int $barberId,
        string $appointmentDate,
        string $appointmentTime,
        int $durationMinutes,
        ?int $ignoreAppointmentId = null
    ): bool {
        return app(AppointmentService::class)->hasConflict(
            $barberId,
            $appointmentDate,
            $appointmentTime,
            $durationMinutes,
            $ignoreAppointmentId
        );
    }

    public static function unavailableSlotsForBarber(int $barberId, string $appointmentDate): array
    {
        return app(AppointmentService::class)->unavailableSlotsForBarber($barberId, $appointmentDate);
    }
}
