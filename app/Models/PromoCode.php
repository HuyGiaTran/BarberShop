<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isValid(?float $orderAmount = null): bool
    {
        if (!$this->is_active) return false;

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;

        if ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit) return false;

        if ($orderAmount !== null && $this->min_order_amount !== null && $orderAmount < $this->min_order_amount) return false;

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->isValid($orderAmount)) return 0;

        if ($this->discount_type === 'fixed') {
            return min($this->discount_value, $orderAmount);
        }

        // percentage
        $discount = $orderAmount * $this->discount_value / 100;
        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }
        return round($discount, 2);
    }
}