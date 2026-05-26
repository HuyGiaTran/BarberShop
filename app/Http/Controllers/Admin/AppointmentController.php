<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * 1. Danh sách lịch hẹn (Có bộ lọc theo ngày và trạng thái)
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['user', 'barber', 'service']);

        // Bộ lọc theo ngày
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Bộ lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
                             ->orderBy('appointment_time', 'asc')
                             ->get();

        return view('appointments.index', compact('appointments'));
    }

    /**
     * 2. Form đặt lịch mới
     */
    public function create()
    {
        // Truyền dữ liệu ra form chọn
        $users = User::where('role', 'customer')->get();
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        return view('appointments.create', compact('users', 'barbers', 'services'));
    }

    /**
     * 3. Lưu lịch hẹn (Kiểm tra trùng giờ phục vụ của Barber)
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ], [
            'user_id.required' => 'Vui lòng chọn khách hàng.',
            'barber_id.required' => 'Vui lòng chọn thợ cắt tóc.',
            'service_id.required' => 'Vui lòng chọn dịch vụ.',
            'appointment_date.required' => 'Vui lòng chọn ngày hẹn.',
            'appointment_date.after_or_equal' => 'Ngày hẹn không được nhỏ hơn ngày hôm nay.',
            'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',
        ]);

        // Logic chặn hoàn thành lịch hẹn trong tương lai
        if ($request->status === 'completed' && Carbon::parse($request->appointment_date)->isFuture()) {
            return back()->withInput()->withErrors(['status' => 'Không thể hoàn thành lịch hẹn trong tương lai.']);
        }

        // Kiểm tra xem Barber đã có lịch trùng khớp ngày giờ và không bị hủy chưa
        $isOverlapping = Appointment::where('barber_id', $request->barber_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isOverlapping) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Thợ cắt tóc này đã có lịch hẹn cố định vào khung giờ này. Vui lòng chọn giờ hoặc thợ khác.'
            ]);
        }

        // Tạo lịch hẹn mới
        Appointment::create([
            'user_id' => $request->user_id,
            'barber_id' => $request->barber_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Đặt lịch hẹn mới thành công!');
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    /**
     * 4. Chỉnh sửa lịch hẹn
     */
    public function edit(Appointment $appointment)
    {
        $users = User::where('role', 'customer')->get();
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::all();
        return view('appointments.edit', compact('appointment', 'users', 'barbers', 'services'));
    }

    /**
     * 5. Cập nhật lịch hẹn (Kiểm tra trùng giờ loại trừ chính nó)
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ], [
            'user_id.required' => 'Vui lòng chọn khách hàng.',
            'barber_id.required' => 'Vui lòng chọn thợ cắt tóc.',
            'service_id.required' => 'Vui lòng chọn dịch vụ.',
            'appointment_date.required' => 'Vui lòng chọn ngày hẹn.',
            'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',
        ]);

        // Logic chặn hoàn thành lịch hẹn trong tương lai
        if ($request->status === 'completed' && Carbon::parse($request->appointment_date)->isFuture()) {
            return back()->withInput()->withErrors(['status' => 'Không thể hoàn thành lịch hẹn trong tương lai.']);
        }

        // Kiểm tra trùng lịch hẹn của Barber (trừ chính bản ghi này)
        $isOverlapping = Appointment::where('barber_id', $request->barber_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($isOverlapping) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Thợ cắt tóc này đã có lịch hẹn cố định vào khung giờ này. Vui lòng chọn giờ khác.'
            ]);
        }

        // Cập nhật vào DB
        $appointment->update($request->all());

        return redirect()->route('appointments.index')->with('success', 'Cập nhật lịch hẹn thành công!');
    }

    /**
     * 6. Hủy / Xóa lịch hẹn
     */
    public function destroy(Appointment $appointment)
    {
        // Xóa lịch hẹn
        $appointment->delete();
        
        return redirect()->route('appointments.index')->with('success', 'Cập nhật lịch hẹn thành công!');
    }

    /**
     * 7. Cập nhật nhanh trạng thái (Xác nhận/Hủy lịch bằng 1 click)
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // API hoặc Form cập nhật nhanh trạng thái
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
        ]);

        // Logic chặn hoàn thành lịch hẹn trong tương lai
        if ($request->status === 'completed' && $appointment->appointment_date->isFuture()) {
            return back()->with('error', 'Không thể hoàn thành lịch hẹn trong tương lai!');
        }

        $appointment->update(['status' => $validated['status']]);

        return redirect()->route('appointments.index')->with('success', 'Cập nhật trạng thái lịch hẹn thành công!');
    }
}