<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BarberController extends Controller
{
    public function index(Request $request)
    {
        $barbers = Barber::with('user')
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim((string) $request->input('keyword'));

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%")
                        ->orWhere('bio', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('barbers.index', compact('barbers'));
    }

    public function create()
    {
        return view('barbers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $avatarPath = null;

        try {
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('barbers', 'public');
            }

            DB::transaction(function () use ($validated, $avatarPath, $request): void {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'role' => 'barber',
                ]);

                Barber::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'avatar' => $avatarPath,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.barbers.index')
            ->with('success', 'Thêm barber thành công!');
    }

    public function show(Barber $barber)
    {
        $barber->loadMissing('user');

        return view('barbers.show', compact('barber'));
    }

    public function edit(Barber $barber)
    {
        $barber->loadMissing('user');

        return view('barbers.edit', compact('barber'));
    }

    public function update(Request $request, Barber $barber)
    {
        $barber->loadMissing('user');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($barber->user_id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $currentAvatar = $barber->avatar;
        $newAvatarPath = null;

        try {
            if ($request->hasFile('avatar')) {
                $newAvatarPath = $request->file('avatar')->store('barbers', 'public');
            }

            DB::transaction(function () use ($barber, $validated, $request, $newAvatarPath): void {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'role' => 'barber',
                ];

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if ($barber->user) {
                    $barber->user->update($userData);
                }

                $barber->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'avatar' => $newAvatarPath ?? $barber->avatar,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newAvatarPath) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            throw $exception;
        }

        if ($newAvatarPath && $currentAvatar) {
            Storage::disk('public')->delete($currentAvatar);
        }

        return redirect()
            ->route('admin.barbers.index')
            ->with('success', 'Cập nhật barber thành công!');
    }

    public function destroy(Barber $barber)
    {
        $barber->loadMissing('user');
        $avatarPath = $barber->avatar;

        DB::transaction(function () use ($barber): void {
            if ($barber->user) {
                $barber->user->delete();

                return;
            }

            $barber->delete();
        });

        if ($avatarPath) {
            Storage::disk('public')->delete($avatarPath);
        }

        return redirect()
            ->route('admin.barbers.index')
            ->with('success', 'Xóa barber thành công!');
    }
}
