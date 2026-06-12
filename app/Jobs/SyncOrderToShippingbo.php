<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingboSyncLog;
use App\Services\ShippingboService;
use Illuminate\Support\Facades\Log;

class SyncOrderToShippingbo
{
    use \Illuminate\Foundation\Bus\Dispatchable;

    public function __construct(
        public string $orderId,
        public string $action = 'sync_order'
    ) {}

    public function handle(ShippingboService $shippingboService): void
    {
        $order = Order::with('items', 'user')->find($this->orderId);

        if (!$order) {
            ShippingboSyncLog::log(
                $this->action, 'Order', $this->orderId, 'failed',
                [], [], "Order {$this->orderId} not found"
            );
            return;
        }

        if ($order->shippingbo_order_id && $this->action === 'sync_order') {
            Log::info("Order {$this->orderId} already synced to Shippingbo (ID: {$order->shippingbo_order_id})");
            return;
        }

        try {
            $result = match ($this->action) {
                'sync_order' => $shippingboService->syncOrderToShippingbo($order),
                'cancel_order' => $shippingboService->cancelOrder($order),
                default => throw new \Exception("Unknown action: {$this->action}"),
            };

            ShippingboSyncLog::log(
                $this->action, 'Order', $this->orderId, 'success',
                ['order_reference' => $order->reference],
                $result
            );

            Log::info("Order {$this->orderId} {$this->action} synced to Shippingbo successfully");
        } catch (\Exception $e) {
            ShippingboSyncLog::log(
                $this->action, 'Order', $this->orderId, 'failed',
                ['order_reference' => $order->reference],
                [],
                $e->getMessage(),
                1
            );

            Log::error("Failed to {$this->action} order {$this->orderId} to Shippingbo: {$e->getMessage()}");

            throw $e;
        }
    }
}
