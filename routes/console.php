<?php

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loyalty:degrade')->name('loyalty:degrade')->daily();

Schedule::call(function (): void {
    $now = Carbon::now(config('app.timezone'));

    Appointment::where('status', 'pending')
        ->where(function ($query) use ($now) {
            $query->where('appointment_date', '<', $now->toDateString())
                ->orWhere(function ($nestedQuery) use ($now) {
                    $nestedQuery->where('appointment_date', '=', $now->toDateString())
                        ->where('appointment_time', '<', $now->format('H:i:s'));
                });
        })
        ->update(['status' => 'cancelled']);
})->name('appointments:auto-cancel-pending')->everyMinute();
