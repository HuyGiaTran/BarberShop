<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeApiController extends Controller
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService
    ) {
    }

    /**
     * POST /api/promo-codes/validate
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $result = $this->promoCodeService->validateAndApply(
            $validated['code'],
            (float) $validated['order_amount'],
            $request->user()?->id
        );

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'],
            'data' => $result['valid'] ? [
                'discount' => $result['discount'],
                'promo_code' => $result['promo_code'],
                'discount_type' => $result['discount_type'],
                'discount_value' => $result['discount_value'],
            ] : null,
        ]);
    }
}