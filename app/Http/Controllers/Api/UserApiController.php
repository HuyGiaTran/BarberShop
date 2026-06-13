<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->forbiddenResponse();
        }

        return response()->json([
            'success' => true,
            'data' => User::all(),
        ], 200);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay nguoi dung.',
            ], 404);
        }

        if (!$this->canAccessUser($request, $user)) {
            return $this->forbiddenResponse();
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay nguoi dung.',
            ], 404);
        }

        if (!$this->canAccessUser($request, $user)) {
            return $this->forbiddenResponse();
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
        ]);

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat thong tin nguoi dung thanh cong.',
            'data' => $user,
        ], 200);
    }

    private function canAccessUser(Request $request, User $user): bool
    {
        return $request->user()->isAdmin() || $request->user()->is($user);
    }

    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Ban khong co quyen truy cap nguon tai nguyen nay.',
        ], 403);
    }
}
