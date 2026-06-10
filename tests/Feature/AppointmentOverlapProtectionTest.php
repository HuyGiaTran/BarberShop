<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\LeaveRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentOverlapProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rejects_overlapping_appointments_when_existing_service_is_longer_than_thirty_minutes(): void
    {
        [$customer, $barber, $longService, $shortService, $targetDate] = $this->seedOverlapScenario();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shortService->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '10:30',
            'status' => 'pending',
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('success', false);

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_admin_rejects_overlapping_appointments_when_existing_service_runs_past_start_time(): void
    {
        [$customer, $barber, $longService, $shortService, $targetDate] = $this->seedOverlapScenario();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), [
                'user_id' => $customer->id,
                'barber_id' => $barber->id,
                'service_id' => $shortService->id,
                'appointment_date' => $targetDate,
                'appointment_time' => '10:30',
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('admin.appointments.create'));
        $response->assertSessionHasErrors('appointment_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_api_rejects_appointments_when_barber_has_approved_leave_request(): void
    {
        [$customer, $barber, $longService, $shortService, $targetDate] = $this->seedOverlapScenario();

        LeaveRequest::create([
            'barber_id' => $barber->id,
            'recipient' => 'Manager',
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'reason' => 'Nghi phep',
            'status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shortService->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '14:00',
            'status' => 'pending',
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('error', 'barber_on_leave');
    }

    public function test_admin_rejects_appointments_when_barber_has_approved_leave_request(): void
    {
        [$customer, $barber, $longService, $shortService, $targetDate] = $this->seedOverlapScenario();

        LeaveRequest::create([
            'barber_id' => $barber->id,
            'recipient' => 'Manager',
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'reason' => 'Nghi phep',
            'status' => 'approved',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), [
                'user_id' => $customer->id,
                'barber_id' => $barber->id,
                'service_id' => $shortService->id,
                'appointment_date' => $targetDate,
                'appointment_time' => '14:00',
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('admin.appointments.create'));
        $response->assertSessionHasErrors('appointment_date');
    }

    /**
     * @return array{0: User, 1: Barber, 2: Service, 3: Service, 4: string}
     */
    private function seedOverlapScenario(): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Overlap Barber',
            'phone' => '0900000020',
            'bio' => 'Overlap test',
            'is_active' => true,
        ]);

        $longService = Service::create([
            'name' => 'Long Service',
            'description' => '60-minute service',
            'price' => 200000,
            'duration_minutes' => 60,
            'barber_id' => $barber->id,
        ]);

        $shortService = Service::create([
            'name' => 'Short Service',
            'description' => '30-minute service',
            'price' => 100000,
            'duration_minutes' => 30,
            'barber_id' => $barber->id,
        ]);

        $targetDate = now()->addDay()->toDateString();

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $longService->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '10:00',
            'status' => 'confirmed',
            'notes' => 'Existing long appointment',
        ]);

        return [$customer, $barber, $longService, $shortService, $targetDate];
    }
}
