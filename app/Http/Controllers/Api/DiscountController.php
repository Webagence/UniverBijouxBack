<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal_ht' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $discount = Discount::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Code promo invalide.',
            ], 404);
        }

        if (!$discount->isAvailable()) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code promo n\'est plus disponible.',
            ], 400);
        }

        if ($discount->min_order_amount && $request->subtotal_ht < $discount->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => sprintf('Minimum de commande : %s € HT', number_format($discount->min_order_amount, 2, '.', '')),
            ], 400);
        }

        if ($discount->applies_to === 'specific_products') {
            $productIds = $discount->products()->pluck('products.id')->toArray();
            $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
            $hasEligibleProduct = !empty(array_intersect($productIds, $cartProductIds));

            if (!$hasEligibleProduct) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Ce code promo ne s\'applique pas aux produits de votre panier.',
                ], 400);
            }
        }

        if ($discount->applies_to === 'specific_universes') {
            $universeIds = $discount->products()->with('universe')->get()->pluck('universe.id')->unique()->toArray();
            $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
            $cartProducts = Product::whereIn('id', $cartProductIds)->get();
            $hasEligibleUniverse = $cartProducts->some(fn ($p) => $p->universe && in_array($p->universe->id, $universeIds));

            if (!$hasEligibleUniverse) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Ce code promo ne s\'applique pas aux univers de votre panier.',
                ], 400);
            }
        }

        $discountAmount = $this->calculateDiscountAmount($discount, $request->subtotal_ht);

        return response()->json([
            'valid' => true,
            'discount' => [
                'id' => $discount->id,
                'code' => $discount->code,
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => $discount->value,
                'amount_ht' => $discountAmount,
            ],
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
