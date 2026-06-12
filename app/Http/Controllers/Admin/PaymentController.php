<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentFlowService $paymentFlowService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Payment::with(['user', 'appointment.barber', 'appointment.service'])
            ->where('payment_type', 'deposit');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('booking_reference', 'like', "%{$keyword}%")
                    ->orWhere('gateway_txn_ref', 'like', "%{$keyword}%")
                    ->orWhere('gateway_transaction_no', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('appointment.barber', function ($barberQuery) use ($keyword) {
                        $barberQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $payments = $query->latest()->paginate(12)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function confirmDeposit(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->isDeposit(), 404);

        $validated = $request->validate([
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        if ($payment->status === 'paid') {
            return back()->with('success', 'Khoản cọc này đã được xác nhận trước đó.');
        }

        $this->paymentFlowService->confirmDepositPayment(
            $payment,
            $validated['transaction_reference'] ?? null
        );

        return back()->with('success', 'Đã xác nhận đặt cọc và chuyển lịch hẹn sang trạng thái đã xác nhận.');
    }

    public function rejectDeposit(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->isDeposit(), 404);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        if ($payment->status === 'paid') {
            return back()->with('error', 'Không thể từ chối khoản cọc đã xác nhận.');
        }

        $this->paymentFlowService->rejectDepositPayment(
            $payment,
            $validated['reason'] ?? null
        );

        return back()->with('success', 'Đã đưa khoản cọc về trạng thái thất bại/chưa xác nhận.');
    }
}
