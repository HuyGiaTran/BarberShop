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

class AppointmentSlotsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_slots_endpoint_returns_available_times_only(): void
    {
        $authenticatedUser = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($authenticatedUser);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Slot',
            'phone' => '0900000004',
            'bio' => 'Slots test',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Shave',
            'description' => 'Service for slot test',
            'price' => 80000,
            'duration_minutes' => 60,
            'barber_id' => $barber->id,
        ]);

        $targetDate = now()->addDay()->toDateString();

        Appointment::create([
            'user_id' => $authenticatedUser->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '10:00',
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'user_id' => $authenticatedUser->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '11:30',
            'status' => 'cancelled',
        ]);

        Appointment::create([
            'user_id' => $authenticatedUser->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => $targetDate,
            'appointment_time' => '12:00',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/barbers/{$barber->id}/slots?date={$targetDate}");

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $slots = $response->json('data');

        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('10:30', $slots);
        $this->assertContains('11:30', $slots);
        $this->assertNotContains('12:00', $slots);
        $this->assertContains('08:00', $slots);
    }

    public function test_slots_endpoint_returns_empty_when_barber_is_on_approved_leave(): void
    {
        $authenticatedUser = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($authenticatedUser);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Leave',
            'phone' => '0900000005',
            'bio' => 'Leave slots test',
            'is_active' => true,
        ]);

        $targetDate = now()->addDays(2)->toDateString();

        LeaveRequest::create([
            'barber_id' => $barber->id,
            'recipient' => 'Manager',
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'reason' => 'Annual leave',
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/barbers/{$barber->id}/slots?date={$targetDate}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('meta.is_on_leave', true);
        $this->assertSame([], $response->json('data'));
    }
}
