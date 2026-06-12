<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SyncOrderToShippingbo;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderDiscount;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingboSetting;
use App\Models\SiteSetting;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $stripeSettings = SiteSetting::where('key', 'stripe')->first()?->value ?? [];
        $secretKey = $stripeSettings['secret_key'] ?? config('services.stripe.secret');

        if (!$secretKey) {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'carrier' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount_code' => 'nullable|string',
            'auto_discount_id' => 'nullable|uuid|exists:discounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!$user->approved) {
            return response()->json([
                'message' => 'Your account must be approved by an admin before placing orders.',
            ], 403);
        }

        Stripe::setApiKey($secretKey);

        try {
            $subtotalHt = 0;
            $totalVat = 0;
            $lineItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->active) {
                    return response()->json(['message' => "Product {$product->name} is not available."], 400);
                }

                if ($product->stock < $item['quantity']) {
                    return response()->json(['message' => "Insufficient stock for {$product->name}."], 400);
                }

                if ($item['quantity'] < $product->moq) {
                    return response()->json(['message' => "Minimum order quantity for {$product->name} is {$product->moq}."], 400);
                }

                $unitPrice = $product->sale_price_ht ?? $product->price_ht;
                $lineTotal = $unitPrice * $item['quantity'];
                $subtotalHt += $lineTotal;
                $vatRate = $product->vat_rate ?? 20;
                $vatAmount = $lineTotal * ($vatRate / 100);
                $totalVat += $vatAmount;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'unit_price_ht' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'line_total_ht' => $lineTotal,
                    'vat_rate' => $vatRate,
                ];
            }

            $discountHt = 0;
            $appliedDiscount = null;

            $discount = null;

            if ($request->filled('discount_code')) {
                $code = strtoupper(trim($request->discount_code));
                $discount = Discount::whereRaw('UPPER(code) = ?', [$code])
                    ->available()
                    ->first();
            } elseif ($request->filled('auto_discount_id')) {
                $discount = Discount::whereNull('code')
                    ->where('id', $request->auto_discount_id)
                    ->available()
                    ->whereHas('users', fn ($q) => $q->where('user_id', $user->id))
                    ->first();
            }

            if ($discount) {
                $discountHt = $this->calculateDiscountAmount($discount, $subtotalHt);

                if ($discount->applies_to === 'specific_products') {
                    $productIds = $discount->products()->pluck('products.id')->toArray();
                    $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
                    if (!empty(array_intersect($productIds, $cartProductIds))) {
                        $appliedDiscount = $discount;
                    }
                } elseif ($discount->applies_to === 'specific_universes') {
                    $universeIds = $discount->products()->with('universe')->get()->pluck('universe.id')->unique()->toArray();
                    $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
                    $cartProducts = Product::whereIn('id', $cartProductIds)->get();
                    if ($cartProducts->some(fn ($p) => $p->universe && in_array($p->universe->id, $universeIds))) {
                        $appliedDiscount = $discount;
                    }
                } else {
                    $appliedDiscount = $discount;
                }

                if ($discount->min_order_amount && $subtotalHt < $discount->min_order_amount) {
                    $appliedDiscount = null;
                    $discountHt = 0;
                }
            }

            $shippingHt = \App\Models\ShippingCarrier::calculateShipping($request->carrier, $subtotalHt, $appliedDiscount);
            $vatAmount = $subtotalHt > 0 ? $totalVat * (($subtotalHt - $discountHt) / $subtotalHt) : 0;

            $totalTtc = ($subtotalHt - $discountHt) + $vatAmount + $shippingHt;

            $totalCents = (int) round($totalTtc * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $totalCents,
                'currency' => 'eur',
                'metadata' => [
                    'user_id' => $user->id,
                    'items_count' => count($request->items),
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'subtotal_ht' => $subtotalHt,
                'discount_ht' => $discountHt,
                'vat_amount' => $vatAmount,
                'shipping_ht' => $shippingHt,
                'total_ttc' => $totalTtc,
                'shipping_address' => $request->shipping_address,
                'carrier' => $request->carrier,
                'notes' => $request->notes,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            foreach ($lineItems as $itemData) {
                $order->items()->create($itemData);
            }

            if ($appliedDiscount) {
                OrderDiscount::create([
                    'order_id' => $order->id,
                    'discount_id' => $appliedDiscount->id,
                    'code' => $appliedDiscount->code,
                    'type' => $appliedDiscount->type,
                    'value' => $appliedDiscount->value,
                    'amount_ht' => $discountHt,
                ]);

                $appliedDiscount->incrementUsage();
            }

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'orderId' => $order->id,
                'orderReference' => $order->reference,
                'amount' => $totalTtc,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $stripeSettings = SiteSetting::where('key', 'stripe')->first()?->value ?? [];
        $secretKey = $stripeSettings['secret_key'] ?? config('services.stripe.secret');
        $webhookSecret = $stripeSettings['webhook_secret'] ?? config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            return response()->json(['message' => 'Webhook secret not configured.'], 400);
        }

        Stripe::setApiKey($secretKey);

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $stripePaymentIntentId = $paymentIntent->id;

            $order = Order::where('stripe_payment_intent_id', $stripePaymentIntentId)
                ->where('status', Order::STATUS_PENDING)
                ->first();

            if ($order) {
                $order->update([
                    'status' => Order::STATUS_CONFIRMED,
                    'stripe_payment_status' => 'succeeded',
                ]);

                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->decrement('stock', $item->quantity);
                    }
                }

                $invoiceService = app(InvoiceService::class);
                $invoice = $invoiceService->generateForOrder($order);
                $invoiceService->sendInvoiceByEmail($invoice);

                if (ShippingboSetting::isConnected()) {
                    SyncOrderToShippingbo::dispatch($order->id, 'sync_order');
                }
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $paymentIntent = $event->data->object;
            $stripePaymentIntentId = $paymentIntent->id;

            $order = Order::where('stripe_payment_intent_id', $stripePaymentIntentId)->first();

            if ($order && $order->status === Order::STATUS_PENDING) {
                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'stripe_payment_status' => 'failed',
                ]);
            }
        }

        return response()->json(['received' => true]);
    }

    public function confirmPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|uuid|exists:orders,id',
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if (!$order) {
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            return response()->json([
                'order' => $order->load('items'),
                'status' => $order->status,
                'invoice' => $order->invoices()->first(),
            ]);
        }

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'stripe_payment_status' => 'succeeded',
        ]);

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->decrement('stock', $item->quantity);
            }
        }

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->generateForOrder($order);
        $invoiceService->sendInvoiceByEmail($invoice);

        if (ShippingboSetting::isConnected()) {
            SyncOrderToShippingbo::dispatch($order->id, 'sync_order');
        }

        return response()->json([
            'order' => $order->load('items'),
            'status' => $order->status,
            'invoice' => $order->invoices()->first(),
        ]);
    }

    private function calculateDiscountAmount(Discount $discount, float $subtotalHt): float
    {
        $amount = 0;

        switch ($discount->type) {
            case 'percentage':
                $amount = $subtotalHt * ($discount->value / 100);
                break;
            case 'fixed':
                $amount = $discount->value;
                break;
            case 'free_shipping':
                $amount = 0;
                break;
        }

        if ($discount->max_discount_amount && $amount > $discount->max_discount_amount) {
            $amount = $discount->max_discount_amount;
        }

        if ($amount > $subtotalHt) {
            $amount = $subtotalHt;
        }

        return round($amount, 2);
    }
}
