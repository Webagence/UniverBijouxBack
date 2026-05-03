<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Ticket;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function accounts(): JsonResponse
    {
        $accounts = User::whereHas('roles', fn ($q) => $q->where('name', 'pro'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($accounts);
    }

    public function toggleApproveAccount(string $id, Request $request): JsonResponse
    {
        $account = User::findOrFail($id);
        $account->update(['approved' => $request->boolean('approved', !$account->approved)]);

        return response()->json(['message' => 'Account updated', 'user' => $account]);
    }

    public function deleteAccount(string $id): JsonResponse
    {
        $account = User::findOrFail($id);
        $account->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    public function orders(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($orders);
    }

    public function updateOrderStatus(string $id, Request $request): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    public function products(): JsonResponse
    {
        $products = Product::with('universe')->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'price_ht' => 'required|numeric|min:0',
            'universe_id' => 'nullable|uuid|exists:universes,id',
            'variations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create($request->all());
        return response()->json(['product' => $product], 201);
    }

    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json(['product' => $product]);
    }

    public function deleteProduct(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function universes(): JsonResponse
    {
        $universes = Universe::withCount('products')->orderBy('display_order')->get();
        return response()->json($universes);
    }

    public function storeUniverse(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:universes,slug',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $universe = Universe::create($request->all());
        return response()->json(['universe' => $universe], 201);
    }

    public function updateUniverse(string $id, Request $request): JsonResponse
    {
        $universe = Universe::findOrFail($id);
        $universe->update($request->all());
        return response()->json(['universe' => $universe]);
    }

    public function deleteUniverse(string $id): JsonResponse
    {
        $universe = Universe::findOrFail($id);
        $universe->delete();
        return response()->json(['message' => 'Universe deleted']);
    }

    public function syncTestimonials(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'testimonials' => 'required|array',
            'testimonials.*.author' => 'required|string',
            'testimonials.*.quote' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Testimonial::truncate();

        foreach ($request->testimonials as $i => $data) {
            Testimonial::create([
                'author' => $data['author'],
                'role' => $data['shop'] ?? '',
                'shop' => $data['shop'] ?? '',
                'quote' => $data['quote'],
                'rating' => $data['rating'] ?? 5,
                'display_order' => $i,
                'active' => true,
            ]);
        }

        return response()->json(['message' => 'Testimonials synced']);
    }

    public function syncFaq(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'faq' => 'required|array',
            'faq.*.question' => 'required|string',
            'faq.*.answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        FaqItem::truncate();

        foreach ($request->faq as $i => $data) {
            FaqItem::create([
                'question' => $data['question'],
                'answer' => $data['answer'],
                'category' => 'general',
                'display_order' => $i,
                'active' => true,
            ]);
        }

        return response()->json(['message' => 'FAQ synced']);
    }

    public function updateHero(Request $request): JsonResponse
    {
        $block = ContentBlock::updateOrCreate(
            ['key' => 'hero'],
            ['data' => $request->all()]
        );

        return response()->json(['content' => $block->data]);
    }

    public function updateAtelier(Request $request): JsonResponse
    {
        $block = ContentBlock::updateOrCreate(
            ['key' => 'atelier'],
            ['data' => $request->all()]
        );

        return response()->json(['content' => $block->data]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $setting = SiteSetting::updateOrCreate(
            ['key' => 'general'],
            ['value' => $request->all()]
        );

        return response()->json(['settings' => $setting->value]);
    }

    public function tickets(Request $request): JsonResponse
    {
        $query = Ticket::with(['user', 'order']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($tickets);
    }
}
