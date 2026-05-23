<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['user', 'barber', 'service'])->get();
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        return view('appointments.create');
    }

    public function store(Request $request)
    {
        // Sẽ được code bởi thành viên 4
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Sẽ được code bởi thành viên 4
    }

    public function destroy(Appointment $appointment)
    {
        // Sẽ được code bởi thành viên 4
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        // Sẽ được code bởi thành viên 4
    }
}