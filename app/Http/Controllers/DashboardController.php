<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard tổng quan
     */
    public function index()
    {
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $totalBarbers = Barber::count();
        $totalServices = Service::count();

        $recentAppointments = Appointment::with(['user', 'barber', 'service'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'totalBarbers',
            'totalServices',
            'recentAppointments'
        ));
    }
}