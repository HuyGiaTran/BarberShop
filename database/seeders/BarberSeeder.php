<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarberSeeder extends Seeder
{
    /**
     * Seed sample barbers and their linked user accounts.
     */
    public function run(): void
    {
        $barbers = [
            [
                'name' => 'Anh Tuan',
                'email' => 'tho1@gmail.com',
                'phone' => '0912345678',
                'bio' => 'Tho cat toc voi hon 5 nam kinh nghiem. The manh: Undercut, Mohican, Side Part.',
            ],
            [
                'name' => 'Hai Phong',
                'email' => 'tho2@gmail.com',
                'phone' => '0987654321',
                'bio' => 'Chuyen gia tao kieu toc hien dai, uon phong va nhuom mau thoi trang.',
            ],
            [
                'name' => 'Duy Thai',
                'email' => 'tho3@gmail.com',
                'phone' => '0934567890',
                'bio' => 'Co kinh nghiem cat toc, cao mat va tu van kieu toc phu hop cho tung khach hang.',
            ],
        ];

        foreach ($barbers as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'role' => 'barber',
                ]
            );

            Barber::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                    'bio' => $item['bio'],
                    'avatar' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
