<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
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

        $response = $this->actingAs($customer)->postJson('/api/appointments', [
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
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
}
