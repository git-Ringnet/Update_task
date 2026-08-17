<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class AuthenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            $token = $request->input('api_token');
        }

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. Please login.'], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized. Session expired.'], 401);
        }

        if (!$user->api_token_expires_at || $user->api_token_expires_at->isPast()) {
            $user->api_token = null;
            $user->api_token_expires_at = null;
            $user->save();
            return response()->json(['message' => 'Unauthorized. Session expired.'], 401);
        }

        // Sliding expiration: extend session token lifetime by 24 hours if less than 23 hours left
        if ($user->api_token_expires_at->lt(now()->addHours(23))) {
            $user->api_token_expires_at = now()->addHours(24);
            $user->save();
        }

        // Authenticate the user globally for the lifecycle of the request
        auth()->login($user);

        return $next($request);
    }
}
