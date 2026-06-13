<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\View\View;

class PromoCodeListController extends Controller
{
    public function index(): View
    {
        $promos = PromoCode::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->where('usage_limit', 0)->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('promo-codes', compact('promos'));
    }
}