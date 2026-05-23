<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = Barber::all();
        return view('barbers.index', compact('barbers'));
    }

    public function create()
    {
        return view('barbers.create');
    }

    public function store(Request $request)
    {
        // Sẽ được code bởi thành viên 2
    }

    public function show(Barber $barber)
    {
        return view('barbers.show', compact('barber'));
    }

    public function edit(Barber $barber)
    {
        return view('barbers.edit', compact('barber'));
    }

    public function update(Request $request, Barber $barber)
    {
        // Sẽ được code bởi thành viên 2
    }

    public function destroy(Barber $barber)
    {
        // Sẽ được code bởi thành viên 2
    }
}