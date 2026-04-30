<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->orders()->with(['items.product', 'user']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->recent()->paginate($request->get('per_page', 20));

        return response()->json($orders);
    }

    public function show(string $id): JsonResponse
    {
        $order = $request->user()->orders()
            ->with(['items.product', 'user', 'invoices'])
            ->findOrFail($id);

        return response()->json(['order' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
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

        return DB::transaction(function () use ($request, $user) {
            $subtotalHt = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->active) {
                    throw new \Exception("Product {$product->name} is not available");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                if ($item['quantity'] < $product->moq) {
                    throw new \Exception("Minimum order quantity for {$product->name} is {$product->moq}");
                }

                $lineTotal = $product->price_ht * $item['quantity'];
                $subtotalHt += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'unit_price_ht' => $product->price_ht,
                    'quantity' => $item['quantity'],
                    'line_total_ht' => $lineTotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $vatRate = 20;
            $vatAmount = $subtotalHt * ($vatRate / 100);
            $shippingHt = $subtotalHt >= 300 ? 0 : 15;
            $totalTtc = $subtotalHt + $vatAmount + $shippingHt;

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
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items'),
            ], 201);
        });
    }

    public function cancel(string $id, Request $request): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
            return response()->json([
                'message' => 'Order cannot be cancelled at this stage.',
            ], 400);
        }

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Order cancelled',
            'order' => $order,
        ]);
    }
}
