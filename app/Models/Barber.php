<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barber extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'bio',
        'avatar',
        'is_active',
        'working_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Một barber thuộc về một user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Một barber có nhiều lịch hẹn
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Một barber có nhiều dịch vụ
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Một barber có nhiều đánh giá
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Một barber có nhiều đơn xin nghỉ phép
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Một barber có nhiều bảng lương
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Một barber có nhiều lịch làm việc
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(BarberSchedule::class);
    }
}