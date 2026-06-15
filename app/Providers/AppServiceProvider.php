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

        // Tự động tạo symlink storage nếu chưa có (fix lỗi ảnh avatar barber bị 404 do chưa link)
        if (!app()->runningInConsole() && !file_exists(public_path('storage'))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Ép Laravel luôn tạo link dạng https khi không phải là localhost (fix lỗi vỡ icon/font/ảnh do HTTP/HTTPS mixed content)
        if (!app()->runningInConsole()) {
            $host = request()->getHost();
            if (!in_array($host, ['127.0.0.1', 'localhost', '::1']) || config('app.env') === 'production') {
                URL::forceScheme('https');
            }
        }
    }
}
