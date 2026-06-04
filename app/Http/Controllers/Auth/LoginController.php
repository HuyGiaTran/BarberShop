<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Hiển thị form đăng nhập.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý logic đăng nhập.
     * - Validate input
     * - Dùng Auth::attempt() để kiểm tra credentials
     * - Regenerate session để chống Session Fixation Attack
     * - Redirect về trang intended hoặc dashboard
     */
    public function login(Request $request)
    {
        // Bước 1: Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất :min ký tự.',
        ]);

        // Bước 2: Thử đăng nhập với credentials và tùy chọn "remember me"
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Bước 3: Regenerate session ID sau khi login thành công
            // Mục đích: Chống tấn công Session Fixation
            $request->session()->regenerate();

            // Bước 4: Redirect theo role
            $user = Auth::user();
            $redirectRoute = match ($user->role) {
                'admin'   => 'dashboard',
                'barber'  => 'barber.dashboard',
                default   => 'home',
            };

            return redirect()->intended(route($redirectRoute))
                ->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->name);
        }

        // Bước 5: Nếu thất bại, quay lại form với thông báo lỗi
        return back()
            ->withErrors(['email' => 'Email hoặc mật khẩu không đúng. Vui lòng thử lại.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Xử lý logic đăng xuất.
     * - Auth::logout() xóa thông tin user khỏi session
     * - invalidate() hủy toàn bộ session data
     * - regenerateToken() tạo CSRF token mới để chống CSRF sau logout
     */
    public function logout(Request $request)
    {
        // Bước 1: Đăng xuất user khỏi session
        Auth::logout();

        // Bước 2: Hủy session hiện tại
        $request->session()->invalidate();

        // Bước 3: Tạo mới CSRF token (bảo mật)
        $request->session()->regenerateToken();

        // Bước 4: Redirect về trang đăng nhập
        return redirect()->route('login')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }
}