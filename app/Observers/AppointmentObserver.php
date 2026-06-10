<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\Invoice;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $this->createInvoiceIfNeeded($appointment, true);
    }

    public function updated(Appointment $appointment): void
    {
        $this->createInvoiceIfNeeded($appointment, false);
    }

    private function createInvoiceIfNeeded(Appointment $appointment, bool $wasCreated): void
    {
        if ($appointment->status !== 'completed') {
            return;
        }

        if (! $wasCreated && ! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->invoice()->exists()) {
            return;
        }

        $appointment->loadMissing('service');

        Invoice::create([
            'appointment_id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'total_amount' => $appointment->service?->price ?? 0,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'transaction_id' => null,
        ]);
    }
}
