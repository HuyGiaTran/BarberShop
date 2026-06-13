<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberSchedule;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with('barber', 'barber.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('leave_requests.index', compact('leaveRequests'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('barber', 'barber.user');

        $conflictingAppointments = collect();

        if ($leaveRequest->start_time && $leaveRequest->end_time) {
            $conflictingAppointments = Appointment::where('barber_id', $leaveRequest->barber_id)
                ->whereBetween('appointment_date', [
                    $leaveRequest->start_time->toDateString(),
                    $leaveRequest->end_time->toDateString()
                ])
                ->get()
                ->filter(function ($apt) use ($leaveRequest) {
                    $aptDateTime = $apt->appointment_date . ' ' . $apt->appointment_time;
                    return strtotime($aptDateTime) >= strtotime($leaveRequest->start_time) &&
                           strtotime($aptDateTime) < strtotime($leaveRequest->end_time);
                });
        }

        return view('admin.leave-requests.show', compact('leaveRequest', 'conflictingAppointments'));
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt đơn đang chờ xét duyệt.');
        }

        $leaveRequest->update(['status' => 'approved']);

        $transferredCount = 0;
        $notFoundMessage = '';

        // Chuyển lịch hẹn trùng (chỉ chuyển các lịch chưa hoàn thành)
        if ($leaveRequest->start_time && $leaveRequest->end_time) {
            $conflictingAppointments = Appointment::where('barber_id', $leaveRequest->barber_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('appointment_date', [
                    $leaveRequest->start_time->toDateString(),
                    $leaveRequest->end_time->toDateString()
                ])
                ->get()
                ->filter(function ($apt) use ($leaveRequest) {
                    $aptDateTime = $apt->appointment_date . ' ' . $apt->appointment_time;
                    return strtotime($aptDateTime) >= strtotime($leaveRequest->start_time) &&
                           strtotime($aptDateTime) < strtotime($leaveRequest->end_time);
                });

            $handoverBarber = Barber::where('name', $leaveRequest->handover_person)->first();

            if ($handoverBarber) {
                foreach ($conflictingAppointments as $apt) {
                    $apt->update([
                        'barber_id' => $handoverBarber->id,
                        'notes' => ($apt->notes ? $apt->notes . "\n" : '') .
                            "Chuyển từ {$leaveRequest->barber->name} do nghỉ phép {$leaveRequest->start_time->format('d/m/Y H:i')}→{$leaveRequest->end_time->format('d/m/Y H:i')}"
                    ]);
                    $transferredCount++;
                }
            } else {
                $notFoundMessage = " (Không tìm thấy barber '{$leaveRequest->handover_person}')";
            }
        }

        // Block schedule - tự động chặn khung giờ
        BarberSchedule::blockForLeave($leaveRequest);

        // Cập nhật trạng thái barber
        if (now() >= $leaveRequest->start_time) {
            $leaveRequest->barber->update(['working_status' => 'off']);
        }

        $message = 'Đã duyệt đơn nghỉ phép.';
        if ($transferredCount > 0) {
            $message .= " Đã chuyển {$transferredCount} lịch hẹn sang {$leaveRequest->handover_person}.";
        }
        $message .= ' Các khung giờ đã được khóa trên lịch làm việc.';

        return redirect()->route('admin.leave_requests.index')
            ->with('success', $message);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể từ chối đơn đang chờ xét duyệt.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('admin.leave_requests.index')
            ->with('success', 'Đã từ chối đơn nghỉ phép.');
    }
}