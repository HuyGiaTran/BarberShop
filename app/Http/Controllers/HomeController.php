<?php

namespace App\Http\Controllers;

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
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->role === 'barber') {
                return redirect()->route('barber.dashboard');
            }
            // Nếu là customer thì có thể ở lại trang chủ hoặc chuyển đi đâu đó tùy logic
            // return redirect()->route('customer.dashboard');
        }

        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        return view('home.index', compact('barbers', 'services'));
    }
}