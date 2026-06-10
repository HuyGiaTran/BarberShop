<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with('barber');

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        } else {
            $query->where('month', Carbon::now()->format('Y-m'));
        }

        $payrolls = $query->orderBy('month', 'desc')->paginate(15)->withQueryString();

        return view('payrolls.index', compact('payrolls'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->month;
        $barbers = Barber::where('is_active', true)->get();

        $calculatedCount = 0;

        foreach ($barbers as $barber) {
            // Check if already calculated
            $existingPayroll = Payroll::where('barber_id', $barber->id)
                ->where('month', $month)
                ->first();

            if ($existingPayroll && $existingPayroll->status === 'paid') {
                continue; // Skip paid payrolls
            }

            // Get all completed appointments for the barber in the given month
            $appointments = Appointment::with('service')
                ->where('barber_id', $barber->id)
                ->where('status', 'completed')
                ->where('appointment_date', 'like', $month . '%')
                ->get();

            $totalAppointments = $appointments->count();
            
            if ($totalAppointments === 0 && !$existingPayroll) {
                continue; // Skip if no appointments and no existing payroll
            }

            $totalRevenue = $appointments->sum(function ($apt) {
                return $apt->service ? $apt->service->price : 0;
            });

            // Commission rule: 30% of total revenue (example rule)
            $commission = $totalRevenue * 0.3;
            $baseSalary = 5000000; // Base salary (example 5M VND)

            $totalAmount = $baseSalary + $commission;

            Payroll::updateOrCreate(
                [
                    'barber_id' => $barber->id,
                    'month' => $month,
                ],
                [
                    'base_salary' => $baseSalary,
                    'commission' => $commission,
                    'total_appointments' => $totalAppointments,
                    'total_amount' => $totalAmount,
                    'status' => $existingPayroll ? $existingPayroll->status : 'pending',
                ]
            );

            $calculatedCount++;
        }

        return back()->with('success', "Đã chốt lương thành công cho $calculatedCount thợ trong tháng $month.");
    }

    public function markPaid(Payroll $payroll)
    {
        $payroll->update(['status' => 'paid']);
        return back()->with('success', 'Đã đánh dấu thanh toán lương thành công.');
    }
}
