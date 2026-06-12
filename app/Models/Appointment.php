<?php

namespace App\Models;

use App\Services\AppointmentService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'barber_id',
        'service_id',
        'booking_reference',
        'booking_sequence',
        'is_booking_primary',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'deposit_amount',
        'deposit_status',
        'deposit_paid_at',
        'deposit_transaction_id',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_booking_primary' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'deposit_paid_at' => 'datetime',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function resolveComboLabelForServices(iterable $services): ?string
    {
        $comboKey = collect($services)
            ->map(function (mixed $service): ?string {
                if ($service instanceof self) {
                    return $service->service?->name;
                }

                if ($service instanceof Service) {
                    return $service->name;
                }

                if (is_array($service)) {
                    return $service['name'] ?? null;
                }

                return is_string($service) ? $service : null;
            })
            ->filter()
            ->map(fn (string $name) => Str::of(Str::ascii($name))->lower()->trim()->value())
            ->unique()
            ->sort()
            ->values()
            ->implode('|');

        return match ($comboKey) {
            'cao mat|cat toc|goi dau' => 'Combo Cắt tóc + Gội đầu + Cạo mặt',
            default => null,
        };
    }

    public function resolvedBookingReference(): string
    {
        return $this->booking_reference ?: 'APT-'.$this->id;
    }

    public function bookingAppointments(): EloquentCollection
    {
        if (! $this->exists) {
            return new EloquentCollection();
        }

        if ($this->booking_reference) {
            return static::with(['barber', 'service'])
                ->where('user_id', $this->user_id)
                ->where('booking_reference', $this->booking_reference)
                ->orderBy('booking_sequence')
                ->orderBy('appointment_time')
                ->get();
        }

        return static::with(['barber', 'service'])
            ->whereKey($this->id)
            ->get();
    }

    public function hasPaidDeposit(): bool
    {
        return $this->deposit_status === 'paid';
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
