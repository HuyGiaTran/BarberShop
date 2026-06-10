<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Models\LoyaltyProgram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddLoyaltyPoints
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $userId = $invoice->user_id;

        // 1.000 VNĐ = 1 point
        $pointsEarned = (int) ($invoice->total_amount / 1000);

        if ($pointsEarned > 0) {
            $loyalty = LoyaltyProgram::firstOrCreate(
                ['user_id' => $userId],
                ['points' => 0, 'tier' => 'bronze']
            );

            $loyalty->points += $pointsEarned;

            // Tier calculation
            // bronze: < 1000, silver: 1000 - 4999, gold: 5000 - 9999, diamond: >= 10000
            if ($loyalty->points >= 10000) {
                $loyalty->tier = 'diamond';
            } elseif ($loyalty->points >= 5000) {
                $loyalty->tier = 'gold';
            } elseif ($loyalty->points >= 1000) {
                $loyalty->tier = 'silver';
            }

            $loyalty->save();
        }
    }
}
