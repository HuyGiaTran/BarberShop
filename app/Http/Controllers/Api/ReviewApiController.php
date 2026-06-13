<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewApiController extends Controller
{
    public function index(string $barberId): JsonResponse
    {
        $reviews = Review::with('user:id,name,avatar')
            ->where('barber_id', $barberId)
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;

        return response()->json([
            'success' => true,
            'message' => 'Danh sách đánh giá',
            'data' => [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->count(),
                'reviews' => $reviews,
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'space_rating' => 'required|integer|min:1|max:5',
            'staff_rating' => 'required|integer|min:1|max:5',
            'service_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        // Check if user is the owner of the appointment
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền đánh giá lịch hẹn này.',
            ], 403);
        }

        // Check if appointment is completed
        if ($appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể đánh giá sau khi hoàn thành lịch hẹn.',
            ], 400);
        }

        // Check if review already exists
        $existingReview = Review::where('appointment_id', $appointment->id)->first();
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá lịch hẹn này rồi.',
            ], 400);
        }

        // Tự động gán rating tổng bằng điểm nhân viên (theo yêu cầu)
        $overallRating = $validated['staff_rating'];

        $review = Review::create([
            'user_id' => $request->user()->id,
            'barber_id' => $appointment->barber_id,
            'appointment_id' => $appointment->id,
            'rating' => $overallRating,
            'space_rating' => $validated['space_rating'],
            'staff_rating' => $validated['staff_rating'],
            'service_rating' => $validated['service_rating'],
            'comment' => $validated['comment'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá thành công! Mã giảm giá 5.000đ của bạn là: REVIEW5K',
            'promo_code' => 'REVIEW5K',
            'data' => $review,
        ], 201);
    }
}
