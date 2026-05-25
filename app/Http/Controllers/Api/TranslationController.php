<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\TranslateBatch;
use App\Models\Locale;
use App\Models\Translation;
use App\Models\TranslationBatch;
use App\Services\Translation\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TranslationController extends Controller
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    public function locales(): JsonResponse
    {
        $locales = Locale::active()->get()->map(function ($locale) {
            return [
                'code' => $locale->code,
                'name' => $locale->name,
                'native_name' => $locale->native_name,
                'flag_emoji' => $locale->flag_emoji,
                'is_default' => $locale->is_default,
                'direction' => $locale->direction,
            ];
        });

        return response()->json(['locales' => $locales]);
    }

    public function getCurrentLocale(): JsonResponse
    {
        return response()->json([
            'locale' => App::getLocale(),
            'fallback_locale' => config('app.fallback_locale'),
        ]);
    }

    public function setLocale(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|in:' . implode(',', Locale::activeCodes()),
        ]);

        $locale = $request->input('locale');
        App::setLocale($locale);

        return response()->json([
            'locale' => $locale,
            'message' => "Locale set to {$locale}",
        ]);
    }

    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string',
            'source_locale' => 'required|string',
            'target_locale' => 'required|string',
        ]);

        try {
            $translated = $this->translationService->getTranslator()->translate(
                $request->input('text'),
                $request->input('source_locale'),
                $request->input('target_locale')
            );

            return response()->json([
                'original' => $request->input('text'),
                'translated' => $translated,
                'source_locale' => $request->input('source_locale'),
                'target_locale' => $request->input('target_locale'),
                'provider' => $this->translationService->getTranslator()->getName(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getModelTranslations(Request $request, string $modelType, string $modelId): JsonResponse
    {
        $request->validate([
            'locale' => 'sometimes|string',
        ]);

        $locale = $request->input('locale', App::getLocale());
        $model = $this->resolveModel($modelType, $modelId);

        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $translations = $this->translationService->getTranslationsForModel($model, $locale);
        $status = $this->translationService->getTranslationStatus($model);

        return response()->json([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'locale' => $locale,
            'translations' => $translations,
            'status' => $status,
        ]);
    }

    public function updateTranslation(Request $request, string $modelType, string $modelId): JsonResponse
    {
        $request->validate([
            'field' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
        ]);

        $model = $this->resolveModel($modelType, $modelId);

        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $this->translationService->updateTranslation(
            $model,
            $request->input('field'),
            $request->input('locale'),
            $request->input('value')
        );

        return response()->json([
            'message' => 'Translation updated successfully',
            'field' => $request->input('field'),
            'locale' => $request->input('locale'),
        ]);
    }

    public function translateModel(Request $request, string $modelType, string $modelId): JsonResponse
    {
        $request->validate([
            'target_locale' => 'required|string',
            'source_locale' => 'sometimes|string',
        ]);

        $model = $this->resolveModel($modelType, $modelId);

        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $sourceLocale = $request->input('source_locale', config('translation.default_locale', 'fr'));
        $targetLocale = $request->input('target_locale');

        \App\Jobs\TranslateModel::dispatch(
            $modelType,
            $modelId,
            $targetLocale,
            $sourceLocale
        );

        return response()->json([
            'message' => 'Translation job dispatched',
            'model_type' => $modelType,
            'model_id' => $modelId,
            'target_locale' => $targetLocale,
        ]);
    }

    public function translateAllModels(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => 'required|string',
            'target_locale' => 'required|string',
            'source_locale' => 'sometimes|string',
        ]);

        $modelType = $request->input('model_type');
        $targetLocale = $request->input('target_locale');
        $sourceLocale = $request->input('source_locale', config('translation.default_locale', 'fr'));

        if (!class_exists($modelType)) {
            return response()->json(['error' => 'Invalid model type'], 400);
        }

        $models = $modelType::cursor();
        $count = 0;

        foreach ($models as $model) {
            \App\Jobs\TranslateModel::dispatch(
                $modelType,
                $model->getKey(),
                $targetLocale,
                $sourceLocale
            );
            $count++;
        }

        return response()->json([
            'message' => "Dispatched {$count} translation jobs",
            'model_type' => $modelType,
            'target_locale' => $targetLocale,
            'count' => $count,
        ]);
    }

    public function getBatches(Request $request): JsonResponse
    {
        $batches = TranslationBatch::withCount('jobs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['batches' => $batches]);
    }

    public function getBatch(string $batchId): JsonResponse
    {
        $batch = TranslationBatch::with('jobs')->find($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json([
            'batch' => $batch,
            'progress' => $batch->progress(),
        ]);
    }

    public function createBatch(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'model_type' => 'required|string',
            'target_locale' => 'required|string',
            'source_locale' => 'sometimes|string',
        ]);

        $modelType = $request->input('model_type');
        $targetLocale = $request->input('target_locale');
        $sourceLocale = $request->input('source_locale', config('translation.default_locale', 'fr'));

        if (!class_exists($modelType)) {
            return response()->json(['error' => 'Invalid model type'], 400);
        }

        $models = $modelType::all();

        $batch = $this->translationService->createBatch(
            $request->input('name'),
            $sourceLocale,
            $targetLocale,
            $models->toArray()
        );

        TranslateBatch::dispatch($batch->id);

        return response()->json([
            'message' => 'Translation batch created and dispatched',
            'batch_id' => $batch->id,
            'total_items' => $batch->total_items,
        ]);
    }

    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => 'sometimes|string',
            'model_id' => 'sometimes|string',
        ]);

        if ($request->has('model_type') && $request->has('model_id')) {
            Translation::clearCache($request->input('model_type'), $request->input('model_id'));
        } else {
            \Illuminate\Support\Facades\Cache::tags(['translations'])->flush();
        }

        return response()->json(['message' => 'Translation cache cleared']);
    }

    protected function resolveModel(string $modelType, string $modelId): ?\Illuminate\Database\Eloquent\Model
    {
        if (!class_exists($modelType)) {
            return null;
        }

        return $modelType::find($modelId);
    }
}
