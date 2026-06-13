<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'appointment_id',
        'invoice_id',
        'booking_reference',
        'payment_type',
        'gateway',
        'amount',
        'status',
        'gateway_txn_ref',
        'gateway_transaction_no',
        'gateway_payload',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isDeposit(): bool
    {
        return $this->payment_type === 'deposit';
    }

    public function isInvoicePayment(): bool
    {
        return $this->payment_type === 'invoice';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this->status === 'awaiting_confirmation';
    }
}
