<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ShippingboSyncLog;
use App\Services\ShippingboService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductToShippingbo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

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
                $this->attempts()
            );

            Log::error("Failed to {$this->action} product {$this->productId} to Shippingbo: {$e->getMessage()}");

            throw $e;
        }
    }
}
