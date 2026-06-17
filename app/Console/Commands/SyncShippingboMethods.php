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

        $carrierConfig = [
            'Colissimo' => ['is_active' => true, 'price' => 9.90],
            'Chronopost' => ['is_active' => true, 'price' => 14.90],
            'Mondial Relay' => ['is_active' => true, 'price' => 6.90],
        ];

        try {
            $result = $shippingboService->syncShippingMethodsFromShippingbo($carrierConfig);
            $this->info("Synced {$result['synced']} new methods, updated {$result['updated']} existing (total: {$result['total']})");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
