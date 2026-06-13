<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatisticsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_statistics_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.statistics.index'))
            ->assertOk()
            ->assertSee('Biểu đồ thống kê')
            ->assertSee('revenueChart', false)
            ->assertSee('peakHoursChart', false)
            ->assertSee('servicesChart', false);
    }

    public function test_customer_cannot_access_statistics_page(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('admin.statistics.index'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }
}
