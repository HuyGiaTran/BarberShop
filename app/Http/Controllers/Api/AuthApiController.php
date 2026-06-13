<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * POST /api/register
     * Đăng ký tài khoản mới và trả về token Sanctum.
     *
     * @param  Request  $request  { name, email, phone?, password, password_confirmation }
     * @return JsonResponse       { status, message, data: { token, token_type, user } }
     */
    public function register(Request $request): JsonResponse
    {
        // Bước 1: Validate dữ liệu đầu vào
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        // Bước 2: Tạo user mới (role mặc định = 'customer')
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'customer',
        ]);

        // Bước 3: Tạo Sanctum Personal Access Token
        $token = $user->createToken('api_token')->plainTextToken;

        // Bước 4: Trả về response chuẩn với token và thông tin user
        return response()->json([
            'status'  => 'success',
            'message' => 'Đăng ký tài khoản thành công!',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role'  => $user->role,
                ],
            ],
        ], 201); // 201 Created
    }

    /**
     * POST /api/login
     * Đăng nhập và trả về token Sanctum.
     *
     * @param  Request  $request  { email, password }
     * @return JsonResponse       { status, message, data: { token, token_type, user } }
     */
    public function login(Request $request): JsonResponse
    {
        // Bước 1: Validate dữ liệu đầu vào
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Bước 2: Kiểm tra user tồn tại trong DB
        $user = User::where('email', $request->email)->first();

        // Bước 3: Kiểm tra mật khẩu bằng Hash::check()
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác.',
                'errors'  => [
                    'email' => ['Thông tin đăng nhập không hợp lệ.'],
                ],
            ], 401); // 401 Unauthorized
        }

        // Bước 4: Xóa các token cũ để tránh phát sinh token rác khi đăng nhập nhiều lần
        $user->tokens()->delete();

        // Bước 5: Tạo Sanctum Personal Access Token mới
        $token = $user->createToken('api_token')->plainTextToken;

        // Bước 6: Trả về response chuẩn với token và thông tin user
        return response()->json([
            'status'  => 'success',
            'message' => 'Đăng nhập thành công!',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role'  => $user->role,
                ],
            ],
        ], 200); // 200 OK
    }

    /**
     * POST /api/logout
     * Đăng xuất - Xóa token hiện tại khỏi DB.
     * Yêu cầu: Header Authorization: Bearer {token}
     *
     * @param  Request  $request
     * @return JsonResponse       { status, message }
     */
    public function logout(Request $request): JsonResponse
    {
        // Xóa đúng token đang được dùng để gọi request này
        // currentAccessToken() trả về token object đang active
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Đăng xuất thành công. Token đã bị hủy.',
        ], 200); // 200 OK
    }

    /**
     * GET /api/user
     * Lấy thông tin của user đang đăng nhập (dựa vào token trong header).
     * Yêu cầu: Header Authorization: Bearer {token}
     *
     * @param  Request  $request
     * @return JsonResponse       { status, data: { user } }
     */
    public function user(Request $request): JsonResponse
    {
        // $request->user() tự động resolve user từ Bearer token nhờ Sanctum
        $user = $request->user();

        return response()->json([
            'status'  => 'success',
            'message' => 'Lấy thông tin người dùng thành công.',
            'data'    => [
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'role'       => $user->role,
                    'created_at' => $user->created_at?->toDateTimeString(),
                ],
            ],
        ], 200); // 200 OK
    }
}
