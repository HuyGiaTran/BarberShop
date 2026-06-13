<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LoyaltyPointLog;
use App\Models\LoyaltyProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * @var array<int, array{key: string, label: string, min_points: int, discount: int, benefits: string}>
     */
    private const TIERS = [
        ['key' => 'bronze',   'label' => 'Đồng',    'min_points' => 0,   'discount' => 0,  'benefits' => 'Hạng mặc định khi đăng ký'],
        ['key' => 'silver',   'label' => 'Bạc',     'min_points' => 200, 'discount' => 5,  'benefits' => 'Giảm 5% cho lần cắt tiếp theo'],
        ['key' => 'gold',     'label' => 'Vàng',    'min_points' => 400, 'discount' => 10, 'benefits' => 'Giảm 10% + ưu tiên đặt lịch'],
        ['key' => 'platinum', 'label' => 'Bạch kim', 'min_points' => 700, 'discount' => 15, 'benefits' => 'Giảm 15% + ưu tiên + quà tặng sinh nhật'],
    ];

    public function ensureProgramForUser(User $user): LoyaltyProgram
    {
        return LoyaltyProgram::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0, 'tier' => self::TIERS[0]['key']]
        );
    }

    public function awardPointsForInvoice(Invoice $invoice): ?LoyaltyPointLog
    {
        return DB::transaction(function () use ($invoice): ?LoyaltyPointLog {
            $invoice->loadMissing('user');

            $existingLog = LoyaltyPointLog::where('source_type', 'invoice_paid')
                ->where('source_id', $invoice->id)
                ->first();

            if ($existingLog) {
                return $existingLog;
            }

            $pointsEarned = $this->calculatePoints((float) $invoice->total_amount);

            if ($pointsEarned <= 0 || ! $invoice->user) {
                return null;
            }

            $program = $this->ensureProgramForUser($invoice->user);
            $program->points += $pointsEarned;
            $program->tier = $this->resolveTierKey($program->points);
            $program->save();

            return LoyaltyPointLog::create([
                'user_id' => $invoice->user_id,
                'loyalty_program_id' => $program->id,
                'invoice_id' => $invoice->id,
                'source_type' => 'invoice_paid',
                'source_id' => $invoice->id,
                'points' => $pointsEarned,
                'balance_after' => $program->points,
                'note' => 'Cộng điểm thưởng từ lượt hẹn',
            ]);
        });
    }

    /**
     * @return array{
     *     points: int,
     *     tier: string,
     *     tier_label: string,
     *     next_tier: ?string,
     *     next_tier_label: ?string,
     *     points_to_next_tier: int,
     *     progress_percentage: int,
     *     recent_logs: array<int, array<string, mixed>>
     * }
     */
    public function summaryForUser(User $user): array
    {
        $program = $this->ensureProgramForUser($user)->loadMissing('user');
        $points = (int) $program->points;
        $tier = $program->tier ?: self::TIERS[0]['key'];
        $currentTier = collect(self::TIERS)->firstWhere('key', $tier) ?? self::TIERS[0];
        $nextTier = collect(self::TIERS)->first(fn (array $tierConfig) => $tierConfig['min_points'] > $points);

        $tierFloor = (int) $currentTier['min_points'];
        $tierCeiling = $nextTier['min_points'] ?? $points;
        $progressPercentage = $nextTier
            ? (int) round((($points - $tierFloor) / max(1, $tierCeiling - $tierFloor)) * 100)
            : 100;

        $recentLogs = LoyaltyPointLog::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (LoyaltyPointLog $log) => [
                'points' => $log->points,
                'balance_after' => $log->balance_after,
                'note' => $log->note,
                'created_at' => $log->created_at?->toISOString(),
            ])
            ->all();

        return [
            'points' => $points,
            'tier' => $tier,
            'tier_label' => (string) $currentTier['label'],
            'discount' => (int) $currentTier['discount'],
            'benefits' => (string) $currentTier['benefits'],
            'next_tier' => $nextTier['key'] ?? null,
            'next_tier_label' => $nextTier['label'] ?? null,
            'next_tier_discount' => $nextTier['discount'] ?? null,
            'next_tier_benefits' => $nextTier['benefits'] ?? null,
            'points_to_next_tier' => $nextTier ? max(0, (int) $nextTier['min_points'] - $points) : 0,
            'progress_percentage' => max(0, min(100, $progressPercentage)),
            'recent_logs' => $recentLogs,
        ];
    }

    public function calculatePoints(float $amount): int
    {
        return max(0, (int) floor($amount / 1000));
    }

    public function resolveTierKey(int $points): string
    {
        return collect(self::TIERS)
            ->reverse()
            ->first(fn (array $tier) => $points >= $tier['min_points'])['key'] ?? self::TIERS[0]['key'];
    }
}
