<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $loyalty = LoyaltyProgram::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0, 'tier' => 'bronze']
        );

        return response()->json([
            'success' => true,
            'message' => 'Thông tin thẻ thành viên',
            'data' => [
                'points' => $loyalty->points,
                'tier' => $loyalty->tier,
                // Next tier logic could be added here
            ],
        ], 200);
    }
}
