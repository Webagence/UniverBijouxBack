<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
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
            'notes' => 'nullable|string',
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

                $lineTotal = $product->price_ht * $item['quantity'];
                $subtotalHt += $lineTotal;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'unit_price_ht' => $product->price_ht,
                    'quantity' => $item['quantity'],
                    'line_total_ht' => $lineTotal,
                ];
            }

            $vatRate = 20;
            $vatAmount = $subtotalHt * ($vatRate / 100);
            $shippingHt = $subtotalHt >= 300 ? 0 : 15;
            $totalTtc = $subtotalHt + $vatAmount + $shippingHt;

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

            $order = Order::where('stripe_payment_intent_id', $stripePaymentIntentId)->first();

            if ($order && $order->status === Order::STATUS_PENDING) {
                $order->update([
                    'status' => Order::STATUS_CONFIRMED,
                    'stripe_payment_status' => 'succeeded',
                ]);

                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->decrement('stock', $item->quantity);
                    }
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
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'order' => $order->load('items'),
            'status' => $order->status,
        ]);
    }
}
