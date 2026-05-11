<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCorsOptions
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            $origin = $request->header('Origin');
            $allowedOrigins = array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')));
            $allowedOriginsPatterns = config('cors.allowed_origins_patterns', []);

            $isAllowed = in_array($origin, $allowedOrigins);

            if (!$isAllowed) {
                foreach ($allowedOriginsPatterns as $pattern) {
                    if (preg_match($pattern, $origin)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            if (!$isAllowed) {
                return response('', 403);
            }

            return response('', 200)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Max-Age', '86400')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }
}
