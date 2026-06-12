<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoyaltyProgramFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_paid_invoice_creates_loyalty_points_and_does_not_duplicate_them(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        [$invoice, $customer] = $this->createInvoice(price: 1800000);

        $this->from(route('admin.invoices.index'))
            ->actingAs($admin)
            ->patch(route('admin.invoices.markCashPaid', $invoice))
            ->assertRedirect(route('admin.invoices.index'));

        $this->assertDatabaseHas('loyalty_programs', [
            'user_id' => $customer->id,
            'points' => 1800,
            'tier' => 'gold',
        ]);

        $this->assertDatabaseHas('loyalty_point_logs', [
            'user_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'source_type' => 'invoice_paid',
            'source_id' => $invoice->id,
            'points' => 1800,
            'balance_after' => 1800,
        ]);

        $this->from(route('admin.invoices.index'))
            ->actingAs($admin)
            ->patch(route('admin.invoices.markCashPaid', $invoice))
            ->assertRedirect(route('admin.invoices.index'));

        $this->assertDatabaseCount('loyalty_point_logs', 1);
    }

    public function test_loyalty_api_returns_progress_for_customer(): void
    {
        [$invoice, $customer] = $this->createInvoice(price: 550000);

        app(\App\Services\PaymentFlowService::class)->markInvoiceAsPaid($invoice, 'cash');

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/loyalty');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'points' => 550,
                    'tier' => 'silver',
                    'tier_label' => 'Silver',
                    'next_tier' => 'gold',
                ],
            ]);
    }

    /**
     * @return array{0: Invoice, 1: User}
     */
    private function createInvoice(int $price): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Loyalty Barber',
            'phone' => '0900000088',
            'bio' => 'Test loyalty',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Loyalty Service',
            'description' => 'Sinh điểm thành viên',
            'price' => $price,
            'duration_minutes' => 60,
            'barber_id' => $barber->id,
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '16:00',
            'status' => 'completed',
            'notes' => 'Tạo hóa đơn cho loyalty',
        ]);

        return [$appointment->invoice()->firstOrFail(), $customer];
    }
}
