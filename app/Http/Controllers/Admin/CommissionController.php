<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Commission;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        $barbers = Barber::with(['commissions.service'])->get();
        $services = Service::all();

        return view('admin.commissions.index', compact('barbers', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        Commission::updateOrCreate(
            [
                'barber_id' => $validated['barber_id'],
                'service_id' => $validated['service_id'],
            ],
            [
                'commission_percent' => $validated['commission_percent'],
            ]
        );

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Cấu hình hoa hồng thành công!');
    }

    public function destroy(Commission $commission): RedirectResponse
    {
        $commission->delete();

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Đã xóa cấu hình hoa hồng.');
    }
}