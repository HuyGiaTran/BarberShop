<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ public
     */
    public function index()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        $myAppointments = collect();

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
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->take(10)
                ->get();
        }

        return view('home.index', compact('barbers', 'services', 'myAppointments'));
    }
}