<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tao tai khoan admin mac dinh de dang nhap dashboard.
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Quản Trị Viên',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Tao tai khoan khach hang rieng de test API auth.
        User::updateOrCreate(
            ['email' => 'api_test@gmail.com'],
            [
                'name' => 'API Tester',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        // Tao du lieu khach hang mau.
        $customerNames = [
            'Nguyễn Văn An', 'Trần Thị Bảo Ngọc', 'Lê Hoàng Hải', 'Phạm Đức Mạnh', 
            'Vũ Thu Quỳnh', 'Hoàng Thái Sơn', 'Đặng Minh Trí', 'Bùi Xuân Hùng', 
            'Đỗ Cẩm Tiên', 'Ngô Thanh Tùng'
        ];

        foreach ($customerNames as $index => $name) {
            $emailPrefix = 'khachhang' . ($index + 1);

            User::updateOrCreate(
                ['email' => $emailPrefix . '@gmail.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                ]
            );
        }

        $this->call([
            BarberSeeder::class,
            ServiceSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
