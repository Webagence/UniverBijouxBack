<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\Translation\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    private function resolveImageUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        if (str_starts_with($url, '/storage/')) {
            return asset($url);
        }
        return asset('storage/' . ltrim($url, '/'));
    }

    private function resolveImageInArray(array $data, string $key = 'image'): array
    {
        if (isset($data[$key])) {
            $data[$key] = $this->resolveImageUrl($data[$key]);
        }
        return $data;
    }

    private function getLocaleData(string $contentKey): array
    {
        $locale = App::getLocale();
        $contentBlock = ContentBlock::getByKey($contentKey);

        if (!$contentBlock) {
            return [];
        }

        $translations = $this->translationService->getTranslationsForModel($contentBlock, $locale);

        if (!empty($translations['data'])) {
            $data = is_string($translations['data']) ? json_decode($translations['data'], true) : $translations['data'];
            return $this->resolveImageInArray($data);
        }

        return $this->resolveImageInArray($contentBlock->data ?? []);
    }

    public function hero(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('hero')]);
    }

    public function atelier(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('atelier')]);
    }

    public function testimonials(): JsonResponse
    {
        $locale = App::getLocale();
        $testimonials = Testimonial::active()->ordered()->get()->map(function ($testimonial) use ($locale) {
            $data = $testimonial->toArray();
            $translations = $this->translationService->getTranslationsForModel($testimonial, $locale);

            $data['author'] = $translations['author'] ?? $testimonial->author;
            $data['shop'] = $translations['shop'] ?? $testimonial->shop;
            $data['quote'] = $translations['quote'] ?? $testimonial->quote;
            $data['_locale'] = $locale;

            return $data;
        });

        return response()->json(['testimonials' => $testimonials]);
    }

    public function faq(Request $request): JsonResponse
    {
        $locale = App::getLocale();
        $query = FaqItem::active()->ordered();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $faq = $query->get()->map(function ($item) use ($locale) {
            $data = $item->toArray();
            $translations = $this->translationService->getTranslationsForModel($item, $locale);

            $data['question'] = $translations['question'] ?? $item->question;
            $data['answer'] = $translations['answer'] ?? $item->answer;
            $data['_locale'] = $locale;

            return $data;
        });

        return response()->json(['faq' => $faq]);
    }

    public function settings(): JsonResponse
    {
        $locale = App::getLocale();
        $settings = SiteSetting::all()->pluck('value', 'key');

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

        $flat['_locale'] = $locale;

        return response()->json(['settings' => $flat]);
    }

    public function promises(): JsonResponse
    {
        return response()->json(['promises' => $this->getLocaleData('promises')]);
    }

    public function categoriesSection(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('categories_section')]);
    }

    public function productGridSection(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('product_grid_section')]);
    }

    public function newByUniverseSection(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('new_by_universe_section')]);
    }

    public function testimonialsSection(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('testimonials_section')]);
    }

    public function legalPage(string $page): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData("legal_{$page}")]);
    }

    public function contactPage(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('contact_page')]);
    }

    public function faqPageHeader(): JsonResponse
    {
        return response()->json(['content' => $this->getLocaleData('faq_page_header')]);
    }
}
