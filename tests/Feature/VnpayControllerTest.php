<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VnpayControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.vnpay', [
            'tmn_code' => 'TESTCODE',
            'hash_secret' => 'SECRETKEY123456',
            'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'return_url' => 'http://localhost/api/vnpay/callback',
            'ipn_url' => 'http://localhost/api/vnpay/ipn',
            'version' => '2.1.0',
            'order_type' => 'other',
            'locale' => 'vn',
            'expire_minutes' => 15,
        ]);
    }

    public function test_customer_can_create_vnpay_payment_url_for_own_invoice(): void
    {
        [$customer, $invoice] = $this->createInvoiceContext();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/vnpay/create-payment', [
            'invoice_id' => $invoice->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $paymentUrl = $response->json('data.payment_url');

        $this->assertIsString($paymentUrl);
        $this->assertStringContainsString('sandbox.vnpayment.vn', $paymentUrl);
        $this->assertStringContainsString('vnp_TmnCode=TESTCODE', $paymentUrl);

        $invoice->refresh();
        $this->assertSame('vnpay', $invoice->payment_method);
        $this->assertSame('unpaid', $invoice->payment_status);
    }

    public function test_callback_returns_result_view_for_valid_vnpay_response(): void
    {
        [, $invoice] = $this->createInvoiceContext();

        $payload = $this->makeSignedPayload($invoice, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => 'VN123456',
        ]);

        $response = $this->get('/api/vnpay/callback?'.http_build_query($payload));

        $response->assertOk()
            ->assertSee('Giao dịch đã được xác thực')
            ->assertSee((string) $invoice->id)
            ->assertSee('VN123456');
    }

    public function test_ipn_marks_invoice_as_paid_after_successful_vnpay_confirmation(): void
    {
        [, $invoice] = $this->createInvoiceContext();

        $payload = $this->makeSignedPayload($invoice, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => 'VN654321',
        ]);

        $response = $this->getJson('/api/vnpay/ipn?'.http_build_query($payload));

        $response->assertOk()
            ->assertJson([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'transaction_id' => 'VN654321',
        ]);
    }

    private function createInvoiceContext(): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber VNPAY',
            'phone' => '0900000099',
            'bio' => 'Test thanh toán online',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Combo thanh toán online',
            'description' => 'Tạo dữ liệu VNPAY test',
            'price' => 199000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '15:00',
            'status' => 'completed',
            'notes' => 'Dùng để test VNPAY',
        ]);

        $invoice = $appointment->invoice()->firstOrFail();

        return [$customer, $invoice];
    }

    private function makeSignedPayload(Invoice $invoice, array $overrides = []): array
    {
        $paymentUrl = app(PaymentService::class)->createPaymentUrl($invoice, [
            'ip_addr' => '127.0.0.1',
        ]);

        parse_str((string) parse_url($paymentUrl, PHP_URL_QUERY), $payload);

        $payload = array_merge($payload, $overrides);
        unset($payload['vnp_SecureHash'], $payload['vnp_SecureHashType']);

        $vnpData = collect($payload)
            ->filter(fn ($value, $key) => str_starts_with((string) $key, 'vnp_'))
            ->all();

        ksort($vnpData);

        $hashData = collect($vnpData)
            ->map(fn ($value, $key) => urlencode((string) $key).'='.urlencode((string) $value))
            ->implode('&');

        $payload['vnp_SecureHash'] = hash_hmac(
            'sha512',
            $hashData,
            (string) config('services.vnpay.hash_secret')
        );

        return $payload;
    }
}
