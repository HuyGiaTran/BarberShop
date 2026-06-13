<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\BarberSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BarberScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $barbers = Barber::all();

        if ($barbers->isEmpty()) {
            $this->command->warn('Không có Barber nào để tạo lịch làm việc.');
            return;
        }

        foreach ($barbers as $barber) {
            // Tạo lịch cho Thứ 2 -> Thứ 7 (day_of_week: 1-6), 08:00 - 19:30
            for ($dayOfWeek = 1; $dayOfWeek <= 6; $dayOfWeek++) {
                BarberSchedule::updateOrCreate(
                    [
                        'barber_id' => $barber->id,
                        'day_of_week' => $dayOfWeek,
                        'specific_date' => null,
                    ],
                    [
                        'start_time' => '08:00',
                        'end_time' => '22:00',
                        'is_off' => false,
                        'is_available' => true,
                    ]
                );
            }

            // Chủ nhật (day_of_week: 0) - nghỉ
            BarberSchedule::updateOrCreate(
                [
                    'barber_id' => $barber->id,
                    'day_of_week' => 0,
                    'specific_date' => null,
                ],
                [
                    'start_time' => '08:00',
                    'end_time' => '22:00',
                    'is_off' => true,
                    'is_available' => false,
                ]
            );
        }

        $this->command->info('Đã tạo lịch làm việc mặc định cho tất cả Barber (T2-T7: 08:00-22:00, CN: nghỉ)');
    }
}