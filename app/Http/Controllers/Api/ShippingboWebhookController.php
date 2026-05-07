<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        Log::info('Shippingbo webhook received', [
            'object_class' => $payload['object_class'] ?? null,
            'additional_data' => $payload['additional_data'] ?? null,
        ]);

        try {
            $objectClass = $payload['object_class'] ?? null;

            match ($objectClass) {
                'Order' => $this->shippingboService->handleWebhookOrderState($payload),
                'Product' => $this->shippingboService->handleWebhookStock($payload),
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
}
