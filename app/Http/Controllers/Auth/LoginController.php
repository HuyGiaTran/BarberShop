<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => 'Vui long nhap dia chi email.',
            'email.email' => 'Dia chi email khong hop le.',
            'password.required' => 'Vui long nhap mat khau.',
            'password.min' => 'Mat khau phai co it nhat :min ky tu.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $redirectRoute = match ($user->role) {
                'admin' => 'admin.dashboard',
                'barber' => 'barber.dashboard',
                default => 'home',
            };

            return redirect()->intended(route($redirectRoute))
                ->with('success', 'Dang nhap thanh cong! Chao mung ' . $user->name);
        }

        return back()
            ->withErrors(['email' => 'Email hoac mat khau khong dung. Vui long thu lai.'])
            ->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Ban da dang xuat thanh cong.');
    }
}