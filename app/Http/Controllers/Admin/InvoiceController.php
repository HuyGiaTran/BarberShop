<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['user', 'appointment.barber', 'appointment.service']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', (string) $request->string('payment_status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', (string) $request->string('payment_method'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $query->where(function ($subQuery) use ($keyword) {
                if (ctype_digit($keyword)) {
                    $subQuery->orWhere('id', (int) $keyword)
                        ->orWhere('appointment_id', (int) $keyword);
                }

                $subQuery->orWhere('transaction_id', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('appointment.service', function ($serviceQuery) use ($keyword) {
                        $serviceQuery->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('appointment.barber', function ($barberQuery) use ($keyword) {
                        $barberQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $invoices = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function markCashPaid(Invoice $invoice): RedirectResponse
    {
        $invoice->loadMissing('appointment');

        if (! $invoice->appointment || $invoice->appointment->status !== 'completed') {
            return back()->with('error', 'Chi co the thu tien khi lich hen da hoan thanh.');
        }

        if ($invoice->payment_status === 'paid' && $invoice->payment_method === 'cash') {
            return back()->with('success', 'Hoa don nay da duoc danh dau da thu tien mat.');
        }

        if ($invoice->payment_status === 'paid' && $invoice->payment_method === 'vnpay') {
            return back()->with('error', 'Hoa don nay da duoc xac nhan thanh toan qua VNPAY.');
        }

        $invoice->update([
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_id' => null,
        ]);

        return back()->with('success', 'Da cap nhat hoa don sang trang thai da thu tien mat.');
    }
}
