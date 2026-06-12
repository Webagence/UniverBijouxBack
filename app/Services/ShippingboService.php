<?php

namespace App\Services;

use App\Models\ShippingboSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingboService
{
    protected string $baseUrl;
    protected string $oauthUrl;

    public function __construct()
    {
        $this->baseUrl = ShippingboSetting::getApiBaseUrl();
        $this->oauthUrl = ShippingboSetting::getOAuthBaseUrl();
    }

    protected function normalizeCountryCode(?string $country): string
    {
        if (!$country) return 'FR';

        $country = trim($country);
        if (strlen($country) === 2) return strtoupper($country);

        $map = [
            'france' => 'FR', 'France' => 'FR', 'FRANCE' => 'FR',
            'belgique' => 'BE', 'Belgique' => 'BE', 'belgium' => 'BE',
            'suisse' => 'CH', 'Suisse' => 'CH', 'switzerland' => 'CH',
            'madagascar' => 'MG',
            'allemagne' => 'DE', 'germany' => 'DE',
            'espagne' => 'ES', 'spain' => 'ES',
            'italie' => 'IT', 'italy' => 'IT',
            'pays-bas' => 'NL', 'netherlands' => 'NL',
            'portugal' => 'PT',
            'royaume-uni' => 'GB', 'angleterre' => 'GB', 'england' => 'GB', 'united kingdom' => 'GB',
            'luxembourg' => 'LU',
            'monaco' => 'MC',
            'etats-unis' => 'US', 'états-unis' => 'US', 'united states' => 'US',
            'canada' => 'CA',
        ];

        return $map[$country] ?? $map[strtolower($country)] ?? 'FR';
    }

    // ==================== OAuth ====================

    public function getAuthorizationUrl(string $redirectUri, array $scopes = ['order']): string
    {
        $clientId = ShippingboSetting::get('client_id');

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
        ], '', '&', PHP_QUERY_RFC3986);

        return "{$this->oauthUrl}/oauth/authorize?{$params}";
    }

    public function getAccessToken(string $code, string $redirectUri): array
    {
        $response = Http::timeout(10)->asJson()
            ->post("{$this->oauthUrl}/oauth/token", [
                'grant_type' => 'authorization_code',
                'client_id' => ShippingboSetting::get('client_id'),
                'client_secret' => ShippingboSetting::get('client_secret'),
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get access token: ' . $response->body());
        }

        $data = $response->json();

        ShippingboSetting::set('access_token', $data['access_token']);
        ShippingboSetting::set('refresh_token', $data['refresh_token']);
        ShippingboSetting::set('token_expires_at', now()->addSeconds($data['expires_in'])->toIso8601String());

        return $data;
    }

    public function refreshAccessToken(): array
    {
        $refreshToken = ShippingboSetting::get('refresh_token');

        if (!$refreshToken) {
            throw new \Exception('No refresh token available');
        }

        $response = Http::timeout(10)->asJson()
            ->post("{$this->oauthUrl}/oauth/token", [
                'grant_type' => 'refresh_token',
                'client_id' => ShippingboSetting::get('client_id'),
                'client_secret' => ShippingboSetting::get('client_secret'),
                'refresh_token' => $refreshToken,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh access token: ' . $response->body());
        }

        $data = $response->json();

        ShippingboSetting::set('access_token', $data['access_token']);
        ShippingboSetting::set('refresh_token', $data['refresh_token']);
        ShippingboSetting::set('token_expires_at', now()->addSeconds($data['expires_in'])->toIso8601String());

        return $data;
    }

    public function ensureValidToken(): void
    {
        $expiresAt = ShippingboSetting::get('token_expires_at');

        if (!$expiresAt || now()->gte(new \DateTime($expiresAt))) {
            $this->refreshAccessToken();
        }
    }

    // ==================== API Requests ====================

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $this->ensureValidToken();

        $url = "{$this->baseUrl}/{$endpoint}";

        $response = Http::timeout(10)->retry(2, 1000)->asJson()->withHeaders(ShippingboSetting::getHeaders())
            ->{$method}($url, $data);

        if ($response->failed()) {
            $error = "Shippingbo API error [{$response->status()}]: {$response->body()}";
            Log::error($error);
            throw new \Exception($error);
        }

        return $response->json() ?? [];
    }

    // ==================== Addresses ====================

    public function createAddress(array $addressData): array
    {
        return $this->request('post', 'addresses', $addressData);
    }

    // ==================== Orders ====================

    public function createOrder(array $orderData): array
    {
        return $this->request('post', 'orders', $orderData);
    }

    public function getOrder(string $orderId): array
    {
        return $this->request('get', "orders/{$orderId}");
    }

    public function listOrders(array $params = []): array
    {
        $query = http_build_query(array_merge([
            'limit' => 50,
            'offset' => 0,
        ], $params));

        return $this->request('get', "orders?{$query}");
    }

    public function updateOrderItems(string $orderId, array $orderItems): array
    {
        return $this->request('post', "orders/{$orderId}/update_order_items", [
            'order_items' => $orderItems,
        ]);
    }

    public function cancelOrder(\App\Models\Order $order): array
    {
        $result = $this->request('delete', "orders/{$order->shippingbo_order_id}");

        $order->update([
            'shippingbo_order_id' => null,
            'shippingbo_synced_at' => null,
        ]);

        return $result;
    }

    // ==================== Products ====================

    public function createProduct(array $productData): array
    {
        return $this->request('post', 'products', $productData);
    }

    public function updateProduct(string $productId, array $productData): array
    {
        return $this->request('patch', "products/{$productId}", $productData);
    }

    public function listProducts(array $params = []): array
    {
        $query = http_build_query(array_merge([
            'limit' => 50,
            'offset' => 0,
        ], $params));

        return $this->request('get', "products?{$query}");
    }

    public function getProduct(string $productId): array
    {
        return $this->request('get', "products/{$productId}");
    }

    // ==================== Product Barcodes ====================

    public function createProductBarcode(array $barcodeData): array
    {
        return $this->request('post', 'product_barcodes', $barcodeData);
    }

    // ==================== Shipments ====================

    public function listShipments(array $params = []): array
    {
        $query = http_build_query(array_merge([
            'limit' => 50,
            'offset' => 0,
        ], $params));

        return $this->request('get', "shipments?{$query}");
    }

    public function getShipment(string $shipmentId): array
    {
        return $this->request('get', "shipments/{$shipmentId}");
    }

    // ==================== Sync Helpers ====================

    public function syncOrderToShippingbo(\App\Models\Order $order): array
    {
        if ($order->shippingbo_order_id) {
            return $this->updateOrderStatusInShippingbo($order);
        }

        $addressData = [
            'firstname' => $order->shipping_address['name'] ?? '',
            'lastname' => $order->shipping_address['last_name'] ?? '',
            'street1' => $order->shipping_address['address'] ?? '',
            'street2' => $order->shipping_address['address_line2'] ?? null,
            'zip' => $order->shipping_address['postal_code'] ?? '',
            'city' => $order->shipping_address['city'] ?? '',
            'country' => $this->normalizeCountryCode($order->shipping_address['country'] ?? 'FR'),
            'phone1' => $order->shipping_address['phone'] ?? null,
            'email' => $order->user->email ?? '',
            'company_name' => $order->user->company_name ?? null,
        ];

        $address = $this->createAddress($addressData);
        $addressId = $address['address']['id'] ?? null;

        $orderItems = [];
        foreach ($order->items as $item) {
            $vatRate = $item->vat_rate ?? 20;
            $vatMultiplier = 1 + ($vatRate / 100);
            $orderItems[] = [
                'product_ref' => $item->product_reference,
                'title' => $item->product_name,
                'quantity' => $item->quantity,
                'source' => 'Francegems',
                'source_ref' => "{$order->reference}-{$item->id}",
                'price_tax_included_cents' => (int) round($item->line_total_ht * $vatMultiplier * 100),
                'price_tax_included_currency' => 'EUR',
                'tax_cents' => (int) round($item->line_total_ht * ($vatRate / 100) * 100),
                'tax_currency' => 'EUR',
            ];
        }

        $orderData = [
            'source' => 'Francegems',
            'source_ref' => $order->reference,
            'shipping_address_id' => $addressId,
            'billing_address_id' => $addressId,
            'origin' => 'Francegems',
            'origin_ref' => $order->reference,
            'origin_created_at' => $order->created_at->toIso8601String(),
            'total_price_cents' => (int) round($order->total_ttc * 100),
            'total_price_currency' => 'EUR',
            'total_tax_cents' => (int) round($order->vat_amount * 100),
            'total_shipping_tax_included_cents' => (int) round($order->shipping_ht * 1.2 * 100),
            'total_shipping_tax_cents' => (int) round($order->shipping_ht * 100),
            'order_items_attributes' => $orderItems,
        ];

        $result = $this->createOrder($orderData);

        $order->update([
            'shippingbo_order_id' => $result['order']['id'] ?? null,
            'shippingbo_synced_at' => now(),
        ]);

        return $result;
    }

    public function updateOrderStatusInShippingbo(\App\Models\Order $order): array
    {
        if (!$order->shippingbo_order_id) {
            throw new \Exception("Order {$order->id} has no Shippingbo ID");
        }

        $statusMap = [
            \App\Models\Order::STATUS_PENDING => 'new',
            \App\Models\Order::STATUS_CONFIRMED => 'in_progress',
            \App\Models\Order::STATUS_PREPARING => 'in_progress',
            \App\Models\Order::STATUS_SHIPPED => 'shipped',
            \App\Models\Order::STATUS_DELIVERED => 'delivered',
            \App\Models\Order::STATUS_CANCELLED => 'cancelled',
        ];

        $shippingboStatus = $statusMap[$order->status] ?? null;

        if (!$shippingboStatus) {
            throw new \Exception("Unknown order status: {$order->status}");
        }

        return $this->request('patch', "orders/{$order->shippingbo_order_id}", [
            'state' => $shippingboStatus,
        ]);
    }

    public function syncProductToShippingbo(\App\Models\Product $product): array
    {
        $productData = [
            'user_ref' => $product->reference,
            'ean13' => null,
            'title' => $product->name,
            'picture_url' => !empty($product->images) ? $product->images[0] : null,
            'weight' => null,
            'height' => null,
            'length' => null,
            'width' => null,
            'hs_code' => null,
            'supplier' => null,
        ];

        $result = $this->createProduct($productData);

        $product->update([
            'shippingbo_product_id' => $result['product']['id'] ?? null,
            'shippingbo_synced_at' => now(),
        ]);

        return $result;
    }

    public function getSyncStatus(): array
    {
        $totalProducts = \App\Models\Product::where('active', true)->count();
        $syncedProducts = \App\Models\Product::whereNotNull('shippingbo_product_id')->count();
        $totalOrders = \App\Models\Order::count();
        $syncedOrders = \App\Models\Order::whereNotNull('shippingbo_order_id')->count();

        return [
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
        ];
    }

    public function syncAllProducts(): array
    {
        $products = \App\Models\Product::where('active', true)->get();

        foreach ($products as $product) {
            \App\Jobs\SyncProductToShippingbo::dispatch($product->id, 'sync_product');
        }

        return ['success' => $products->count(), 'failed' => 0, 'errors' => []];
    }

    public function handleWebhookOrderState(array $payload): void
    {
        $shippingboOrderId = $payload['object']['id'] ?? null;
        $state = $payload['object']['state'] ?? null;

        if (!$shippingboOrderId || !$state) {
            Log::warning('Shippingbo webhook: missing order id or state', $payload);
            return;
        }

        $order = \App\Models\Order::where('shippingbo_order_id', $shippingboOrderId)->first();

        if (!$order) {
            Log::warning("Shippingbo webhook: order not found for shippingbo_id {$shippingboOrderId}");
            return;
        }

        $statusMap = [
            'new' => \App\Models\Order::STATUS_PENDING,
            'in_progress' => \App\Models\Order::STATUS_PREPARING,
            'shipped' => \App\Models\Order::STATUS_SHIPPED,
            'delivered' => \App\Models\Order::STATUS_DELIVERED,
            'cancelled' => \App\Models\Order::STATUS_CANCELLED,
        ];

        $newStatus = $statusMap[$state] ?? null;

        if ($newStatus && $newStatus !== $order->status) {
            $updateData = ['status' => $newStatus];

            $shipments = $payload['object']['shipments'] ?? [];
            if (!empty($shipments)) {
                $firstShipment = $shipments[0];
                $updateData['tracking_number'] = $firstShipment['shipping_ref'] ?? null;
                $updateData['carrier'] = $firstShipment['carrier_name'] ?? null;
            }

            $order->update($updateData);

            Log::info("Shippingbo webhook: Order {$order->reference} status updated to {$newStatus}");
        }
    }

    public function handleWebhookStock(array $payload): void
    {
        $productData = $payload['object'] ?? [];
        $userRef = $productData['user_ref'] ?? null;
        $newStock = $productData['stock'] ?? null;

        if (!$userRef || $newStock === null) {
            Log::warning('Shippingbo webhook: missing product user_ref or stock', $payload);
            return;
        }

        $product = \App\Models\Product::where('reference', $userRef)->first();

        if (!$product) {
            Log::warning("Shippingbo webhook: product not found for user_ref {$userRef}");
            return;
        }

        $oldStock = $product->stock;
        $product->update(['stock' => $newStock]);

        Log::info("Shippingbo webhook: Product {$product->reference} stock updated from {$oldStock} to {$newStock}");
    }

    public function handleWebhookShipment(array $payload): void
    {
        $shipmentData = $payload['object'] ?? [];
        $orderId = $shipmentData['order_id'] ?? null;
        $shippingRef = $shipmentData['shipping_ref'] ?? null;
        $trackingUrl = $shipmentData['tracking_url'] ?? null;
        $carrierName = $shipmentData['carrier_name'] ?? null;

        if (!$orderId) {
            Log::warning('Shippingbo webhook: missing order_id in shipment', $payload);
            return;
        }

        $order = \App\Models\Order::where('shippingbo_order_id', $orderId)->first();

        if (!$order) {
            Log::warning("Shippingbo webhook: order not found for shippingbo_id {$orderId}");
            return;
        }

        $updateData = [];
        if ($shippingRef) $updateData['tracking_number'] = $shippingRef;
        if ($carrierName) $updateData['carrier'] = $carrierName;

        if (!empty($updateData)) {
            $order->update($updateData);
            Log::info("Shippingbo webhook: Order {$order->reference} shipment updated", $updateData);
        }
    }
}
