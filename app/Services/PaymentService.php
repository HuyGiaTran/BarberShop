<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function createPaymentUrl(Invoice $invoice, array $options = []): string
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
        $txnRef = $this->buildTxnRef($invoice);
        $orderInfo = $this->sanitizeOrderInfo(sprintf(
            'Thanh toan hoa don %s cho lich hen %s',
            $invoice->id,
            $invoice->appointment_id
        ));

        $inputData = array_filter([
            'vnp_Version' => $config['version'] ?? '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => $this->toVnpAmount($invoice->total_amount),
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

        return rtrim($paymentUrl, '?').'?' . $query;
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

    public function buildTxnRef(Invoice $invoice): string
    {
        return sprintf('INV%d_%s', $invoice->id, now('Asia/Ho_Chi_Minh')->format('YmdHis'));
    }

    public function matchesAmount(Invoice $invoice, int|string $vnpAmount): bool
    {
        return (int) $vnpAmount === $this->toVnpAmount($invoice->total_amount);
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
