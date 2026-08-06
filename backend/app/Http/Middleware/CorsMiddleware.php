<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        $origin = $request->header('Origin') ?: '';

        // Allowed origins: production domain + localhost dev
        $allowedOrigins = [
            'https://xuongrong.ringnet.vn',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];

        if (in_array($origin, $allowedOrigins) || str_starts_with($origin, 'http://localhost')) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            // Same-origin requests (no Origin header) - still allow
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        // Allow Google OAuth popup postMessage (required for Google One Tap)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        return $response;
    }
}
