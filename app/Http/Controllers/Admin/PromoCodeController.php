<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        $promos = PromoCode::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.promo_codes.index', compact('promos'));
    }

    public function create(): View
    {
        return view('admin.promo_codes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['usage_limit'] = $validated['usage_limit'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        PromoCode::create($validated);

        return redirect()->route('admin.promo_codes.index')
            ->with('success', 'Tạo mã giảm giá thành công!');
    }

    public function edit(PromoCode $promoCode): View
    {
        return view('admin.promo_codes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['usage_limit'] = $validated['usage_limit'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $promoCode->update($validated);

        return redirect()->route('admin.promo_codes.index')
            ->with('success', 'Cập nhật mã giảm giá thành công!');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();
        return redirect()->route('admin.promo_codes.index')
            ->with('success', 'Đã xóa mã giảm giá.');
    }
}