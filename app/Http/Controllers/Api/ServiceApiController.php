<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceApiController extends Controller
{
    /**
     * GET /api/services - Lấy danh sách tất cả dịch vụ
     */
    public function index(): JsonResponse
    {
        $services = Service::all();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách dịch vụ',
            'data' => $services,
        ]);
    }

    /**
     * POST /api/services - Thêm dịch vụ mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm dịch vụ thành công',
            'data' => $service,
        ], 201);
    }

    /**
     * GET /api/services/{id} - Lấy chi tiết một dịch vụ
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy dịch vụ',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết dịch vụ',
            'data' => $service,
        ]);
    }

    /**
     * PUT /api/services/{id} - Cập nhật dịch vụ
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy dịch vụ',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_minutes' => 'sometimes|required|integer|min:1',
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dịch vụ thành công',
            'data' => $service,
        ]);
    }

    /**
     * DELETE /api/services/{id} - Xóa dịch vụ
     */
    public function destroy(string $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy dịch vụ',
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa dịch vụ thành công',
        ]);
    }
}