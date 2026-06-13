<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentInvoiceObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_is_created_when_appointment_becomes_completed(): void
    {
        [$customer, $barber, $service] = $this->createBookingContext();

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'confirmed',
            'notes' => 'Chuẩn bị hoàn thành',
        ]);

        $appointment->update([
            'status' => 'completed',
        ]);

        $invoice = $appointment->fresh()->invoice;

        $this->assertNotNull($invoice);
        $this->assertSame($customer->id, $invoice->user_id);
        $this->assertSame('150000.00', $invoice->total_amount);
        $this->assertSame('cash', $invoice->payment_method);
        $this->assertSame('unpaid', $invoice->payment_status);
    }

    public function test_completed_appointment_only_generates_one_invoice(): void
    {
        [$customer, $barber, $service] = $this->createBookingContext();

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'completed',
            'notes' => 'Tạo invoice ngay khi khởi tạo',
        ]);

        $appointment->update([
            'notes' => 'Cập nhật ghi chú sau khi hoàn thành',
        ]);

        $this->assertSame(1, $appointment->fresh()->invoice()->count());
    }

    private function createBookingContext(): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Invoice',
            'phone' => '0901234567',
            'bio' => 'Tạo dữ liệu test hóa đơn',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Cắt tóc premium',
            'description' => 'Dịch vụ dùng để test observer',
            'price' => 150000,
            'duration_minutes' => 60,
            'barber_id' => $barber->id,
        ]);

        return [$customer, $barber, $service];
    }
}
