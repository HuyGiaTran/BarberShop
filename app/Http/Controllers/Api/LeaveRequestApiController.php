<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestApiController extends Controller
{
    /**
     * GET /api/barber/leave-requests
     * Lấy danh sách đơn nghỉ phép của Barber hiện tại
     */
    public function index(Request $request): JsonResponse
    {
        $barber = $request->user()->barber;

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không phải là thợ cắt tóc.',
            ], 403);
        }

        $leaveRequests = LeaveRequest::where('barber_id', $barber->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách đơn xin nghỉ phép',
            'data' => $leaveRequests,
        ], 200);
    }

    /**
     * POST /api/barber/leave-requests
     * Tạo đơn xin nghỉ phép mới
     */
    public function store(Request $request): JsonResponse
    {
        $barber = $request->user()->barber;

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không phải là thợ cắt tóc.',
            ], 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $leaveRequest = LeaveRequest::create([
            'barber_id' => $barber->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn xin nghỉ phép thành công. Vui lòng chờ admin duyệt.',
            'data' => $leaveRequest,
        ], 201);
    }
}
