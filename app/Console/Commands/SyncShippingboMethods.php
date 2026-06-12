<?php

namespace App\Console\Commands;

use App\Services\ShippingboService;
use Illuminate\Console\Command;

class SyncShippingboMethods extends Command
{
    protected $signature = 'shippingbo:sync-methods';
    protected $description = 'Sync shipping methods from Shippingbo';

    public function handle(ShippingboService $shippingboService): int
    {
        $this->info('Syncing shipping methods from Shippingbo...');

        try {
            $result = $shippingboService->syncShippingMethodsFromShippingbo();
            $this->info("Synced {$result['synced']} new methods (total: {$result['total']})");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
