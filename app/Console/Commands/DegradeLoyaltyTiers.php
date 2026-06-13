<?php

namespace App\Console\Commands;

use App\Models\LoyaltyProgram;
use App\Services\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DegradeLoyaltyTiers extends Command
{
    protected $signature = 'loyalty:degrade';
    protected $description = 'Hạ bậc loyalty sau mỗi quý (3 tháng) không có hoạt động kể từ đầu năm';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $now = Carbon::now();
        $startOfYear = Carbon::create($now->year, 1, 1, 0, 0, 0); // 01/01 năm nay

        $degradedCount = 0;
        $programs = LoyaltyProgram::where('tier', '!=', 'bronze')->get();

        foreach ($programs as $program) {
            $lastActive = $program->updated_at;

            // Nếu chưa có hoạt động nào trong năm nay, mốc là đầu năm
            $referenceDate = $lastActive->greaterThan($startOfYear) ? $lastActive : $startOfYear;

            // Số quý đã trôi qua kể từ lần cuối hoạt động
            $quartersPassed = (int) $referenceDate->diffInMonths($now) / 3;

            if ($quartersPassed < 1) {
                continue; // Chưa đủ 1 quý, không hạ
            }

            // Mỗi quý hạ 1 bậc (tối đa xuống bronze)
            $tierOrder = ['platinum' => 3, 'gold' => 2, 'silver' => 1, 'bronze' => 0];
            $currentLevel = $tierOrder[$program->tier] ?? 0;

            if ($currentLevel <= 0) continue;

            $newLevel = max(0, $currentLevel - (int) $quartersPassed);
            $tierKeys = array_flip($tierOrder);

            $newTier = $tierKeys[$newLevel] ?? 'bronze';

            if ($newTier !== $program->tier) {
                $oldTier = $program->tier;
                $program->tier = $newTier;
                $program->save();

                $this->info("User #{$program->user_id}: {$oldTier} → {$newTier} (không hoạt động {$quartersPassed} quý)");
                $degradedCount++;
            }
        }

        $this->info("Đã hạ bậc {$degradedCount} khách hàng.");
        return Command::SUCCESS;
    }
}