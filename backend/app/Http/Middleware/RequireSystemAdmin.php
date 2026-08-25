<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSystemAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()?->is_system_admin) {
            return response()->json(['message' => 'Bạn không có quyền quản trị tối cao (System Admin).'], 403);
        }

        return $next($request);
    }
}
