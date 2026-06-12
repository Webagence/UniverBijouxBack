<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ShippingboSyncLog;
use App\Services\ShippingboService;
use Illuminate\Support\Facades\Log;

class SyncProductToShippingbo
{
    use \Illuminate\Foundation\Bus\Dispatchable;

    public function __construct(
        public string $productId,
        public string $action = 'sync_product'
    ) {}

    public function handle(ShippingboService $shippingboService): void
    {
        $product = Product::find($this->productId);

        if (!$product) {
            ShippingboSyncLog::log(
                $this->action, 'Product', $this->productId, 'failed',
                [], [], "Product {$this->productId} not found"
            );
            return;
        }

        try {
            $result = match ($this->action) {
                'sync_product' => $shippingboService->syncProductToShippingbo($product),
                'update_product' => $shippingboService->updateProduct(
                    $product->shippingbo_product_id,
                    [
                        'title' => $product->name,
                        'picture_url' => !empty($product->images) ? $product->images[0] : null,
                    ]
                ),
                default => throw new \Exception("Unknown action: {$this->action}"),
            };

            ShippingboSyncLog::log(
                $this->action, 'Product', $this->productId, 'success',
                ['product_reference' => $product->reference, 'product_name' => $product->name],
                $result
            );

            Log::info("Product {$this->productId} {$this->action} synced to Shippingbo successfully");
        } catch (\Exception $e) {
            ShippingboSyncLog::log(
                $this->action, 'Product', $this->productId, 'failed',
                ['product_reference' => $product->reference],
                [],
                $e->getMessage(),
                1
            );

            Log::error("Failed to {$this->action} product {$this->productId} to Shippingbo: {$e->getMessage()}");

            throw $e;
        }
    }
}
