<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VnpayController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    public function createPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'bank_code' => 'nullable|string|max:20',
            'locale' => 'nullable|in:vn,en',
        ]);

        $invoice = Invoice::with(['user', 'appointment.service'])->findOrFail($validated['invoice_id']);

        if (! $request->user()->isAdmin() && $invoice->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền thanh toán hóa đơn này.');
        }

        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Hóa đơn này đã được thanh toán.',
            ], 422);
        }

        if (! $invoice->appointment || $invoice->appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể thanh toán hóa đơn của lịch hẹn đã hoàn thành.',
            ], 422);
        }

        $invoice->update([
            'payment_method' => 'vnpay',
            'payment_status' => 'unpaid',
        ]);

        $paymentUrl = $this->paymentService->createPaymentUrl($invoice, [
            'bank_code' => $validated['bank_code'] ?? null,
            'locale' => $validated['locale'] ?? null,
            'ip_addr' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo URL thanh toán VNPAY thành công.',
            'data' => [
                'invoice_id' => $invoice->id,
                'payment_url' => $paymentUrl,
            ],
        ]);
    }

    public function callback(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        $payload = $this->paymentService->extractVnpParameters($request->all());

        if (empty($payload) || ! $this->paymentService->verifyResponse($request->all())) {
            $data = [
                'success' => false,
                'message' => 'Dữ liệu callback VNPAY không hợp lệ.',
            ];

            if ($request->expectsJson()) {
                return response()->json($data, 400);
            }

            return response()->view('payments.result', [
                'success' => false,
                'message' => $data['message'],
                'invoice' => null,
                'responseCode' => $payload['vnp_ResponseCode'] ?? null,
                'transactionStatus' => $payload['vnp_TransactionStatus'] ?? null,
                'transactionNo' => $payload['vnp_TransactionNo'] ?? null,
            ], 400);
        }

        $invoice = $this->resolveInvoiceFromPayload($payload);

        if (! $invoice) {
            $data = [
                'success' => false,
                'message' => 'Không tìm thấy hóa đơn tương ứng.',
            ];

            if ($request->expectsJson()) {
                return response()->json($data, 404);
            }

            return response()->view('payments.result', [
                'success' => false,
                'message' => $data['message'],
                'invoice' => null,
                'responseCode' => $payload['vnp_ResponseCode'] ?? null,
                'transactionStatus' => $payload['vnp_TransactionStatus'] ?? null,
                'transactionNo' => $payload['vnp_TransactionNo'] ?? null,
            ], 404);
        }

        $success = $this->paymentService->isSuccessfulPayment($payload);
        $message = $success
            ? 'Giao dịch VNPAY đã được xác thực. Trạng thái thanh toán sẽ được cập nhật qua IPN.'
            : 'Giao dịch VNPAY không thành công.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => [
                    'invoice_id' => $invoice->id,
                    'payment_status' => $invoice->payment_status,
                    'vnp_response_code' => $payload['vnp_ResponseCode'] ?? null,
                    'vnp_transaction_status' => $payload['vnp_TransactionStatus'] ?? null,
                ],
            ]);
        }

        return response()->view('payments.result', [
            'success' => $success,
            'message' => $message,
            'invoice' => $invoice,
            'responseCode' => $payload['vnp_ResponseCode'] ?? null,
            'transactionStatus' => $payload['vnp_TransactionStatus'] ?? null,
            'transactionNo' => $payload['vnp_TransactionNo'] ?? null,
        ]);
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

        $invoice = $this->resolveInvoiceFromPayload($payload);

        if (! $invoice) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
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
            $invoice->update([
                'payment_method' => 'vnpay',
                'payment_status' => 'paid',
                'transaction_id' => $payload['vnp_TransactionNo'] ?? $invoice->transaction_id,
            ]);
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

    private function resolveInvoiceFromPayload(array $payload): ?Invoice
    {
        $invoiceId = $this->paymentService->resolveInvoiceId((string) ($payload['vnp_TxnRef'] ?? ''));

        if (! $invoiceId) {
            return null;
        }

        return Invoice::find($invoiceId);
    }
}
