<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateInvoice extends Command
{
    protected $signature = 'invoice:generate {order_id? : Order ID to generate invoice for}';

    protected $description = 'Generate invoice PDF for an order';

    public function handle(InvoiceService $invoiceService): int
    {
        $orderId = $this->argument('order_id');

        if ($orderId) {
            $orders = Order::where('id', $orderId)->get();
        } else {
            $orders = Order::where('status', '!=', Order::STATUS_CANCELLED)
                ->whereDoesntHave('invoices')
                ->get();
        }

        if ($orders->isEmpty()) {
            $this->info('No orders to invoice.');
            return Command::SUCCESS;
        }

        foreach ($orders as $order) {
            $this->info("Generating invoice for order {$order->reference} ({$order->id})...");

            try {
                $invoice = $invoiceService->generateForOrder($order);
                $this->info("  → Invoice {$invoice->invoice_number} created.");

                $this->info("  → Sending email to {$order->user->email}...");
                $invoiceService->sendInvoiceByEmail($invoice);
                $this->info("  → Email sent.");
            } catch (\Exception $e) {
                $this->error("  → Failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
