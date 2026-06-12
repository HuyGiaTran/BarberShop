<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_charts_count_primary_bookings_for_current_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-13 09:00:00'));

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Dashboard Barber',
            'phone' => '0901234567',
            'bio' => 'Dashboard metrics test',
            'is_active' => true,
        ]);

        $haircut = Service::create([
            'name' => 'Cắt tóc',
            'description' => 'Primary service',
            'price' => 120000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $shampoo = Service::create([
            'name' => 'Gội đầu',
            'description' => 'Secondary service',
            'price' => 40000,
            'duration_minutes' => 20,
            'barber_id' => $barber->id,
        ]);

        $shave = Service::create([
            'name' => 'Cạo mặt',
            'description' => 'Tertiary service',
            'price' => 30000,
            'duration_minutes' => 15,
            'barber_id' => $barber->id,
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $haircut->id,
            'booking_reference' => 'BKG-MAY-CANCELLED',
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => '2026-05-28',
            'appointment_time' => '08:30',
            'status' => 'cancelled',
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $haircut->id,
            'booking_reference' => 'BKG-JUNE-CANCELLED',
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:30',
            'status' => 'cancelled',
            'deposit_amount' => 50000,
            'deposit_status' => 'unpaid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shampoo->id,
            'booking_reference' => 'BKG-JUNE-CANCELLED',
            'booking_sequence' => 2,
            'is_booking_primary' => false,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '11:15',
            'status' => 'cancelled',
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shave->id,
            'booking_reference' => 'BKG-JUNE-CANCELLED',
            'booking_sequence' => 3,
            'is_booking_primary' => false,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '11:40',
            'status' => 'cancelled',
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $haircut->id,
            'booking_reference' => 'BKG-JUNE-COMPLETED',
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => '2026-06-12',
            'appointment_time' => '09:00',
            'status' => 'completed',
            'deposit_amount' => 50000,
            'deposit_status' => 'paid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $haircut->id,
            'booking_reference' => 'BKG-JUNE-CONFIRMED',
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => '2026-06-20',
            'appointment_time' => '13:00',
            'status' => 'confirmed',
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $shampoo->id,
            'booking_reference' => 'BKG-JUNE-PENDING',
            'booking_sequence' => 1,
            'is_booking_primary' => true,
            'appointment_date' => '2026-06-18',
            'appointment_time' => '15:30',
            'status' => 'pending',
            'deposit_amount' => 0,
            'deposit_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalAppointments', 5);
        $response->assertViewHas('pendingAppointments', 1);
        $response->assertViewHas('dashboardMonthLabel', '06/2026');
        $response->assertViewHas('recentAppointments', function ($appointments) {
            return $appointments->count() === 5
                && $appointments->every(fn (Appointment $appointment) => $appointment->is_booking_primary)
                && $appointments->contains(fn (Appointment $appointment) => $appointment->display_service_name === 'Combo Cắt tóc + Gội đầu + Cạo mặt');
        });
        $response->assertViewHas('statusChartData', function ($data) {
            return collect($data)->values()->all() === [1, 1, 1, 1];
        });
        $response->assertViewHas('appointmentChartData', function ($data) {
            $values = collect($data)->values();

            return $values->sum() === 4
                && $values->count() === 30
                && (int) $values[11] === 1
                && (int) $values[17] === 1
                && (int) $values[19] === 1
                && (int) $values[24] === 1;
        });
        $response->assertViewHas('popularServiceLabels', function ($labels) {
            return collect($labels)->values()->all() === ['Cắt tóc', 'Gội đầu'];
        });
        $response->assertViewHas('popularServiceData', function ($data) {
            return collect($data)->values()->all() === [2, 1];
        });
    }
}
