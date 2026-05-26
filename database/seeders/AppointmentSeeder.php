<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Seed sample appointments using existing customers, barbers and services.
     */
    public function run(): void
    {
        $customers = User::where('role', 'customer')
            ->where('email', 'like', 'khachhang%@gmail.com')
            ->get();
        $barbers = Barber::all();
        $services = Service::all();

        if ($customers->isEmpty() || $barbers->isEmpty() || $services->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $times = ['08:30', '09:00', '10:00', '13:30', '15:00', '16:30', '18:00', '19:30'];

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers[$i % $customers->count()];
            $barber = $barbers[$i % $barbers->count()];
            $service = $services[$i % $services->count()];
            $date = Carbon::now()->addDays($i - 2)->format('Y-m-d');
            $time = $times[$i % count($times)];

            Appointment::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'barber_id' => $barber->id,
                    'service_id' => $service->id,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                ],
                [
                    'status' => $statuses[$i % count($statuses)],
                    'notes' => $i % 2 === 0 ? 'Khach quen, uu tien cat ky phan gay.' : null,
                ]
            );
        }
    }
}
