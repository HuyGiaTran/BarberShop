<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\PaymentFlowService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly PaymentFlowService $paymentFlowService
    ) {
    }

    public function index(Request $request): View
    {
        $this->ensureCustomer();

        $appointments = Appointment::with(['barber', 'service'])
            ->where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->orderBy('booking_sequence')
            ->get();

        $bookings = $appointments
            ->groupBy(fn (Appointment $appointment) => $appointment->resolvedBookingReference())
            ->map(fn (Collection $items) => $this->buildBookingSummary(new EloquentCollection($items->values()->all())))
            ->reject(fn (array $booking) => $booking['appointments']->every(
                fn (Appointment $appointment) => $appointment->status === 'cancelled'
            ))
            ->sortByDesc(fn (array $booking) => sprintf(
                '%s %s',
                $booking['primary']->appointment_date?->format('Y-m-d') ?? '',
                $booking['primary']->appointment_time ?? ''
            ))
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $pagedItems = $bookings->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedBookings = new LengthAwarePaginator(
            $pagedItems,
            $bookings->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('customer.my-schedules', [
            'bookings' => $paginatedBookings,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $this->ensureCustomer();
        $this->authorizeCustomerAppointment($appointment);

        [$bookingAppointments, $primaryAppointment] = $this->loadBookingContext($appointment);

        return view('customer.my-schedule-detail', $this->buildDetailViewData(
            $bookingAppointments,
            $primaryAppointment,
            false
        ));
    }

    public function downloadInvoicePdf(Appointment $appointment)
    {
        $this->ensureCustomer();
        $this->authorizeCustomerAppointment($appointment);

        $invoice = $appointment->invoice()->with(['user', 'appointment.barber', 'appointment.service'])->first();

        if (!$invoice) {
            return back()->with('error', 'Không tìm thấy hóa đơn cho lịch hẹn này.');
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('hoa_don_' . $invoice->id . '.pdf');
    }

    public function deposit(Appointment $appointment): RedirectResponse|View
    {
        $this->ensureCustomer();
        $this->authorizeCustomerAppointment($appointment);

        [$bookingAppointments, $primaryAppointment] = $this->loadBookingContext($appointment);
        $depositState = $this->resolveDepositState($bookingAppointments);
        $canDeposit = $this->canDepositBooking($bookingAppointments);
        $depositPayment = $this->latestDepositPayment($bookingAppointments);

        if (! $canDeposit && $depositState !== 'awaiting_confirmation') {
            return redirect()
                ->route('customer.appointments.show', $primaryAppointment)
                ->with('error', $this->depositBlockMessage($bookingAppointments));
        }

        if (! $depositPayment) {
            $depositPayment = $this->paymentFlowService->createDepositPayment($bookingAppointments, Auth::user());
        }

        return view('customer.my-schedule-detail', $this->buildDetailViewData(
            $bookingAppointments,
            $primaryAppointment,
            true,
            $depositPayment
        ));
    }

    public function processDeposit(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->ensureCustomer();
        $this->authorizeCustomerAppointment($appointment);

        [$bookingAppointments, $primaryAppointment] = $this->loadBookingContext($appointment);

        if (! $this->canDepositBooking($bookingAppointments)) {
            if ($this->resolveDepositState($bookingAppointments) === 'awaiting_confirmation') {
                return redirect()
                    ->route('customer.appointments.show', $primaryAppointment)
                    ->with('success', 'Khoản cọc này đang chờ admin xác nhận. Vui lòng đợi cửa hàng kiểm tra giao dịch.');
            }

            return redirect()
                ->route('customer.appointments.show', $primaryAppointment)
                ->with('error', $this->depositBlockMessage($bookingAppointments));
        }

        $payment = $this->paymentFlowService->createDepositPayment($bookingAppointments, $request->user());
        
        try {
            $paymentUrl = app(\App\Services\PaymentService::class)->createPaymentUrlForPayment($payment, [
                'ip_addr' => $request->ip(),
            ]);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            return redirect()
                ->route('customer.appointments.show', $primaryAppointment)
                ->with('error', 'Lỗi tạo giao dịch VNPAY: ' . $e->getMessage());
        }

    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->ensureCustomer();
        $this->authorizeCustomerAppointment($appointment);

        [$bookingAppointments, $primaryAppointment] = $this->loadBookingContext($appointment);

        if (! $this->canCancelBooking($bookingAppointments)) {
            return redirect()
                ->route('customer.appointments.show', $primaryAppointment)
                ->with('error', $this->cancelBlockMessage($bookingAppointments));
        }

        $bookingAppointments
            ->whereIn('status', ['pending', 'confirmed'])
            ->each(fn (Appointment $bookingAppointment) => $bookingAppointment->update([
                'status' => 'cancelled',
                'notes' => $this->appendCancellationNote($bookingAppointment->notes),
            ]));

        Payment::query()
            ->where('payment_type', 'deposit')
            ->where('user_id', Auth::id())
            ->where('booking_reference', $primaryAppointment->resolvedBookingReference())
            ->whereIn('status', ['pending'])
            ->update([
                'status' => 'cancelled',
            ]);

        return redirect()
            ->route('customer.appointments.index')
            ->with('success', 'Đã hủy lịch hẹn thành công.');
    }

    private function ensureCustomer(): void
    {
        abort_unless(Auth::user()?->role === 'customer', 403, 'Chức năng này chỉ dành cho khách hàng.');
    }

    private function authorizeCustomerAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->user_id === Auth::id(), 403, 'Bạn không có quyền xem lịch hẹn này.');
    }

    /**
     * @return array{0: EloquentCollection<int, Appointment>, 1: Appointment}
     */
    private function loadBookingContext(Appointment $appointment): array
    {
        $bookingAppointments = $appointment->bookingAppointments();
        $primaryAppointment = $bookingAppointments->firstWhere('is_booking_primary', true)
            ?? $bookingAppointments->sortBy('booking_sequence')->first()
            ?? $appointment->loadMissing(['barber', 'service']);

        return [$bookingAppointments, $primaryAppointment];
    }

    /**
     * @return array{
     *     reference: string,
     *     primary: Appointment,
     *     appointments: EloquentCollection<int, Appointment>,
     *     service_names: Collection<int, string>,
     *     combo_label: string|null,
     *     display_service_name: string,
     *     is_combo: bool,
     *     total_price: float,
     *     total_duration: int,
     *     deposit_amount: float,
     *     deposit_state: string,
     *     has_paid_deposit: bool,
     *     can_deposit: bool,
     *     can_cancel: bool
     * }
     */
    private function buildBookingSummary(EloquentCollection $appointments): array
    {
        $sortedAppointments = $appointments->sortBy('booking_sequence')->values();
        $primary = $sortedAppointments->firstWhere('is_booking_primary', true)
            ?? $sortedAppointments->firstOrFail();
        $serviceNames = $sortedAppointments
            ->map(fn (Appointment $appointment) => $appointment->service?->name)
            ->filter()
            ->unique()
            ->values();
        $comboLabel = Appointment::resolveComboLabelForServices($sortedAppointments);

        return [
            'reference' => $primary->resolvedBookingReference(),
            'primary' => $primary,
            'appointments' => $sortedAppointments,
            'service_names' => $serviceNames,
            'combo_label' => $comboLabel,
            'display_service_name' => $comboLabel
                ?? ($serviceNames->count() === 1 ? (string) $serviceNames->first() : $serviceNames->implode(' + ')),
            'is_combo' => $comboLabel !== null,
            'total_price' => (float) $appointments->sum(fn (Appointment $appointment) => (float) ($appointment->service?->price ?? 0)),
            'total_discount' => (float) $appointments->sum(fn (Appointment $appointment) => (float) ($appointment->discount_amount ?? 0)),
            'promo_code' => $appointments->firstWhere('promo_code', '!=', null)?->promo_code,
            'total_duration' => (int) $appointments->sum(fn (Appointment $appointment) => (int) ($appointment->service?->duration_minutes ?? 0)),
            'deposit_amount' => (float) ($primary->deposit_amount ?: 50000),
            'deposit_state' => $this->resolveDepositState($appointments),
            'has_paid_deposit' => $this->bookingHasPaidDeposit($appointments),
            'can_deposit' => $this->canDepositBooking($appointments),
            'can_cancel' => $this->canCancelBooking($appointments),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDetailViewData(
        EloquentCollection $bookingAppointments,
        Appointment $primaryAppointment,
        bool $showPaymentPanel,
        ?Payment $depositPayment = null
    ): array
    {
        $summary = $this->buildBookingSummary($bookingAppointments);
        $depositPayment ??= $this->latestDepositPayment($bookingAppointments);
        $transferConfig = $this->transferConfig();
        $transferContent = $this->transferContent($summary['reference']);

        return [
            'appointment' => $primaryAppointment,
            'bookingAppointments' => $bookingAppointments,
            'bookingReference' => $summary['reference'],
            'bookingComboLabel' => $summary['combo_label'],
            'bookingDisplayName' => $summary['display_service_name'],
            'bookingIsCombo' => $summary['is_combo'],
            'bookingTotalPrice' => $summary['total_price'],
            'bookingTotalDiscount' => $summary['total_discount'],
            'bookingPromoCode' => $summary['promo_code'],
            'bookingTotalDuration' => $summary['total_duration'],
            'depositAmount' => $showPaymentPanel ? $summary['deposit_amount'] : null,
            'depositState' => $summary['deposit_state'],
            'depositStateLabel' => $this->depositStateLabel($summary['deposit_state']),
            'showPaymentPanel' => $showPaymentPanel,
            'hasPaidDeposit' => $summary['has_paid_deposit'],
            'canDeposit' => $summary['can_deposit'],
            'canCancel' => $summary['can_cancel'],
            'depositPayment' => $depositPayment,
            'transferConfigured' => $this->transferConfigured($transferConfig),
            'transferBankName' => $transferConfig['bank_name'] ?? null,
            'transferBankAccount' => $transferConfig['bank_account'] ?? null,
            'transferAccountName' => $transferConfig['account_name'] ?? null,
            'transferContent' => $transferContent,
            'transferQrUrl' => $this->buildTransferQrUrl(
                $transferConfig,
                $summary['deposit_amount'],
                $transferContent
            ),
        ];
    }

    private function canDepositBooking(EloquentCollection $bookingAppointments): bool
    {
        return $this->resolveDepositState($bookingAppointments) === 'unpaid'
            && $bookingAppointments->isNotEmpty()
            && $bookingAppointments->every(fn (Appointment $appointment) => $appointment->status === 'pending');
    }

    private function canCancelBooking(EloquentCollection $bookingAppointments): bool
    {
        if ($this->resolveDepositState($bookingAppointments) !== 'unpaid') {
            return false;
        }

        return $bookingAppointments->isNotEmpty()
            && $bookingAppointments->every(fn (Appointment $appointment) => in_array($appointment->status, ['pending', 'confirmed'], true));
    }

    private function bookingHasPaidDeposit(EloquentCollection $bookingAppointments): bool
    {
        return $bookingAppointments->contains(fn (Appointment $appointment) => $appointment->deposit_status === 'paid');
    }

    private function resolveDepositState(EloquentCollection $bookingAppointments): string
    {
        if ($bookingAppointments->contains(fn (Appointment $appointment) => $appointment->deposit_status === 'paid')) {
            return 'paid';
        }

        if ($bookingAppointments->contains(fn (Appointment $appointment) => $appointment->deposit_status === 'awaiting_confirmation')) {
            return 'awaiting_confirmation';
        }

        return 'unpaid';
    }

    private function depositBlockMessage(EloquentCollection $bookingAppointments): string
    {
        if ($this->bookingHasPaidDeposit($bookingAppointments)) {
            return 'Lượt hẹn này đã thanh toán cọc rồi.';
        }

        if ($this->resolveDepositState($bookingAppointments) === 'awaiting_confirmation') {
            return 'Khoản cọc này đang chờ admin xác nhận. Vui lòng đợi cửa hàng kiểm tra giao dịch.';
        }

        if ($bookingAppointments->contains(fn (Appointment $appointment) => $appointment->status === 'cancelled')) {
            return 'Không thể thanh toán cọc cho lịch hẹn đã bị hủy.';
        }

        if ($bookingAppointments->contains(fn (Appointment $appointment) => $appointment->status === 'completed')) {
            return 'Không thể thanh toán cọc cho lịch hẹn đã hoàn thành.';
        }

        return 'Chỉ có thể thanh toán cọc khi toàn bộ lịch trong lượt hẹn đang ở trạng thái chờ xác nhận.';
    }

    private function cancelBlockMessage(EloquentCollection $bookingAppointments): string
    {
        if ($this->bookingHasPaidDeposit($bookingAppointments)) {
            return 'Lịch hẹn đã thanh toán cọc nên không thể hủy online.';
        }

        if ($this->resolveDepositState($bookingAppointments) === 'awaiting_confirmation') {
            return 'Khoản cọc đang chờ admin xác nhận nên tạm thời không thể hủy online.';
        }

        if ($bookingAppointments->contains(fn (Appointment $appointment) => $appointment->status === 'completed')) {
            return 'Không thể hủy lịch hẹn đã hoàn thành.';
        }

        if ($bookingAppointments->every(fn (Appointment $appointment) => $appointment->status === 'cancelled')) {
            return 'Lịch hẹn này đã được hủy trước đó.';
        }

        return 'Chỉ có thể hủy lịch hẹn đang chờ xác nhận hoặc đã xác nhận nhưng chưa thanh toán.';
    }

    private function appendCancellationNote(?string $notes): string
    {
        $cancellationNote = 'Khách hàng đã hủy lịch từ My Schedule.';

        if (! $notes) {
            return $cancellationNote;
        }

        if (str_contains($notes, $cancellationNote)) {
            return $notes;
        }

        return trim($notes)."\n".$cancellationNote;
    }

    private function latestDepositPayment(EloquentCollection $bookingAppointments): ?Payment
    {
        $reference = $bookingAppointments->first()?->resolvedBookingReference();

        if (! $reference) {
            return null;
        }

        return Payment::query()
            ->where('payment_type', 'deposit')
            ->where('user_id', Auth::id())
            ->where('booking_reference', $reference)
            ->latest()
            ->first();
    }

    private function depositStateLabel(string $depositState): string
    {
        return match ($depositState) {
            'paid' => 'Đã thanh toán cọc',
            'awaiting_confirmation' => 'Chờ admin xác nhận',
            default => 'Chưa thanh toán cọc',
        };
    }

    private function transferConfig(): array
    {
        return config('services.deposit_transfer', []);
    }

    private function transferConfigured(array $transferConfig): bool
    {
        return ! empty($transferConfig['bank_bin'])
            && ! empty($transferConfig['bank_account'])
            && ! empty($transferConfig['account_name']);
    }

    private function transferContent(string $bookingReference): string
    {
        return 'COC '.$bookingReference;
    }

    private function buildTransferQrUrl(array $transferConfig, float $amount, string $transferContent): ?string
    {
        if (! $this->transferConfigured($transferConfig)) {
            return null;
        }

        $bankBin = rawurlencode((string) $transferConfig['bank_bin']);
        $bankAccount = rawurlencode((string) $transferConfig['bank_account']);
        $accountName = rawurlencode((string) $transferConfig['account_name']);
        $template = rawurlencode((string) ($transferConfig['qr_template'] ?? 'compact2'));
        $transferContent = rawurlencode($transferContent);

        return sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?amount=%d&addInfo=%s&accountName=%s',
            $bankBin,
            $bankAccount,
            $template,
            (int) round($amount),
            $transferContent,
            $accountName
        );
    }
}
