<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ public
     */
    public function index()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        return view('home.index', compact('barbers', 'services'));
    }
}