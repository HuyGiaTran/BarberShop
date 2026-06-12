<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyApiController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $summary = $this->loyaltyService->summaryForUser($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Thông tin thẻ thành viên',
            'data' => $summary,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
