<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Appointment;
use App\Models\Barber;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Hiển thị danh sách đơn xin nghỉ phép
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::with('barber', 'barber.user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Hiển thị chi tiết đơn xin nghỉ phép
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('barber', 'barber.user');

        // Tìm các lịch hẹn trùng với thời gian nghỉ
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

        return view('admin.leave-requests.show', compact('leaveRequest', 'conflictingAppointments'));
    }

    /**
     * Duyệt đơn xin nghỉ phép
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt đơn đang chờ xét duyệt.');
        }

        // Cập nhật trạng thái đơn
        $leaveRequest->update(['status' => 'approved']);

        // Tìm các lịch hẹn trùng với thời gian nghỉ
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

        $transferredCount = 0;
        $notFoundMessage = '';

        // Chuyển các lịch hẹn sang người bàn giao
        $handoverBarber = Barber::where('name', $leaveRequest->handover_person)->first();

        if ($handoverBarber) {
            if ($conflictingAppointments->count() > 0) {
                foreach ($conflictingAppointments as $apt) {
                    $apt->update([
                        'barber_id' => $handoverBarber->id,
                        'notes' => ($apt->notes ? $apt->notes . "\n" : '') . 
                            "Lịch được chuyển từ {$leaveRequest->barber->name} do họ xin nghỉ phép từ {$leaveRequest->start_time->format('d/m/Y H:i')} đến {$leaveRequest->end_time->format('d/m/Y H:i')}"
                    ]);
                    $transferredCount++;
                }
            }
        } else {
            $notFoundMessage = " (Lưu ý: Không tìm thấy barber tên '{$leaveRequest->handover_person}', vui lòng chuyển lịch hẹn thủ công)";
        }

        // Cập nhật trạng thái của barber thành "off" nếu đã đến thời gian bắt đầu
        if (now() >= $leaveRequest->start_time) {
            $leaveRequest->barber->update(['working_status' => 'off']);
        }

        $message = 'Đơn xin nghỉ phép đã được duyệt.';
        if ($transferredCount > 0) {
            $message .= " Đã chuyển {$transferredCount} lịch hẹn sang {$leaveRequest->handover_person}.";
        } elseif ($conflictingAppointments->count() > 0 && !$handoverBarber) {
            $message .= " Có {$conflictingAppointments->count()} lịch hẹn cần chuyển.{$notFoundMessage}";
        } else {
            $message .= " Không có lịch hẹn nào cần chuyển.";
        }

        return redirect()->route('admin.leave_requests.show', $leaveRequest)
            ->with('success', $message);
    }

    /**
     * Từ chối đơn xin nghỉ phép
     */
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
            ->with('success', 'Đơn xin nghỉ phép đã bị từ chối. Trạng thái barber và lịch hẹn giữ nguyên.');
    }
}
