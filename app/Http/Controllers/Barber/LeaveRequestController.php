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
    private function getBarber()
    {
        return Barber::where('user_id', Auth::id())->first();
    }

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

    public function create()
    {
        $barber = $this->getBarber();
        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }
        $availableBarbers = Barber::where('id', '!=', $barber->id)->orderBy('name')->get();
        return view('barber.leave-requests.create', compact('barber', 'availableBarbers'));
    }

    public function store(Request $request)
    {
        $barber = $this->getBarber();
        if (!$barber) {
            return redirect()->route('barber.dashboard')->with('error', 'Không tìm thấy hồ sơ Barber.');
        }

        $rules = [
            'leave_type' => 'required|in:full_day,shift',
            'applicant_name' => 'required|string|max:255',
            'applicant_dob' => 'required|date',
            'applicant_address' => 'required|string|max:255',
            'applicant_phone' => 'required|regex:/^[0-9]{10}$/',
            'reason' => 'required|string',
            'handover_person' => 'required|string|max:255',
            'commitment' => 'required|boolean',
        ];

        $messages = [
            'leave_type.required' => 'Vui lòng chọn loại nghỉ phép.',
            'applicant_name.required' => 'Vui lòng nhập họ tên.',
            'applicant_dob.required' => 'Vui lòng chọn ngày sinh.',
            'applicant_address.required' => 'Vui lòng nhập địa chỉ.',
            'applicant_phone.required' => 'Vui lòng nhập số điện thoại.',
            'applicant_phone.regex' => 'Số điện thoại phải có đúng 10 chữ số.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ.',
            'handover_person.required' => 'Vui lòng chọn người bàn giao.',
            'commitment.required' => 'Vui lòng xác nhận cam kết.',
        ];

        // Chỉ validate start_date/end_date khi chọn full_day
        if ($request->leave_type === 'full_day') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $messages['start_date.required'] = 'Vui lòng chọn ngày bắt đầu.';
            $messages['end_date.required'] = 'Vui lòng chọn ngày kết thúc.';
            $messages['end_date.after_or_equal'] = 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.';
        }

        $validated = $request->validate($rules, $messages);

        $slots = [
            'morning' => ['start' => '08:00', 'end' => '13:00'],
            'afternoon' => ['start' => '13:00', 'end' => '18:00'],
            'evening' => ['start' => '18:00', 'end' => '22:00'],
        ];

        if ($validated['leave_type'] === 'full_day') {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $validated['start_time'] = $startDate->copy()->setTime(8, 0);
            $validated['end_time'] = $endDate->copy()->setTime(22, 0);
            $validated['start_date'] = $startDate;
            $validated['end_date'] = $endDate;

            $dates = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
            $validated['leave_dates'] = $dates;
        } else {
            $leaveDate = Carbon::parse($request->input('leave_date'));
            $selectedSlots = $request->input('slots', []);

            if (empty($selectedSlots)) {
                return back()->withInput()->withErrors(['slots' => 'Vui lòng chọn ít nhất một ca nghỉ.']);
            }

            $minStart = '23:59';
            $maxEnd = '00:00';
            foreach ($selectedSlots as $slot) {
                if (isset($slots[$slot])) {
                    if ($slots[$slot]['start'] < $minStart) $minStart = $slots[$slot]['start'];
                    if ($slots[$slot]['end'] > $maxEnd) $maxEnd = $slots[$slot]['end'];
                }
            }

            $validated['start_time'] = $leaveDate->copy()->setTime(
                (int) explode(':', $minStart)[0],
                (int) explode(':', $minStart)[1]
            );
            $validated['end_time'] = $leaveDate->copy()->setTime(
                (int) explode(':', $maxEnd)[0],
                (int) explode(':', $maxEnd)[1]
            );
            $validated['start_date'] = $leaveDate;
            $validated['end_date'] = $leaveDate;
            $validated['leave_dates'] = [$leaveDate->format('Y-m-d')];
        }

        $validated['barber_id'] = $barber->id;
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

        return redirect()->route('barber.leave_requests.index')
            ->with('success', 'Đơn xin nghỉ phép đã được gửi thành công!');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $barber = $this->getBarber();
        if (!$barber || $leaveRequest->barber_id !== $barber->id) {
            return redirect()->route('barber.dashboard')->with('error', 'Bạn không có quyền xem đơn này.');
        }
        return view('barber.leave-requests.show', compact('barber', 'leaveRequest'));
    }

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