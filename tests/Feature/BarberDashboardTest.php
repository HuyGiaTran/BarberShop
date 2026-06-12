<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarberDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_dashboard_shows_real_appointments_for_today(): void
    {
        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Dashboard',
            'phone' => '0900000002',
            'bio' => 'Dashboard test',
            'is_active' => true,
        ]);

        $customerA = User::factory()->create([
            'name' => 'Nguyen Van A',
            'role' => 'customer',
        ]);

        $customerB = User::factory()->create([
            'name' => 'Tran Thi B',
            'role' => 'customer',
        ]);

        $service = Service::create([
            'name' => 'Cat toc nam',
            'description' => 'Dich vu test',
            'price' => 100000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        Appointment::create([
            'user_id' => $customerA->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'pending',
            'notes' => 'Khach den som',
        ]);

        Appointment::create([
            'user_id' => $customerB->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:30',
            'status' => 'completed',
            'notes' => 'Da xong',
        ]);

        $response = $this->actingAs($barberUser)->get(route('barber.dashboard'));

        $response->assertOk();
        $response->assertSeeText('2');
        $response->assertSeeText('Lịch hẹn hôm nay');
        $response->assertSeeText('1');
        $response->assertSeeText('Hoàn thành hôm nay');
        $response->assertSeeText('Nguyen Van A');
        $response->assertSeeText('Tran Thi B');
        $response->assertSeeText('Cat toc nam');
    }

    public function test_barber_can_toggle_status_from_dashboard(): void
    {
        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Toggle',
            'phone' => '0900000003',
            'bio' => 'Toggle test',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($barberUser)
            ->from(route('barber.dashboard'))
            ->patch(route('barber.status.update'), [
                'working_status' => 'busy',
            ]);

        $response->assertRedirect(route('barber.dashboard'));

        $barber->refresh();

        $this->assertSame('busy', $barber->working_status);
    }
}
