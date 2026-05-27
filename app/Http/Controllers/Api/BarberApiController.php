<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarberApiController extends Controller
{
    /**
     * GET /api/barbers
     * Lấy danh sách barber, có tìm kiếm theo keyword
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $barbers = Barber::with('user:id,name,email,phone')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%")
                      ->orWhere('bio', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách barber thành công.',
            'data' => $barbers,
        ], 200);
    }

    /**
     * POST /api/barbers
     * Thêm barber mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('barbers', 'user_id'),
            ],
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Vui lòng nhập user_id.',
            'user_id.exists' => 'User không tồn tại.',
            'user_id.unique' => 'User này đã được gán cho barber khác.',
            'name.required' => 'Vui lòng nhập tên barber.',
            'name.max' => 'Tên barber không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'bio.max' => 'Giới thiệu không được vượt quá 1000 ký tự.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $barber = Barber::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm barber thành công.',
            'data' => $barber,
        ], 201);
    }

    /**
     * GET /api/barbers/{id}
     * Xem chi tiết barber
     */
    public function show($id)
    {
        $barber = Barber::with('user:id,name,email,phone')->find($id);

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy barber.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết barber thành công.',
            'data' => $barber,
        ], 200);
    }

    /**
     * PUT /api/barbers/{id}
     * Cập nhật barber
     */
    public function update(Request $request, $id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy barber.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => [
                'sometimes',
                'required',
                'exists:users,id',
                Rule::unique('barbers', 'user_id')->ignore($barber->id),
            ],
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'user_id.exists' => 'User không tồn tại.',
            'user_id.unique' => 'User này đã được gán cho barber khác.',
            'name.required' => 'Vui lòng nhập tên barber.',
            'name.max' => 'Tên barber không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'bio.max' => 'Giới thiệu không được vượt quá 1000 ký tự.',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $barber->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật barber thành công.',
            'data' => $barber,
        ], 200);
    }

    /**
     * DELETE /api/barbers/{id}
     * Xóa barber
     */
    public function destroy($id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy barber.',
                'data' => null,
            ], 404);
        }

        $barber->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa barber thành công.',
            'data' => null,
        ], 200);
    }
}