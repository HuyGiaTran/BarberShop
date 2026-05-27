<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BarberController extends Controller
{
    // index() → Hiển thị danh sách barber, có tìm kiếm
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $barbers = Barber::with('user')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('phone', 'like', '%' . $keyword . '%')
                      ->orWhere('bio', 'like', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('barbers.index', compact('barbers', 'keyword'));
    }

    // create() → Hiển thị form thêm barber
    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('barbers.create', compact('users'));
    }

    // store(Request $request) → Lưu barber mới, có validate dữ liệu
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
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Vui lòng chọn tài khoản người dùng.',
            'user_id.exists' => 'Tài khoản người dùng không tồn tại.',
            'user_id.unique' => 'Tài khoản này đã được gán cho barber khác.',
            'name.required' => 'Vui lòng nhập tên barber.',
            'avatar.image' => 'Avatar phải là hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng jpg, jpeg, png hoặc webp.',
            'avatar.max' => 'Avatar không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('barbers', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Barber::create($validated);

        return redirect()
            ->route('barbers.index')
            ->with('success', 'Thêm barber thành công!');
    }

    // show($id) → Xem chi tiết barber
    public function show($id)
    {
        $barber = Barber::with('user')->findOrFail($id);

        return view('barbers.show', compact('barber'));
    }

    // edit($id) → Hiển thị form sửa barber
    public function edit($id)
    {
        $barber = Barber::findOrFail($id);
        $users = User::orderBy('name')->get();

        return view('barbers.edit', compact('barber', 'users'));
    }

    // update(Request $request, $id) → Cập nhật barber
    public function update(Request $request, $id)
    {
        $barber = Barber::findOrFail($id);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('barbers', 'user_id')->ignore($barber->id),
            ],
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Vui lòng chọn tài khoản người dùng.',
            'user_id.exists' => 'Tài khoản người dùng không tồn tại.',
            'user_id.unique' => 'Tài khoản này đã được gán cho barber khác.',
            'name.required' => 'Vui lòng nhập tên barber.',
            'avatar.image' => 'Avatar phải là hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng jpg, jpeg, png hoặc webp.',
            'avatar.max' => 'Avatar không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($barber->avatar && Storage::disk('public')->exists($barber->avatar)) {
                Storage::disk('public')->delete($barber->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('barbers', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $barber->update($validated);

        return redirect()
            ->route('barbers.index')
            ->with('success', 'Cập nhật barber thành công!');
    }

    // destroy($id) → Xóa barber
    public function destroy($id)
    {
        $barber = Barber::findOrFail($id);

        if ($barber->avatar && Storage::disk('public')->exists($barber->avatar)) {
            Storage::disk('public')->delete($barber->avatar);
        }

        $barber->delete();

        return redirect()
            ->route('barbers.index')
            ->with('success', 'Xóa barber thành công!');
    }
}