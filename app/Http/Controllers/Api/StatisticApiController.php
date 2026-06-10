<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StatisticApiController extends Controller
{
    public function revenue(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Chua trien khai endpoint thong ke doanh thu.',
        ], 501);
    }

    public function peakHours(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Chua trien khai endpoint thong ke khung gio cao diem.',
        ], 501);
    }

    public function popularServices(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Chua trien khai endpoint thong ke dich vu pho bien.',
        ], 501);
    }
}
