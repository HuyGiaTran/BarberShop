<?php

namespace App\Providers;

use App\Events\InvoicePaid;
use App\Listeners\AddLoyaltyPoints;
use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Event::listen(InvoicePaid::class, AddLoyaltyPoints::class);

        // Ép Laravel luôn tạo link dạng https khi chạy trên môi trường thật (Production)
        if (config('app.env') === 'production' || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}
