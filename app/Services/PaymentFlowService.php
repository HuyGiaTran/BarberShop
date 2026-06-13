<?php

namespace App\Services;

use App\Events\InvoicePaid;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class PaymentFlowService
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    public function createDepositPayment(EloquentCollection $bookingAppointments, User $user): Payment
    {
        /** @var Appointment|null $primaryAppointment */
        $primaryAppointment = $bookingAppointments->firstWhere('is_booking_primary', true)
            ?? $bookingAppointments->sortBy('booking_sequence')->first();

        abort_unless($primaryAppointment, 404, 'Không tìm thấy lịch hẹn đại diện cho lượt đặt cọc.');

        $existingPayment = Payment::query()
            ->where('payment_type', 'deposit')
            ->where('user_id', $user->id)
            ->where('booking_reference', $primaryAppointment->resolvedBookingReference())
            ->whereIn('status', ['pending', 'awaiting_confirmation'])
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        return Payment::create([
            'user_id' => $user->id,
            'appointment_id' => $primaryAppointment->id,
            'booking_reference' => $primaryAppointment->resolvedBookingReference(),
            'payment_type' => 'deposit',
            'gateway' => 'bank_transfer',
            'amount' => (float) ($primaryAppointment->deposit_amount ?: 50000),
            'status' => 'pending',
            'gateway_txn_ref' => $this->paymentService->buildGatewayTxnRef('DEP'),
            'expires_at' => now()->addMinutes((int) config('services.vnpay.expire_minutes', 15)),
        ]);
    }

    public function createInvoicePayment(Invoice $invoice): Payment
    {
        $existingPayment = Payment::query()
            ->where('payment_type', 'invoice')
            ->where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        return Payment::create([
            'user_id' => $invoice->user_id,
            'appointment_id' => $invoice->appointment_id,
            'invoice_id' => $invoice->id,
            'booking_reference' => $invoice->appointment?->resolvedBookingReference(),
            'payment_type' => 'invoice',
            'gateway' => 'vnpay',
            'amount' => (float) $invoice->total_amount,
            'status' => 'pending',
            'gateway_txn_ref' => $this->paymentService->buildGatewayTxnRef('INV'),
            'expires_at' => now()->addMinutes((int) config('services.vnpay.expire_minutes', 15)),
        ]);
    }

    public function markInvoiceAsPaid(Invoice $invoice, string $method, ?string $transactionId = null): bool
    {
        if ($invoice->payment_status === 'paid') {
            return false;
        }

        $invoice->update([
            'payment_method' => $method,
            'payment_status' => 'paid',
            'transaction_id' => $transactionId,
        ]);

        event(new InvoicePaid($invoice->fresh()));

        return true;
    }

    public function markDepositAsAwaitingConfirmation(Payment $payment): bool
    {
        if (! $payment->isDeposit() || $payment->status === 'paid' || $payment->status === 'awaiting_confirmation') {
            return false;
        }

        return DB::transaction(function () use ($payment): bool {
            $payment->refresh();

            if ($payment->status === 'paid' || $payment->status === 'awaiting_confirmation') {
                return false;
            }

            $payload = $payment->gateway_payload ?? [];
            $payload['customer_marked_transferred_at'] = now()->toISOString();

            $payment->update([
                'status' => 'awaiting_confirmation',
                'gateway_payload' => $payload,
            ]);

            $this->resolveBookingAppointments($payment)->each(function (Appointment $appointment): void {
                $appointment->update([
                    'deposit_status' => 'awaiting_confirmation',
                ]);
            });

            return true;
        });
    }

    public function confirmDepositPayment(Payment $payment, ?string $transactionReference = null): bool
    {
        if (! $payment->isDeposit()) {
            return false;
        }

        return $this->settleSuccessfulPayment($payment, [
            'vnp_TransactionNo' => $transactionReference ?: $payment->gateway_txn_ref,
        ]);
    }

    public function rejectDepositPayment(Payment $payment, ?string $reason = null): bool
    {
        if (! $payment->isDeposit() || $payment->status === 'paid') {
            return false;
        }

        return DB::transaction(function () use ($payment, $reason): bool {
            $payment->refresh();

            if ($payment->status === 'paid') {
                return false;
            }

            $payload = $payment->gateway_payload ?? [];
            $payload['admin_rejected_at'] = now()->toISOString();

            if ($reason) {
                $payload['rejection_reason'] = $reason;
            }

            $payment->update([
                'status' => 'failed',
                'gateway_payload' => $payload,
            ]);

            $this->resolveBookingAppointments($payment)->each(function (Appointment $appointment): void {
                $appointment->update([
                    'deposit_status' => 'unpaid',
                    'deposit_paid_at' => null,
                    'deposit_transaction_id' => null,
                ]);
            });

            return true;
        });
    }

    public function settleSuccessfulPayment(Payment $payment, array $payload = []): bool
    {
        if ($payment->status === 'paid') {
            return false;
        }

        return DB::transaction(function () use ($payment, $payload): bool {
            $payment->refresh();

            if ($payment->status === 'paid') {
                return false;
            }

            $transactionNo = $payload['vnp_TransactionNo'] ?? $payment->gateway_transaction_no ?? $payment->gateway_txn_ref;

            $payment->update([
                'status' => 'paid',
                'gateway_transaction_no' => $transactionNo,
                'gateway_payload' => $payload === [] ? $payment->gateway_payload : $payload,
                'paid_at' => now(),
            ]);

            if ($payment->isInvoicePayment() && $payment->invoice) {
                $this->markInvoiceAsPaid($payment->invoice()->firstOrFail(), 'vnpay', $transactionNo);
            }

            if ($payment->isDeposit()) {
                $bookingAppointments = $this->resolveBookingAppointments($payment);

                $bookingAppointments->each(function (Appointment $appointment) use ($transactionNo): void {
                    $appointment->update([
                        'status' => $appointment->status === 'pending' ? 'confirmed' : $appointment->status,
                        'deposit_status' => 'paid',
                        'deposit_paid_at' => now(),
                        'deposit_transaction_id' => $transactionNo,
                    ]);
                });
            }

            return true;
        });
    }

    public function markPaymentAsFailed(Payment $payment, array $payload = []): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status' => 'failed',
            'gateway_transaction_no' => $payload['vnp_TransactionNo'] ?? $payment->gateway_transaction_no,
            'gateway_payload' => $payload === [] ? $payment->gateway_payload : $payload,
        ]);

        if ($payment->isDeposit()) {
            $this->resolveBookingAppointments($payment)->each(function (Appointment $appointment): void {
                $appointment->update([
                    'deposit_status' => 'unpaid',
                    'deposit_paid_at' => null,
                    'deposit_transaction_id' => null,
                ]);
            });
        }
    }

    /**
     * @return EloquentCollection<int, Appointment>
     */
    private function resolveBookingAppointments(Payment $payment): EloquentCollection
    {
        if ($payment->booking_reference) {
            return Appointment::with(['barber', 'service'])
                ->where('user_id', $payment->user_id)
                ->where('booking_reference', $payment->booking_reference)
                ->orderBy('booking_sequence')
                ->get();
        }

        if ($payment->appointment_id) {
            return Appointment::with(['barber', 'service'])
                ->whereKey($payment->appointment_id)
                ->get();
        }

        return new EloquentCollection();
    }
}
