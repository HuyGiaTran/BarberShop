<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed sample services for the barbershop.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Cat toc',
                'description' => 'Cat toc nam tieu chuan, tao kieu gon gang va de cham soc.',
                'price' => 50000,
                'duration_minutes' => 30,
            ],
            [
                'name' => 'Goi dau',
                'description' => 'Goi dau thu gian ket hop massage da dau nhe nhang.',
                'price' => 80000,
                'duration_minutes' => 45,
            ],
            [
                'name' => 'Cao mat',
                'description' => 'Cao mat, lay ray tai va cham soc da mat co ban.',
                'price' => 40000,
                'duration_minutes' => 20,
            ],
            [
                'name' => 'Combo',
                'description' => 'Combo cat toc, goi dau va cao mat tiet kiem chi phi.',
                'price' => 150000,
                'duration_minutes' => 60,
            ],
        ];

        foreach ($services as $item) {
            Service::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
