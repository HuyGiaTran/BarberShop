<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\LoyaltyProgram;
use App\Models\Payroll;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_tables_exist_after_migrations(): void
    {
        foreach ([
            'reviews',
            'invoices',
            'loyalty_programs',
            'leave_requests',
            'payrolls',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Failed asserting that table [{$table}] exists.");
        }
    }

    public function test_new_report_models_resolve_relationships(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Schema Barber',
            'phone' => '0900000010',
            'bio' => 'Schema relation test',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Schema Service',
            'description' => 'Schema service description',
            'price' => 150000,
            'duration_minutes' => 45,
            'barber_id' => $barber->id,
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'confirmed',
            'notes' => 'Schema appointment',
        ]);

        $review = Review::create([
            'user_id' => $customer->id,
            'barber_id' => $barber->id,
            'appointment_id' => $appointment->id,
            'rating' => 5,
            'comment' => 'Excellent service',
        ]);

        $invoice = Invoice::create([
            'appointment_id' => $appointment->id,
            'user_id' => $customer->id,
            'total_amount' => 150000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_id' => 'TXN-001',
        ]);

        $loyaltyProgram = LoyaltyProgram::create([
            'user_id' => $customer->id,
            'points' => 120,
            'tier' => 'silver',
        ]);

        $leaveRequest = LeaveRequest::create([
            'barber_id' => $barber->id,
            'recipient' => 'Manager',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDay()->toDateString(),
            'reason' => 'Family event',
            'handover_person' => 'Barber B',
            'commitment' => 'Handed over schedule',
            'status' => 'pending',
        ]);

        $payroll = Payroll::create([
            'barber_id' => $barber->id,
            'month' => now()->format('Y-m'),
            'base_salary' => 5000000,
            'commission' => 750000,
            'total_appointments' => 18,
            'total_amount' => 5750000,
            'status' => 'draft',
        ]);

        $this->assertTrue($review->user->is($customer));
        $this->assertTrue($review->barber->is($barber));
        $this->assertTrue($review->appointment->is($appointment));

        $this->assertTrue($invoice->user->is($customer));
        $this->assertTrue($invoice->appointment->is($appointment));

        $this->assertTrue($loyaltyProgram->user->is($customer));
        $this->assertTrue($leaveRequest->barber->is($barber));
        $this->assertTrue($payroll->barber->is($barber));

        $this->assertCount(1, $customer->reviews);
        $this->assertCount(1, $barber->leaveRequests);
        $this->assertTrue($appointment->invoice->is($invoice));
    }
}
