<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Hiển thị form đăng ký.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý logic đăng ký tài khoản mới.
     * - Validate input (bao gồm unique email, password confirmation)
     * - Hash mật khẩu bằng Bcrypt (Hash::make)
     * - Tạo User mới với role mặc định là 'customer'
     * - Tự động đăng nhập sau khi đăng ký thành công
     * - Redirect về dashboard
     */
    public function register(Request $request)
    {
        // Bước 1: Validate dữ liệu đầu vào
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:20', 'regex:/^[0-9\s\+\-\(\)]+$/'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'name.max'           => 'Họ và tên không được quá 255 ký tự.',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu.',
        ]);

        // Bước 2: Tạo user mới
        // Hash::make() tự động mã hóa password bằng Bcrypt
        // role mặc định là 'customer', chỉ admin mới có thể set role=admin
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'customer',
        ]);

        // Bước 3: Tự động đăng nhập sau khi đăng ký thành công
        Auth::login($user);

        // Bước 4: Regenerate session
        $request->session()->regenerate();

        // Bước 5: Redirect về dashboard với thông báo thành công
        return redirect()->route('dashboard')
            ->with('success', 'Đăng ký thành công! Chào mừng bạn, ' . $user->name . '!');
    }
}