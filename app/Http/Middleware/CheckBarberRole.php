<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBarberRole
{
    /**
     * Handle an incoming request.
     * Chỉ cho phép user có role = 'barber' đi qua.
     * Nếu không phải barber, trả về lỗi 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'barber') {
            return $next($request);
        }

        abort(403, 'Unauthorized. Only barber can access this page.');
    }
}