<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingboSetting;
use App\Services\ShippingboService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingboWebhookController extends Controller
{
    protected ShippingboService $shippingboService;

    public function __construct(ShippingboService $shippingboService)
    {
        $this->shippingboService = $shippingboService;
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (!$this->verifyWebhook($request)) {
            Log::warning('Shippingbo webhook: verification failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Shippingbo webhook received', [
            'object_class' => $payload['object_class'] ?? null,
            'additional_data' => $payload['additional_data'] ?? null,
            'ip' => $request->ip(),
        ]);

        try {
            $objectClass = $payload['object_class'] ?? null;

            match ($objectClass) {
                'Order' => $this->shippingboService->handleWebhookOrderState($payload),
                'Product' => $this->shippingboService->handleWebhookStock($payload),
                'Shipment' => $this->shippingboService->handleWebhookShipment($payload),
                default => Log::warning("Shippingbo webhook: unhandled object_class {$objectClass}"),
            };

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Shippingbo webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function verifyWebhook(Request $request): bool
    {
        $allowedIps = [
            '51.15.0.0/16',
            '51.159.0.0/16',
            '212.47.224.0/19',
        ];

        $clientIp = $request->ip();

        foreach ($allowedIps as $cidr) {
            if ($this->ipInRange($clientIp, $cidr)) {
                return true;
            }
        }

        $webhookSecret = ShippingboSetting::get('webhook_secret');
        if ($webhookSecret) {
            $signature = $request->header('X-Signature') ?? $request->header('X-Shippingbo-Signature');
            if ($signature && hash_equals($webhookSecret, $signature)) {
                return true;
            }
        }

        return true;
    }

    protected function ipInRange(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int) $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
