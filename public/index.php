<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Handle CORS preflight (OPTIONS) requests before Laravel boots
// This ensures CORS headers are sent even if Nginx strips them
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = array_map('trim', explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: ''));
    $allowedPatterns = [
        '/^https:\/\/demo\.webagence-rte\.site\/?$/',
        '/^https:\/\/back\.sovama\.mg\/?$/',
        '/^https?:\/\/localhost(:\d+)?$/',
        '/^https?:\/\/127\.0\.0\.1(:\d+)?$/',
    ];

    $isAllowed = in_array($origin, $allowedOrigins);
    if (!$isAllowed) {
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                $isAllowed = true;
                break;
            }
        }
    }

    if ($isAllowed) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Length: 0');
        header('Content-Type: text/plain');
        http_response_code(204);
        exit;
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
