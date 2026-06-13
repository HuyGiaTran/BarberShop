<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function createPaymentUrl(Invoice $invoice, array $options = []): string
    {
        return $this->buildPaymentUrl(
            (float) $invoice->total_amount,
            $this->buildTxnRef($invoice),
            $this->sanitizeOrderInfo(sprintf(
                'Thanh toan hoa don %s cho lich hen %s',
                $invoice->id,
                $invoice->appointment_id
            )),
            $options
        );
    }

    public function createPaymentUrlForPayment(Payment $payment, array $options = []): string
    {
        // VNPAY requires a unique TxnRef for every payment attempt, even if the previous one failed or was abandoned.
        // We regenerate the gateway_txn_ref to ensure it's always unique on every redirect.
        $newTxnRef = $this->buildGatewayTxnRef($payment->isDeposit() ? 'DEP' : 'INV');
        $payment->update(['gateway_txn_ref' => $newTxnRef]);

        return $this->buildPaymentUrl(
            (float) $payment->amount,
            (string) $payment->gateway_txn_ref,
            $this->paymentOrderInfo($payment),
            $options
        );
    }

    public function verifyResponse(array $payload): bool
    {
        $secureHash = (string) Arr::get($payload, 'vnp_SecureHash', '');

        if ($secureHash === '') {
            return false;
        }

        $data = $this->extractVnpParameters($payload);
        unset($data['vnp_SecureHash'], $data['vnp_SecureHashType']);

        $calculatedHash = hash_hmac('sha512', $this->buildHashData($data), (string) ($this->config()['hash_secret'] ?? ''));

        return hash_equals($calculatedHash, $secureHash);
    }

    public function extractVnpParameters(array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, $key) => Str::startsWith((string) $key, 'vnp_'))
            ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
            ->all();
    }

    public function isSuccessfulPayment(array $payload): bool
    {
        return Arr::get($payload, 'vnp_ResponseCode') === '00'
            && Arr::get($payload, 'vnp_TransactionStatus') === '00';
    }

    public function resolveInvoiceId(string $txnRef): ?int
    {
        if (preg_match('/^INV(\d+)_\d{14}$/', $txnRef, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    public function resolvePaymentByTxnRef(string $txnRef): ?Payment
    {
        return Payment::query()->where('gateway_txn_ref', $txnRef)->first();
    }

    public function buildTxnRef(Invoice $invoice): string
    {
        return sprintf('INV%d_%s', $invoice->id, now('Asia/Ho_Chi_Minh')->format('YmdHis'));
    }

    public function buildGatewayTxnRef(string $prefix = 'PAY'): string
    {
        return sprintf(
            '%s%s%s',
            Str::upper(Str::substr($prefix, 0, 3)),
            now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            Str::upper(Str::random(6))
        );
    }

    public function matchesAmount(Invoice $invoice, int|string $vnpAmount): bool
    {
        return (int) $vnpAmount === $this->toVnpAmount($invoice->total_amount);
    }

    public function matchesPaymentAmount(Payment $payment, int|string $vnpAmount): bool
    {
        return (int) $vnpAmount === $this->toVnpAmount($payment->amount);
    }

    private function buildPaymentUrl(float $amount, string $txnRef, string $orderInfo, array $options = []): string
    {
        $config = $this->config();
        $tmnCode = (string) ($config['tmn_code'] ?? '');
        $hashSecret = (string) ($config['hash_secret'] ?? '');
        $paymentUrl = (string) ($config['payment_url'] ?? '');

        if ($tmnCode === '' || $hashSecret === '' || $paymentUrl === '') {
            throw new InvalidArgumentException('VNPAY configuration is incomplete.');
        }

        $createdAt = Carbon::now('Asia/Ho_Chi_Minh');
        $expireAt = $createdAt->copy()->addMinutes((int) ($config['expire_minutes'] ?? 15));

        $inputData = array_filter([
            'vnp_Version' => $config['version'] ?? '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => $this->toVnpAmount($amount),
            'vnp_CreateDate' => $createdAt->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $options['ip_addr'] ?? request()->ip() ?? '127.0.0.1',
            'vnp_Locale' => $options['locale'] ?? ($config['locale'] ?? 'vn'),
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => $config['order_type'] ?? 'other',
            'vnp_ReturnUrl' => $config['return_url'] ?? '',
            'vnp_ExpireDate' => $expireAt->format('YmdHis'),
            'vnp_TxnRef' => $txnRef,
            'vnp_BankCode' => $options['bank_code'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $query = $this->buildSignedQuery($inputData, $hashSecret);

        return rtrim($paymentUrl, '?').'?'.$query;
    }

    private function buildSignedQuery(array $inputData, string $hashSecret): string
    {
        ksort($inputData);

        $query = http_build_query($inputData);
        $secureHash = hash_hmac('sha512', $this->buildHashData($inputData), $hashSecret);

        return $query.'&vnp_SecureHash='.$secureHash;
    }

    private function buildHashData(array $inputData): string
    {
        ksort($inputData);

        return collect($inputData)
            ->map(fn ($value, $key) => urlencode((string) $key).'='.urlencode((string) $value))
            ->implode('&');
    }

    private function paymentOrderInfo(Payment $payment): string
    {
        if ($payment->isDeposit()) {
            return $this->sanitizeOrderInfo(sprintf(
                'Thanh toan coc luot hen %s',
                $payment->booking_reference ?: 'booking'
            ));
        }

        return $this->sanitizeOrderInfo(sprintf(
            'Thanh toan hoa don %s',
            $payment->invoice_id ?: $payment->id
        ));
    }

    private function sanitizeOrderInfo(string $value): string
    {
        $normalized = Str::ascii($value);
        $normalized = preg_replace('/[^A-Za-z0-9\s:.-]/', ' ', $normalized) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function toVnpAmount(string|float|int $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function config(): array
    {
        return config('services.vnpay', []);
    }
}
