<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     * Adds CORS headers for API access from allowed origins.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Respond immediately to OPTIONS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        $allowedOrigin = $this->getAllowedOrigin($request);
        if ($allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    private function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header('Origin') ?: '';

        $allowedOrigins = [
            'https://xuongrong.ringnet.vn',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:5173',
            'http://192.168.30.121:3000',
            'http://192.168.30.121:5173',
        ];

        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Allow any localhost port for local dev
        if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
            return $origin;
        }

        // No Origin header = same-origin request, no CORS headers needed
        return '';
    }
}
