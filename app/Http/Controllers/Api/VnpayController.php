<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentFlowService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class VnpayController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PaymentFlowService $paymentFlowService
    ) {
    }

    public function createPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'bank_code' => 'nullable|string|max:20',
            'locale' => 'nullable|in:vn,en',
        ]);

        $invoice = Invoice::with(['user', 'appointment.service', 'appointment.barber'])->findOrFail($validated['invoice_id']);

        if (! $request->user()->isAdmin() && $invoice->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền thanh toán hóa đơn này.');
        }

        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Hóa đơn này đã được thanh toán.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        if (! $invoice->appointment || $invoice->appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể thanh toán hóa đơn của lịch hẹn đã hoàn thành.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $invoice->update([
            'payment_method' => 'vnpay',
            'payment_status' => 'unpaid',
        ]);

        $payment = $this->paymentFlowService->createInvoicePayment($invoice);

        try {
            $paymentUrl = $this->paymentService->createPaymentUrlForPayment($payment, [
                'bank_code' => $validated['bank_code'] ?? null,
                'locale' => $validated['locale'] ?? null,
                'ip_addr' => $request->ip(),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo URL thanh toán VNPAY thành công.',
            'data' => [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'payment_url' => $paymentUrl,
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function callback(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        $payload = $this->paymentService->extractVnpParameters($request->all());

        if (empty($payload) || ! $this->paymentService->verifyResponse($request->all())) {
            return $this->callbackResponse(
                $request,
                false,
                'Dữ liệu callback VNPAY không hợp lệ.',
                null,
                null,
                $payload,
                400
            );
        }

        [$payment, $invoice] = $this->resolveTargetsFromPayload($payload);

        if (! $payment && ! $invoice) {
            return $this->callbackResponse(
                $request,
                false,
                'Không tìm thấy giao dịch tương ứng.',
                null,
                null,
                $payload,
                404
            );
        }

        if ($this->paymentService->isSuccessfulPayment($payload)) {
            if ($payment) {
                $this->paymentFlowService->settleSuccessfulPayment($payment, $payload);
                $payment->refresh();
                $invoice = $payment->invoice?->fresh() ?? $invoice;
            } elseif ($invoice) {
                $this->paymentFlowService->markInvoiceAsPaid(
                    $invoice,
                    'vnpay',
                    $payload['vnp_TransactionNo'] ?? $invoice->transaction_id
                );
                $invoice->refresh();
            }

            $message = $payment?->isDeposit()
                ? 'Đặt cọc đã được xác thực và lượt hẹn của bạn đã được xác nhận.'
                : 'Thanh toán hóa đơn đã được xác thực thành công.';

            return $this->callbackResponse($request, true, $message, $payment, $invoice, $payload);
        }

        if ($payment) {
            $this->paymentFlowService->markPaymentAsFailed($payment, $payload);
        }

        if ($invoice && $invoice->payment_status !== 'paid') {
            $invoice->update([
                'payment_method' => 'vnpay',
            ]);
        }

        return $this->callbackResponse(
            $request,
            false,
            'Giao dịch VNPAY không thành công.',
            $payment,
            $invoice,
            $payload
        );
    }

    public function ipn(Request $request): JsonResponse
    {
        $payload = $this->paymentService->extractVnpParameters($request->all());

        if (empty($payload)) {
            return response()->json([
                'RspCode' => '99',
                'Message' => 'Input data required',
            ]);
        }

        if (! $this->paymentService->verifyResponse($request->all())) {
            return response()->json([
                'RspCode' => '97',
                'Message' => 'Invalid signature',
            ]);
        }

        [$payment, $invoice] = $this->resolveTargetsFromPayload($payload);

        if (! $payment && ! $invoice) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
            ]);
        }

        if ($payment) {
            if (! $this->paymentService->matchesPaymentAmount($payment, (int) ($payload['vnp_Amount'] ?? 0))) {
                return response()->json([
                    'RspCode' => '04',
                    'Message' => 'invalid amount',
                ]);
            }

            if ($payment->status === 'paid') {
                return response()->json([
                    'RspCode' => '02',
                    'Message' => 'Order already confirmed',
                ]);
            }

            if ($this->paymentService->isSuccessfulPayment($payload)) {
                $this->paymentFlowService->settleSuccessfulPayment($payment, $payload);
            } else {
                $this->paymentFlowService->markPaymentAsFailed($payment, $payload);
            }

            return response()->json([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);
        }

        if (! $this->paymentService->matchesAmount($invoice, (int) ($payload['vnp_Amount'] ?? 0))) {
            return response()->json([
                'RspCode' => '04',
                'Message' => 'invalid amount',
            ]);
        }

        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'RspCode' => '02',
                'Message' => 'Order already confirmed',
            ]);
        }

        if ($this->paymentService->isSuccessfulPayment($payload)) {
            $this->paymentFlowService->markInvoiceAsPaid(
                $invoice,
                'vnpay',
                $payload['vnp_TransactionNo'] ?? $invoice->transaction_id
            );
        } else {
            $invoice->update([
                'payment_method' => 'vnpay',
            ]);
        }

        return response()->json([
            'RspCode' => '00',
            'Message' => 'Confirm Success',
        ]);
    }

    private function callbackResponse(
        Request $request,
        bool $success,
        string $message,
        ?Payment $payment,
        ?Invoice $invoice,
        array $payload,
        int $status = 200
    ): JsonResponse|\Illuminate\Http\Response {
        $targetReference = $payment?->isDeposit()
            ? ($payment->booking_reference ?: 'N/A')
            : ($invoice ? '#'.$invoice->id : 'N/A');

        $currentStatus = $payment?->status
            ?? $invoice?->payment_status
            ?? 'unknown';

        $viewData = [
            'success' => $success,
            'message' => $message,
            'payment' => $payment,
            'invoice' => $invoice,
            'targetType' => $payment?->isDeposit() ? 'deposit' : 'invoice',
            'targetReference' => $targetReference,
            'currentStatus' => $currentStatus,
            'responseCode' => $payload['vnp_ResponseCode'] ?? null,
            'transactionStatus' => $payload['vnp_TransactionStatus'] ?? null,
            'transactionNo' => $payload['vnp_TransactionNo'] ?? null,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => [
                    'payment_id' => $payment?->id,
                    'invoice_id' => $invoice?->id,
                    'target_type' => $viewData['targetType'],
                    'target_reference' => $targetReference,
                    'current_status' => $currentStatus,
                    'vnp_response_code' => $viewData['responseCode'],
                    'vnp_transaction_status' => $viewData['transactionStatus'],
                ],
            ], $status, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->view('payments.result', $viewData, $status);
    }

    /**
     * @return array{0: ?Payment, 1: ?Invoice}
     */
    private function resolveTargetsFromPayload(array $payload): array
    {
        $txnRef = (string) ($payload['vnp_TxnRef'] ?? '');
        $payment = $this->paymentService->resolvePaymentByTxnRef($txnRef);

        if ($payment) {
            return [$payment, $payment->invoice];
        }

        $invoiceId = $this->paymentService->resolveInvoiceId($txnRef);

        if (! $invoiceId) {
            return [null, null];
        }

        return [null, Invoice::find($invoiceId)];
    }
}
