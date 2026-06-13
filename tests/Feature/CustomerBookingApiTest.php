<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_web_user_can_create_appointment_through_api(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Test',
            'phone' => '0900000001',
            'bio' => 'Test barber',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Cắt tóc',
            'description' => 'Dịch vụ test',
            'price' => 120000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $appointmentDate = now()->addDay()->toDateString();
        $this->createScheduleForDate($barber->id, $appointmentDate);

        $response = $this->actingAs($customer)->postJson('/api/appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_ids' => [$service->id],
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
            'status' => 'pending',
            'notes' => 'Đặt lịch từ test',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);
    }

    public function test_api_rejects_booking_when_barber_is_busy_even_if_schedule_exists(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Busy',
            'phone' => '0900000002',
            'bio' => 'Busy barber',
            'is_active' => true,
            'working_status' => 'busy',
        ]);

        $service = Service::create([
            'name' => 'Gội đầu',
            'description' => 'Dịch vụ test barber busy',
            'price' => 70000,
            'duration_minutes' => 30,
            'barber_id' => $barber->id,
        ]);

        $appointmentDate = now()->addDay()->toDateString();
        $this->createScheduleForDate($barber->id, $appointmentDate);

        $response = $this->actingAs($customer)->postJson('/api/appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_ids' => [$service->id],
            'appointment_date' => $appointmentDate,
            'appointment_time' => '09:00',
            'status' => 'pending',
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('error', 'barber_busy');
    }

    private function createScheduleForDate(int $barberId, string $date, string $startTime = '08:00', string $endTime = '18:00'): void
    {
        BarberSchedule::create([
            'barber_id' => $barberId,
            'day_of_week' => Carbon::parse($date)->dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_off' => false,
            'is_available' => true,
            'specific_date' => null,
        ]);
    }
}
