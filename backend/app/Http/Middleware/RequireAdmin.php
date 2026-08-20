<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()?->is_admin) {
            return response()->json(['message' => 'Bạn không có quyền quản trị thành viên.'], 403);
        }

        return $next($request);
    }
}
