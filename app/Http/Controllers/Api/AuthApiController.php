<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate dữ liệu gửi lên
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Kiểm tra user có tồn tại trong CSDL không
        $user = User::where('email', $request->email)->first();

        // 3. Kiểm tra mật khẩu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác!',
            ], 401); // 401 là lỗi Unauthorized (Không có quyền)
        }

        // 4. Mật khẩu đúng -> Tạo Token bằng Laravel Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Trả Token về cho Client (Postman)
        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}