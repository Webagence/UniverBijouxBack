<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOrderToShippingbo;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderDiscount;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingboSetting;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function show(Request $request, string $id): JsonResponse
    {
        $order = $request->user()->orders()
            ->with(['items', 'user'])
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

        return DB::transaction(function () use ($request, $user) {
            $subtotalHt = 0;
            $totalVat = 0;
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

                $unitPrice = $product->sale_price_ht ?? $product->price_ht;
                $lineTotal = $unitPrice * $item['quantity'];
                $subtotalHt += $lineTotal;
                $vatRate = $product->vat_rate ?? 20;
                $vatAmount = $lineTotal * ($vatRate / 100);
                $totalVat += $vatAmount;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'unit_price_ht' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'line_total_ht' => $lineTotal,
                    'vat_rate' => $vatRate,
                ];

                $product->decrement('stock', $item['quantity']);
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

            $shippingHt = $subtotalHt >= 300 ? 0 : 15;
            $vatAmount = $subtotalHt > 0 ? $totalVat * (($subtotalHt - $discountHt) / $subtotalHt) : 0;

            if ($appliedDiscount && $appliedDiscount->type === 'free_shipping') {
                $shippingHt = 0;
            }

            $totalTtc = ($subtotalHt - $discountHt) + $vatAmount + $shippingHt;

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
            ]);

            foreach ($orderItems as $itemData) {
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
                'message' => 'Order created successfully',
                'order' => $order->load(['items', 'orderDiscounts']),
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

        if ($order->shippingbo_order_id && ShippingboSetting::isConnected()) {
            SyncOrderToShippingbo::dispatch($order->id, 'cancel_order');
        }

        return response()->json([
            'message' => 'Order cancelled',
            'order' => $order,
        ]);
    }

    public function downloadInvoice(Request $request, string $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        $invoice = $order->invoices()->first();

        if (!$invoice) {
            $invoiceService = app(InvoiceService::class);
            $invoice = $invoiceService->generateForOrder($order);
        }

        $pdfPath = storage_path('app/public/' . $invoice->pdf_path);

        if (!file_exists($pdfPath)) {
            $invoiceService = app(InvoiceService::class);
            $invoice = $invoiceService->generateForOrder($order);
            $pdfPath = storage_path('app/public/' . $invoice->pdf_path);
        }

        if (!file_exists($pdfPath)) {
            return response()->json(['message' => 'PDF not found.'], 500);
        }

        return response()->download($pdfPath, $invoice->invoice_number . '.pdf', [
            'Content-Type' => 'application/pdf',
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
