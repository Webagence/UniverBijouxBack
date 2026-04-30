<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Universe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->active()->with('universe');

        if ($request->has('universe')) {
            $query->byUniverse($request->universe);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('new')) {
            $query->new();
        }

        if ($request->has('tag')) {
            $query->where('tag', $request->tag);
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with('universe')
            ->firstOrFail();

        return response()->json(['product' => $product]);
    }

    public function universes(): JsonResponse
    {
        $universes = Universe::withCount(['products' => function ($query) {
            $query->where('active', true);
        }])
            ->orderBy('display_order')
            ->get();

        return response()->json(['universes' => $universes]);
    }

    public function newArrivals(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 12);

        $products = Product::active()
            ->new()
            ->with('universe')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['products' => $products]);
    }

    public function bestsellers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 8);

        $products = Product::active()
            ->where('tag', 'Best-seller')
            ->with('universe')
            ->limit($limit)
            ->get();

        return response()->json(['products' => $products]);
    }
}
