<?php

namespace App\Services;

use App\Models\PromoCode;

class PromoCodeService
{
    /**
     * Kiểm tra mã giảm giá và trả về kết quả
     */
    public function validateAndApply(string $code, float $orderAmount): array
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (!$promo) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá không tồn tại.',
                'discount' => 0,
            ];
        }

        if (!$promo->is_active) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá đã bị vô hiệu hoá.',
                'discount' => 0,
            ];
        }

        $now = now();
        if ($promo->starts_at && $now->lt($promo->starts_at)) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá chưa đến hạn sử dụng.',
                'discount' => 0,
            ];
        }
        if ($promo->expires_at && $now->gt($promo->expires_at)) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá đã hết hạn.',
                'discount' => 0,
            ];
        }

        if ($promo->usage_limit > 0 && $promo->used_count >= $promo->usage_limit) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng.',
                'discount' => 0,
            ];
        }

        if ($promo->min_order_amount !== null && $orderAmount < $promo->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($promo->min_order_amount, 0, ',', '.') . 'đ.',
                'discount' => 0,
            ];
        }

        $discount = $promo->calculateDiscount($orderAmount);

        return [
            'valid' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount' => $discount,
            'promo_code' => $promo->code,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
        ];
    }

    /**
     * Tăng số lần sử dụng mã
     */
    public function incrementUsage(PromoCode $promo): void
    {
        $promo->increment('used_count');
    }
}