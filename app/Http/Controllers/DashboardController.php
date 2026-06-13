<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonthStart = Carbon::today()->startOfMonth();
        $currentMonthEnd = Carbon::today()->endOfMonth();
        $dashboardMonthLabel = $currentMonthStart->format('m/Y');

        $totalAppointments = Appointment::query()->primaryBookings()->count();
        $pendingAppointments = Appointment::query()->primaryBookings()->where('status', 'pending')->count();
        $totalBarbers = Barber::count();
        $totalServices = Service::count();

        $recentAppointments = Appointment::query()
            ->primaryBookings()
            ->with(['user', 'barber', 'service'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->take(10)
            ->get();

        $recentBookingServices = Appointment::query()
            ->with('service:id,name')
            ->whereIn('booking_reference', $recentAppointments->pluck('booking_reference')->filter()->unique())
            ->orderBy('booking_sequence')
            ->get()
            ->groupBy('booking_reference');

        $recentAppointments->each(function (Appointment $appointment) use ($recentBookingServices): void {
            $bookingAppointments = $recentBookingServices->get($appointment->booking_reference) ?? collect([$appointment]);
            $comboLabel = Appointment::resolveComboLabelForServices($bookingAppointments);
            $serviceNames = $bookingAppointments->pluck('service.name')->filter()->unique()->values();

            $appointment->setAttribute(
                'display_service_name',
                $comboLabel
                    ?? ($serviceNames->count() === 1 ? (string) $serviceNames->first() : $serviceNames->implode(' + '))
            );
        });

        $days = collect(range(0, $currentMonthStart->daysInMonth - 1))
            ->map(fn (int $offset) => $currentMonthStart->copy()->addDays($offset));

        $appointmentsByDay = Appointment::query()
            ->primaryBookings()
            ->whereBetween('appointment_date', [
                $days->first()->toDateString(),
                $days->last()->toDateString(),
            ])
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $appointmentChartLabels = $days
            ->map(fn (Carbon $date) => $date->format('d/m'))
            ->values();

        $appointmentChartData = $days
            ->map(fn (Carbon $date) => (int) ($appointmentsByDay[$date->toDateString()] ?? 0))
            ->values();

        $statusOrder = ['pending', 'confirmed', 'completed', 'cancelled'];
        $statusCounts = Appointment::query()
            ->primaryBookings()
            ->whereBetween('appointment_date', [
                $currentMonthStart->toDateString(),
                $currentMonthEnd->toDateString(),
            ])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusChartLabels = collect($statusOrder)
            ->map(fn (string $status) => match ($status) {
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
                default => ucfirst($status),
            })
            ->values();

        $statusChartData = collect($statusOrder)
            ->map(fn (string $status) => (int) ($statusCounts[$status] ?? 0))
            ->values();

        $popularServices = Appointment::query()
            ->whereBetween('appointment_date', [
                $currentMonthStart->toDateString(),
                $currentMonthEnd->toDateString(),
            ])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('service_id, COUNT(*) as bookings_count')
            ->with('service:id,name')
            ->groupBy('service_id')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        $popularServiceLabels = $popularServices
            ->map(fn (Appointment $appointment) => $appointment->service?->name ?? 'N/A')
            ->values();
        $popularServiceData = $popularServices
            ->pluck('bookings_count')
            ->map(fn ($count) => (int) $count)
            ->values();

        return view('dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'totalBarbers',
            'totalServices',
            'recentAppointments',
            'dashboardMonthLabel',
            'appointmentChartLabels',
            'appointmentChartData',
            'statusChartLabels',
            'statusChartData',
            'popularServiceLabels',
            'popularServiceData'
        ));
    }
}
