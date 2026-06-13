<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {
    }

    /**
     * Hiển thị trang chủ public
     */
    public function index()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        $myAppointments = collect();
        $loyaltySummary = null;

        // Fetch top reviews for testimonials section
        $topReviews = \App\Models\Review::with(['user', 'barber'])
            ->where('rating', 5)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'barber') {
                return redirect()->route('barber.dashboard');
            }
            
            // Customer: lấy lịch hẹn của họ
            $myAppointments = Appointment::with(['barber', 'service'])
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->orderBy('booking_sequence')
                ->get()
                ->groupBy(fn (Appointment $appointment) => $appointment->resolvedBookingReference())
                ->map(function ($items) {
                    $appointments = $items->sortBy('booking_sequence')->values();
                    $primary = $appointments->firstWhere('is_booking_primary', true)
                        ?? $appointments->first();

                    if (! $primary) {
                        return null;
                    }

                    $serviceNames = $appointments
                        ->map(fn (Appointment $appointment) => $appointment->service?->name)
                        ->filter()
                        ->unique()
                        ->values();
                    $comboLabel = Appointment::resolveComboLabelForServices($appointments);

                    $primary->setAttribute(
                        'display_service_name',
                        $comboLabel
                            ?? ($serviceNames->count() === 1 ? (string) $serviceNames->first() : $serviceNames->implode(' + '))
                    );
                    $primary->setAttribute('combo_label', $comboLabel);
                    $primary->setAttribute('is_combo_booking', $comboLabel !== null);
                    $primary->setAttribute('booking_service_preview', $serviceNames->implode(', '));

                    return $primary;
                })
                ->filter()
                ->sortByDesc(fn (Appointment $appointment) => sprintf(
                    '%s %s',
                    $appointment->appointment_date?->format('Y-m-d') ?? '',
                    $appointment->appointment_time ?? ''
                ))
                ->take(10)
                ->values();

            $loyaltySummary = $this->loyaltyService->summaryForUser($user);
        }

        return view('home.index', compact('barbers', 'services', 'myAppointments', 'loyaltySummary', 'topReviews'));
    }
}
