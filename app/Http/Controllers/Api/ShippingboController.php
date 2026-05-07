<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingboSetting;
use App\Services\ShippingboService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingboController extends Controller
{
    protected ShippingboService $shippingboService;

    public function __construct(ShippingboService $shippingboService)
    {
        $this->shippingboService = $shippingboService;
    }

    public function getSettings(): JsonResponse
    {
        return response()->json([
            'client_id' => ShippingboSetting::get('client_id'),
            'client_secret' => ShippingboSetting::get('client_secret') ? '••••••••' : null,
            'app_id' => ShippingboSetting::get('app_id'),
            'is_connected' => ShippingboSetting::isConnected(),
            'token_expires_at' => ShippingboSetting::get('token_expires_at'),
            'webhook_url' => url('/api/shippingbo/webhook'),
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'app_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ShippingboSetting::set('client_id', $request->client_id);
        ShippingboSetting::set('client_secret', $request->client_secret);
        ShippingboSetting::set('app_id', $request->app_id);

        return response()->json(['message' => 'Shippingbo settings saved']);
    }

    public function getAuthorizationUrl(Request $request): JsonResponse
    {
        $redirectUri = url('/api/shippingbo/callback');
        $url = $this->shippingboService->getAuthorizationUrl($redirectUri);

        return response()->json(['authorization_url' => $url]);
    }

    public function handleCallback(Request $request): JsonResponse
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json(['error' => 'No authorization code provided'], 400);
        }

        try {
            $redirectUri = url('/api/shippingbo/callback');
            $tokens = $this->shippingboService->getAccessToken($code, $redirectUri);

            return response()->json([
                'message' => 'Successfully connected to Shippingbo',
                'token_expires_at' => $tokens['expires_in'] . ' seconds',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncProduct(string $productId): JsonResponse
    {
        $product = \App\Models\Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        try {
            $result = $this->shippingboService->syncProductToShippingbo($product);

            return response()->json([
                'message' => 'Product synced to Shippingbo',
                'shippingbo_product_id' => $result['product']['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncAllProducts(): JsonResponse
    {
        try {
            $results = $this->shippingboService->syncAllProducts();

            return response()->json([
                'message' => 'Product sync completed',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncOrder(string $orderId): JsonResponse
    {
        $order = \App\Models\Order::with('items', 'user')->find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->shippingbo_order_id) {
            return response()->json(['error' => 'Order already synced to Shippingbo'], 400);
        }

        try {
            $result = $this->shippingboService->syncOrderToShippingbo($order);

            return response()->json([
                'message' => 'Order synced to Shippingbo',
                'shippingbo_order_id' => $result['order']['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSyncStatus(): JsonResponse
    {
        $totalProducts = \App\Models\Product::where('active', true)->count();
        $syncedProducts = \App\Models\Product::whereNotNull('shippingbo_product_id')->count();
        $totalOrders = \App\Models\Order::count();
        $syncedOrders = \App\Models\Order::whereNotNull('shippingbo_order_id')->count();

        return response()->json([
            'is_connected' => ShippingboSetting::isConnected(),
            'products' => [
                'total' => $totalProducts,
                'synced' => $syncedProducts,
                'pending' => $totalProducts - $syncedProducts,
            ],
            'orders' => [
                'total' => $totalOrders,
                'synced' => $syncedOrders,
                'pending' => $totalOrders - $syncedOrders,
            ],
            'webhook_url' => url('/api/shippingbo/webhook'),
        ]);
    }
}
