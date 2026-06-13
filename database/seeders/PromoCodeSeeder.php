<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $promos = [
            [
                'code' => 'WELCOME10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => null,
                'max_discount' => 50000,
                'usage_limit' => 100,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'SALE50K',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 200000,
                'max_discount' => null,
                'usage_limit' => 50,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SUMMER20',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_order_amount' => 300000,
                'max_discount' => 100000,
                'usage_limit' => 200,
                'starts_at' => $now->copy()->addDays(7),
                'expires_at' => $now->copy()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'VIP15',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'min_order_amount' => null,
                'max_discount' => null,
                'usage_limit' => 0,
                'starts_at' => null,
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'HET_HAN',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'min_order_amount' => null,
                'max_discount' => null,
                'usage_limit' => 0,
                'starts_at' => $now->copy()->subMonths(3),
                'expires_at' => $now->copy()->subDay(),
                'is_active' => true,
            ],
            [
                'code' => 'TRI_AN',
                'discount_type' => 'fixed',
                'discount_value' => 20000,
                'min_order_amount' => 100000,
                'max_discount' => null,
                'usage_limit' => 10,
                'starts_at' => null,
                'expires_at' => $now->copy()->addMonth(),
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promo) {
            PromoCode::updateOrCreate(
                ['code' => $promo['code']],
                $promo
            );
        }

        $this->command->info('Đã tạo ' . count($promos) . ' mã giảm giá mẫu.');
    }
}