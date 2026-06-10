<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ban khong co quyen truy cap tai nguyen nay.',
                ], 403);
            }

            return redirect()
                ->route('home')
                ->with('error', 'Ban khong co quyen truy cap khu vuc quan tri.');
        }

        return $next($request);
    }
}
