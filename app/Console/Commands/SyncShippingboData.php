<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductToShippingbo;
use App\Models\Product;
use App\Models\ShippingboSetting;
use App\Services\ShippingboService;
use Illuminate\Console\Command;

class SyncShippingboData extends Command
{
    protected $signature = 'shippingbo:sync {--products : Sync only products} {--orders : Sync only orders} {--status : Show sync status}';

    protected $description = 'Synchronize data with Shippingbo (products, orders, stock)';

    public function handle(ShippingboService $shippingboService): int
    {
        if (!ShippingboSetting::isConnected()) {
            $this->error('Shippingbo is not connected. Please configure credentials first.');
            return Command::FAILURE;
        }

        if ($this->option('status')) {
            $this->showStatus();
            return Command::SUCCESS;
        }

        if ($this->option('products') || !$this->option('orders')) {
            $this->syncProducts($shippingboService);
        }

        if ($this->option('orders')) {
            $this->syncPendingOrders();
        }

        $this->info('Shippingbo sync completed.');
        return Command::SUCCESS;
    }

    protected function syncProducts(ShippingboService $shippingboService): void
    {
        $products = Product::where('active', true)
            ->where(function ($q) {
                $q->whereNull('shippingbo_product_id')
                  ->orWhere('updated_at', '>', 'shippingbo_synced_at');
            })
            ->get();

        if ($products->isEmpty()) {
            $this->info('No products need syncing.');
            return;
        }

        $this->withProgressBar($products, function ($product) {
            SyncProductToShippingbo::dispatch($product->id, 'sync_product');
        });

        $this->newLine();
        $this->info("{$products->count()} product(s) queued for sync.");
    }

    protected function syncPendingOrders(): void
    {
        $orders = \App\Models\Order::whereNull('shippingbo_order_id')
            ->whereNotIn('status', ['cancelled'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders need syncing.');
            return;
        }

        $this->withProgressBar($orders, function ($order) {
            \App\Jobs\SyncOrderToShippingbo::dispatch($order->id, 'sync_order');
        });

        $this->newLine();
        $this->info("{$orders->count()} order(s) queued for sync.");
    }

    protected function showStatus(): void
    {
        $totalProducts = Product::where('active', true)->count();
        $syncedProducts = Product::whereNotNull('shippingbo_product_id')->count();
        $totalOrders = \App\Models\Order::count();
        $syncedOrders = \App\Models\Order::whereNotNull('shippingbo_order_id')->count();
        $failedSyncs = \App\Models\ShippingboSyncLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Products (total)', $totalProducts],
                ['Products (synced)', $syncedProducts],
                ['Products (pending)', $totalProducts - $syncedProducts],
                ['Orders (total)', $totalOrders],
                ['Orders (synced)', $syncedOrders],
                ['Orders (pending)', $totalOrders - $syncedOrders],
                ['Failed syncs (24h)', $failedSyncs],
                ['Token expires', ShippingboSetting::get('token_expires_at', 'N/A')],
            ]
        );
    }
}
