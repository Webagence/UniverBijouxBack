<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function visit(Request $request): JsonResponse
    {
        $host = $request->input('host', $request->header('X-Forwarded-Host', ''));
        $site = match (true) {
            str_contains($host, 'bijoux') => 'bijoux',
            str_contains($host, 'pierres') => 'pierres',
            default => 'portail',
        };

        PageView::create([
            'site' => $site,
            'url' => $request->input('url'),
            'path' => $request->input('path'),
            'title' => $request->input('title'),
            'referrer' => $request->input('referrer'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $request->input('device'),
            'visited_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
