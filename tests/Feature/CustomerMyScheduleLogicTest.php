<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMyScheduleLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.deposit_transfer', [
            'bank_bin' => '970422',
            'bank_name' => 'MBBank',
            'bank_account' => '1234567890',
            'account_name' => 'BARBER SHOP DEMO',
            'qr_template' => 'compact2',
        ]);
    }

    public function test_customer_can_open_qr_deposit_page_and_submit_transfer_for_confirmation(): void
    {
        [$customer, $primary, $secondary] = $this->createGroupedBooking();

        $this->actingAs($customer)
            ->get(route('customer.appointments.deposit', $primary))
            ->assertOk()
            ->assertSee('Mã QR chuyển khoản đặt cọc')
            ->assertSee('COC '.$primary->resolvedBookingReference());

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('deposit', $payment->payment_type);
        $this->assertSame('bank_transfer', $payment->gateway);
        $this->assertSame('pending', $payment->status);
        $this->assertSame($primary->resolvedBookingReference(), $payment->booking_reference);

        $response = $this->actingAs($customer)
            ->post(route('customer.appointments.processDeposit', $primary));

        $payment->refresh();
        $primary->refresh();
        $secondary->refresh();

        $response->assertRedirect(route('customer.appointments.show', $primary));
        $this->assertSame('awaiting_confirmation', $payment->status);
        $this->assertSame('pending', $primary->status);
        $this->assertSame('pending', $secondary->status);
        $this->assertSame('awaiting_confirmation', $primary->deposit_status);
        $this->assertSame('awaiting_confirmation', $secondary->deposit_status);

        $cancelResponse = $this->actingAs($customer)
            ->from(route('customer.appointments.show', $primary))
            ->delete(route('customer.appointments.cancel', $primary));

        $cancelResponse->assertRedirect(route('customer.appointments.show', $primary));
        $cancelResponse->assertSessionHas('error');
    }

    public function test_admin_can_confirm_qr_deposit_and_lock_booking_from_cancellation(): void
    {
        [$customer, $primary, $secondary] = $this->createGroupedBooking();

        $this->actingAs($customer)->get(route('customer.appointments.deposit', $primary));
        $payment = Payment::query()->firstOrFail();

        $this->actingAs($customer)
            ->post(route('customer.appointments.processDeposit', $primary));

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->from(route('admin.payments.index'))
            ->actingAs($admin)
            ->patch(route('admin.payments.confirmDeposit', $payment), [
                'transaction_reference' => 'BANK-DEP-001',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $payment->refresh();
        $primary->refresh();
        $secondary->refresh();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('confirmed', $primary->status);
        $this->assertSame('confirmed', $secondary->status);
        $this->assertSame('paid', $primary->deposit_status);
        $this->assertSame('paid', $secondary->deposit_status);
        $this->assertNotNull($primary->deposit_paid_at);
        $this->assertSame('BANK-DEP-001', $primary->deposit_transaction_id);

        $cancelResponse = $this->actingAs($customer)
            ->from(route('customer.appointments.show', $primary))
            ->delete(route('customer.appointments.cancel', $primary));

        $cancelResponse->assertRedirect(route('customer.appointments.show', $primary));
        $cancelResponse->assertSessionHas('error');

        $this->assertSame('confirmed', $primary->fresh()->status);
        $this->assertSame('confirmed', $secondary->fresh()->status);
    }

    public function test_customer_can_cancel_unpaid_booking_group(): void
    {
        [$customer, $primary, $secondary] = $this->createGroupedBooking(status: 'confirmed');
        $bookingReference = $primary->resolvedBookingReference();

        $response = $this->actingAs($customer)
            ->delete(route('customer.appointments.cancel', $primary));

        $response->assertRedirect(route('customer.appointments.index'));

        $this->assertSame('cancelled', $primary->fresh()->status);
        $this->assertSame('cancelled', $secondary->fresh()->status);
        $this->assertStringContainsString('Khách hàng đã hủy lịch', $primary->fresh()->notes ?? '');

        $this->actingAs($customer)
            ->get(route('customer.appointments.index'))
            ->assertOk()
            ->assertViewHas('bookings', fn ($bookings) => $bookings->getCollection()->doesntContain(
                fn (array $booking) => $booking['reference'] === $bookingReference
            ));

        $this->actingAs($customer)
            ->get('/')
            ->assertOk()
            ->assertViewHas('myAppointments', fn ($appointments) => $appointments->doesntContain(
                fn (Appointment $appointment) => $appointment->resolvedBookingReference() === $bookingReference
            ));
    }

    public function test_non_customer_role_cannot_access_customer_schedule_routes(): void
    {
        [$customer, $primary] = $this->createGroupedBooking();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('customer.appointments.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('customer.appointments.show', $primary))
            ->assertForbidden();
    }

    public function test_classic_service_trio_is_displayed_as_combo_in_customer_views(): void
    {
        [$customer, $primary] = $this->createClassicComboBooking();

        $this->actingAs($customer)
            ->get(route('customer.appointments.index'))
            ->assertOk()
            ->assertSee('Combo Cắt tóc + Gội đầu + Cạo mặt')
            ->assertSee('Bao gồm: Cắt tóc, Gội đầu, Cạo mặt');

        $this->actingAs($customer)
            ->get(route('customer.appointments.show', $primary))
            ->assertOk()
            ->assertSee('Combo Cắt tóc + Gội đầu + Cạo mặt')
            ->assertSee('Chi tiết dịch vụ');

        $this->actingAs($customer)
            ->get('/')
            ->assertOk()
            ->assertSee('Combo Cắt tóc + Gội đầu + Cạo mặt');
    }

    public function test_admin_can_reject_qr_deposit_and_restore_unpaid_state(): void
    {
        [$customer, $primary, $secondary] = $this->createGroupedBooking();

        $this->actingAs($customer)->get(route('customer.appointments.deposit', $primary));
        $payment = Payment::query()->firstOrFail();

        $this->actingAs($customer)
            ->post(route('customer.appointments.processDeposit', $primary));

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->from(route('admin.payments.index'))
            ->actingAs($admin)
            ->patch(route('admin.payments.rejectDeposit', $payment), [
                'reason' => 'Chưa thấy giao dịch trên app ngân hàng',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $payment->refresh();
        $primary->refresh();
        $secondary->refresh();

        $this->assertSame('failed', $payment->status);
        $this->assertSame('unpaid', $primary->deposit_status);
        $this->assertSame('unpaid', $secondary->deposit_status);
        $this->assertNull($primary->deposit_paid_at);
        $this->assertNull($primary->deposit_transaction_id);
    }

    /**
     * @return array{0: User, 1: Appointment, 2: Appointment}
     */
    private function createGroupedBooking(string $status = 'pending'): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Schedule Barber',
            'phone' => '0900000055',
            'bio' => 'Customer schedule test',
            'is_active' => true,
        ]);

        $serviceA = Service::create([
            'name' => 'Cắt tóc',
            'description' => 'Dịch vụ A',
            'price' => 120000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $serviceB = Service::create([
            'name' => 'Gội đầu',
            'description' => 'Dịch vụ B',
            'price' => 40000,
            'duration_minutes' => 20,
            'barber_id' => $barber->id,
        ]);

        $bookingReference = 'BKG-TEST-001';
        $appointmentDate = now()->addDay()->toDateString();

        $primary = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $serviceA->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '09:00',
            'status' => $status,
            'deposit_amount' => 50000,
            'deposit_status' => 'unpaid',
        ]);

        $secondary = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $serviceB->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => 2,
            'is_booking_primary' => false,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '09:45',
            'status' => $status,
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        return [$customer, $primary, $secondary];
    }

    /**
     * @return array{0: User, 1: Appointment, 2: Appointment, 3: Appointment}
     */
    private function createClassicComboBooking(string $status = 'pending'): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Combo Barber',
            'phone' => '0900000077',
            'bio' => 'Combo test barber',
            'is_active' => true,
        ]);

        $haircut = Service::create([
            'name' => 'Cắt tóc',
            'description' => 'Combo item 1',
            'price' => 120000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $shampoo = Service::create([
            'name' => 'Gội đầu',
            'description' => 'Combo item 2',
            'price' => 40000,
            'duration_minutes' => 20,
            'barber_id' => $barber->id,
        ]);

        $shave = Service::create([
            'name' => 'Cạo mặt',
            'description' => 'Combo item 3',
            'price' => 30000,
            'duration_minutes' => 15,
            'barber_id' => $barber->id,
        ]);

        $bookingReference = 'BKG-COMBO-001';
        $appointmentDate = now()->addDays(2)->toDateString();

        $primary = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $haircut->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
            'status' => $status,
            'deposit_amount' => 50000,
            'deposit_status' => 'unpaid',
        ]);

        $secondary = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shampoo->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => 2,
            'is_booking_primary' => false,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:45',
            'status' => $status,
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        $tertiary = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shave->id,
            'booking_reference' => $bookingReference,
            'booking_sequence' => 3,
            'is_booking_primary' => false,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '11:05',
            'status' => $status,
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        return [$customer, $primary, $secondary, $tertiary];
    }
}
