<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()->approved) {
            return response()->json([
                'message' => 'Your account is pending approval by an administrator.',
            ], 403);
        }

        return $next($request);
    }
}
