<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function hero(): JsonResponse
    {
        $content = ContentBlock::getByKey('hero');
        return response()->json(['content' => $content?->data ?? []]);
    }

    public function atelier(): JsonResponse
    {
        $content = ContentBlock::getByKey('atelier');
        return response()->json(['content' => $content?->data ?? []]);
    }

    public function testimonials(): JsonResponse
    {
        $testimonials = Testimonial::active()->ordered()->get();
        return response()->json(['testimonials' => $testimonials]);
    }

    public function faq(Request $request): JsonResponse
    {
        $query = FaqItem::active()->ordered();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        return response()->json(['faq' => $query->get()]);
    }

    public function settings(): JsonResponse
    {
        $settings = SiteSetting::all()->pluck('value', 'key');
        // Flatten nested settings (e.g. 'general' key)
        $flat = [];
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $flat[$k] = $v;
                }
            } else {
                $flat[$key] = $value;
            }
        }
        return response()->json(['settings' => $flat]);
    }
}
