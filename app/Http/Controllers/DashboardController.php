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
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $totalBarbers = Barber::count();
        $totalServices = Service::count();

        $recentAppointments = Appointment::with(['user', 'barber', 'service'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->take(10)
            ->get();

        $days = collect(range(6, 0))
            ->map(fn (int $daysAgo) => Carbon::today()->subDays($daysAgo));

        $appointmentsByDay = Appointment::whereBetween('appointment_date', [
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
        $statusCounts = Appointment::selectRaw('status, COUNT(*) as count')
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

        $popularServices = Service::withCount('appointments')
            ->orderByDesc('appointments_count')
            ->take(5)
            ->get();

        $popularServiceLabels = $popularServices->pluck('name')->values();
        $popularServiceData = $popularServices->pluck('appointments_count')->map(fn ($count) => (int) $count)->values();

        return view('dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'totalBarbers',
            'totalServices',
            'recentAppointments',
            'appointmentChartLabels',
            'appointmentChartData',
            'statusChartLabels',
            'statusChartData',
            'popularServiceLabels',
            'popularServiceData'
        ));
    }
}
