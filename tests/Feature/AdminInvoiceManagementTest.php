<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_invoice_list_and_mark_cash_payment_as_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        [$invoice, $customer] = $this->createInvoice();

        $listResponse = $this->actingAs($admin)->get(route('admin.invoices.index'));

        $listResponse->assertOk()
            ->assertSee((string) $invoice->id)
            ->assertSee($customer->name)
            ->assertSee('Cắt tóc VIP');

        $markPaidResponse = $this->from(route('admin.invoices.index'))
            ->actingAs($admin)
            ->patch(route('admin.invoices.markCashPaid', $invoice));

        $markPaidResponse->assertRedirect(route('admin.invoices.index'));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_id' => null,
        ]);
    }

    public function test_admin_cannot_overwrite_paid_vnpay_invoice_with_cash_payment(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        [$invoice] = $this->createInvoice([
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'transaction_id' => 'VN999888',
        ]);

        $response = $this->from(route('admin.invoices.index'))
            ->actingAs($admin)
            ->patch(route('admin.invoices.markCashPaid', $invoice));

        $response->assertRedirect(route('admin.invoices.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'transaction_id' => 'VN999888',
        ]);
    }

    private function createInvoice(array $invoiceOverrides = []): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Thu Ngan',
            'phone' => '0900000011',
            'bio' => 'Phục vụ test admin invoice',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Cắt tóc VIP',
            'description' => 'Dùng để test màn hóa đơn',
            'price' => 220000,
            'duration_minutes' => 60,
            'barber_id' => $barber->id,
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '13:00',
            'status' => 'completed',
            'notes' => 'Sinh invoice tự động',
        ]);

        $invoice = $appointment->invoice()->firstOrFail();

        if ($invoiceOverrides !== []) {
            $invoice->update($invoiceOverrides);
            $invoice->refresh();
        }

        return [$invoice, $customer];
    }
}
