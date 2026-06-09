<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Lấy Barber profile dựa trên user đang đăng nhập
     */
    private function getBarber()
    {
        return Barber::where('user_id', Auth::id())->first();
    }

    /**
     * Hiển thị danh sách đơn xin nghỉ phép
     */
    public function index()
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        $leaveRequests = LeaveRequest::where('barber_id', $barber->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('barber.leave-requests.index', compact('barber', 'leaveRequests'));
    }

    /**
     * Hiển thị form tạo đơn xin nghỉ phép
     */
    public function create()
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        // Get all barbers except current one for handover selection
        $availableBarbers = Barber::where('id', '!=', $barber->id)
            ->orderBy('name')
            ->get();

        return view('barber.leave-requests.create', compact('barber', 'availableBarbers'));
    }

    /**
     * Lưu đơn xin nghỉ phép
     */
    public function store(Request $request)
    {
        $barber = $this->getBarber();

        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        $validated = $request->validate([
            'recipient' => 'required|string|max:255',
            'applicant_name' => 'required|string|max:255',
            'applicant_dob' => 'required|date',
            'applicant_address' => 'required|string|max:255',
            'applicant_phone' => 'required|regex:/^[0-9]{10}$/',
            'applicant_workplace' => 'required|string|max:255',
            'applicant_position' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'required|string',
            'handover_person' => 'required|string|max:255',
            'commitment' => 'required|boolean',
        ], [
            'recipient.required' => 'Vui lòng nhập tên cơ quan/công ty.',
            'applicant_name.required' => 'Vui lòng nhập họ tên.',
            'applicant_dob.required' => 'Vui lòng chọn ngày sinh.',
            'applicant_address.required' => 'Vui lòng nhập địa chỉ.',
            'applicant_phone.required' => 'Vui lòng nhập số điện thoại.',
            'applicant_phone.regex' => 'Số điện thoại phải có đúng 10 chữ số.',
            'applicant_workplace.required' => 'Vui lòng nhập địa điểm công tác.',
            'applicant_position.required' => 'Vui lòng nhập chức vụ.',
            'start_time.required' => 'Vui lòng chọn ngày giờ bắt đầu.',
            'end_time.required' => 'Vui lòng chọn ngày giờ kết thúc.',
            'end_time.after' => 'Ngày giờ kết thúc phải sau ngày giờ bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ phép.',
            'handover_person.required' => 'Vui lòng chọn người đảm nhiệm công việc.',
            'commitment.required' => 'Vui lòng xác nhận cam kết.',
        ]);

        // Thêm barber_id
        $validated['barber_id'] = $barber->id;
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

        return redirect()->route('barber.leave_requests.index')
            ->with('success', 'Đơn xin nghỉ phép đã được gửi thành công. Đang chờ xét duyệt.');
    }

    /**
     * Hiển thị chi tiết đơn xin nghỉ phép
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $barber = $this->getBarber();

        if (!$barber || $leaveRequest->barber_id !== $barber->id) {
            return redirect()->route('barber.dashboard')->with('error', 'Bạn không có quyền xem đơn này.');
        }

        return view('barber.leave-requests.show', compact('barber', 'leaveRequest'));
    }

    /**
     * Hủy đơn xin nghỉ phép (chỉ khi trạng thái pending)
     */
    public function cancel(LeaveRequest $leaveRequest)
    {
        $barber = $this->getBarber();

        if (!$barber || $leaveRequest->barber_id !== $barber->id) {
            return redirect()->route('barber.dashboard')->with('error', 'Bạn không có quyền hủy đơn này.');
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy đơn đang chờ xét duyệt.');
        }

        $leaveRequest->delete();

        return redirect()->route('barber.leave_requests.index')
            ->with('success', 'Đơn xin nghỉ phép đã được hủy.');
    }
}
