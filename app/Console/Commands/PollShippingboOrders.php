<?php

namespace App\Console\Commands;

use App\Services\ShippingboService;
use Illuminate\Console\Command;

class PollShippingboOrders extends Command
{
    protected $signature = 'shippingbo:poll-orders';
    protected $description = 'Poll Shippingbo for order status updates';

    public function handle(ShippingboService $shippingboService): int
    {
        $this->info('Polling Shippingbo order statuses...');

        try {
            $result = $shippingboService->pollOrderStatuses();
            $this->info("Checked {$result['checked']} orders, updated {$result['updated']}, errors {$result['errors']}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
