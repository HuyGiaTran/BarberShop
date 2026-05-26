<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    /**
     * GET /api/users
     * Lấy danh sách toàn bộ user (Chỉ dành cho Admin)
     */
    public function index(Request $request)
    {
        // Kiểm tra xem người dùng hiện tại có phải là admin không
        // Giả sử trường phân quyền của bạn tên là 'role' và giá trị cho admin là 'admin'
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chức năng này (Forbidden).'
            ], 403);
        }

        $users = User::all();

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }

    /**
     * GET /api/users/{id}
     * Xem thông tin chi tiết của 1 user
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * PUT /api/users/{id}
     * Cập nhật thông tin user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.'
            ], 404);
        }

        // Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'role'  => 'sometimes|string'
            // Bạn có thể thêm xử lý đổi mật khẩu ở đây nếu cần thiết
        ]);

        // Cập nhật dữ liệu
        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin người dùng thành công.',
            'data' => $user
        ], 200);
    }
}