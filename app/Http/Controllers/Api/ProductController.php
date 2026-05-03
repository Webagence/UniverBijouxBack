<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Universe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $products->getCollection()->transform(fn($p) => $this->formatProduct($p));

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with('universe')
            ->firstOrFail();

        return response()->json(['product' => $this->formatProduct($product)]);
    }

    public function universes(): JsonResponse
    {
        $universes = Universe::withCount(['products' => function ($query) {
            $query->where('active', true);
        }])
            ->orderBy('display_order')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'slug' => $u->slug,
                    'name' => $u->name,
                    'description' => $u->description,
                    'image_url' => $u->image_url
                        ? $this->storageUrl($u->image_url)
                        : asset("images/products/{$u->slug}.jpg"),
                    'display_order' => $u->display_order,
                    'products_count' => $u->products_count,
                ];
            });

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
            ->get()
            ->map(fn($p) => $this->formatProduct($p));

        return response()->json(['products' => $products]);
    }

    public function bestsellers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 8);

        $products = Product::active()
            ->where('tag', 'Best-seller')
            ->with('universe')
            ->limit($limit)
            ->get()
            ->map(fn($p) => $this->formatProduct($p));

        return response()->json(['products' => $products]);
    }

    private function storageUrl(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return Storage::disk('public')->url($path);
    }

    private function formatProduct(Product $product): array
    {
        $images = $product->images;

        if (is_array($images) && count($images) > 0) {
            $images = array_map(fn($img) => $this->storageUrl($img), $images);
        } else {
            $universeSlug = $product->universe?->slug ?? 'colliers';
            $images = [asset("images/products/{$universeSlug}.jpg")];
        }

        $variations = $product->variations ?? [];
        if (is_array($variations)) {
            $variations = array_map(function ($v) {
                if (isset($v['options']) && is_string($v['options'])) {
                    $v['options'] = array_map('trim', explode(',', $v['options']));
                }
                return $v;
            }, $variations);
        }

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'reference' => $product->reference,
            'description' => $product->description,
            'universe_id' => $product->universe_id,
            'price_ht' => $product->price_ht,
            'sale_price_ht' => $product->sale_price_ht,
            'retail_ttc' => $product->retail_ttc,
            'vat_rate' => $product->vat_rate,
            'moq' => $product->moq,
            'pack_size' => $product->pack_size,
            'stock' => $product->stock,
            'images' => $images,
            'material' => $product->material,
            'finish' => $product->finish,
            'quality_grade' => $product->quality_grade,
            'tag' => $product->tag,
            'variations' => $variations,
            'is_new' => $product->is_new,
            'active' => $product->active,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'universe' => $product->universe ? [
                'id' => $product->universe->id,
                'slug' => $product->universe->slug,
                'name' => $product->universe->name,
                'description' => $product->universe->description,
                'image_url' => $product->universe->image_url
                    ? $this->storageUrl($product->universe->image_url)
                    : asset("images/products/{$product->universe->slug}.jpg"),
                'display_order' => $product->universe->display_order,
            ] : null,
        ];
    }
}
