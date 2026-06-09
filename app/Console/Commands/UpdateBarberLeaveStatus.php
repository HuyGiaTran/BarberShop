<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveRequest;
use App\Models\Barber;

class UpdateBarberLeaveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barber:update-leave-status';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Automatically update barber status to "off" when leave starts and back to "active" when it ends';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // Cập nhật trạng thái thành "off" khi thời gian bắt đầu đạt
        LeaveRequest::where('status', 'approved')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->get()
            ->each(function ($leave) {
                if ($leave->barber && $leave->barber->working_status !== 'off') {
                    $leave->barber->update(['working_status' => 'off']);
                    $this->info("Barber {$leave->barber->name} status updated to 'off' for leave starting {$leave->start_time->format('d/m/Y H:i')}");
                }
            });

        // Cập nhật trạng thái về "active" khi thời gian kết thúc
        LeaveRequest::where('status', 'approved')
            ->where('end_time', '<=', $now)
            ->get()
            ->each(function ($leave) {
                if ($leave->barber && $leave->barber->working_status === 'off') {
                    $leave->barber->update(['working_status' => 'active']);
                    $this->info("Barber {$leave->barber->name} status updated back to 'active' after leave ended {$leave->end_time->format('d/m/Y H:i')}");
                }
            });

        $this->info('Leave status update completed.');
    }
}
