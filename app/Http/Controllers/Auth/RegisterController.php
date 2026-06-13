<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\\s\\+\\-\\(\\)]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'name.required' => 'Vui long nhap ho va ten.',
            'name.max' => 'Ho va ten khong duoc qua 255 ky tu.',
            'email.required' => 'Vui long nhap dia chi email.',
            'email.email' => 'Dia chi email khong hop le.',
            'email.unique' => 'Email nay da duoc su dung. Vui long chon email khac.',
            'phone.regex' => 'So dien thoai khong hop le.',
            'password.required' => 'Vui long nhap mat khau.',
            'password.min' => 'Mat khau phai co it nhat :min ky tu.',
            'password.confirmed' => 'Xac nhan mat khau khong khop.',
            'password_confirmation.required' => 'Vui long xac nhan mat khau.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Dang ky thanh cong! Chao mung ban, ' . $user->name . '!');
    }
}
