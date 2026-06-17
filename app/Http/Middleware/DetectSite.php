<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $site = $this->resolveSite($request);
        $request->attributes->set('site', $site);

        return $next($request);
    }

    protected function resolveSite(Request $request): ?Site
    {
        $slug = $request->header('X-Site')
            ?? $request->query('site');

        if ($slug) {
            return Site::where('slug', $slug)->where('is_active', true)->first();
        }

        return null;
    }
}
