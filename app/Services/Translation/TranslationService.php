<?php

namespace App\Services\Translation;

use App\Models\Translation;
use App\Models\TranslationBatch;
use App\Models\TranslationJob;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class TranslationService
{
    protected TranslatorInterface $translator;

    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator ?? $this->resolveTranslator();
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function translateModel(Model $model, string $targetLocale, ?string $sourceLocale = null): array
    {
        $sourceLocale = $sourceLocale ?? config('translation.default_locale', 'fr');
        $modelConfig = $this->getModelConfig($model);

        if (!$modelConfig) {
            return [];
        }

        $translated = [];

        foreach ($modelConfig['fields'] as $field) {
            $value = $model->{$field};

            if (empty($value)) {
                continue;
            }

            $existingTranslation = Translation::for(
                get_class($model),
                $model->getKey(),
                $targetLocale,
                $field
            );

            if ($existingTranslation) {
                $translated[$field] = $existingTranslation;
                continue;
            }

            try {
                if ($modelConfig['is_json'] ?? false) {
                    $data = is_string($value) ? json_decode($value, true) : $value;
                    $translatedValue = $this->translator->translateJson($data, $sourceLocale, $targetLocale);
                    $translatedValue = json_encode($translatedValue, JSON_UNESCAPED_UNICODE);
                } else {
                    $translatedValue = $this->translator->translate($value, $sourceLocale, $targetLocale);
                }

                Translation::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'locale' => $targetLocale,
                    'field' => $field,
                    'value' => $translatedValue,
                    'source' => 'auto',
                    'status' => 'completed',
                    'provider' => $this->translator->getName(),
                    'translated_at' => now(),
                ]);

                $translated[$field] = $translatedValue;

                Log::info("Translated {$field} for " . get_class($model) . " {$model->getKey()} to {$targetLocale}");
            } catch (Exception $e) {
                Log::error("Translation failed: {$e->getMessage()}");

                Translation::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'locale' => $targetLocale,
                    'field' => $field,
                    'value' => $value,
                    'source' => 'auto',
                    'status' => 'failed',
                    'provider' => $this->translator->getName(),
                ]);
            }
        }

        Translation::clearCache(get_class($model), $model->getKey());

        return $translated;
    }

    public function translateModelToAllLocales(Model $model, ?string $sourceLocale = null): void
    {
        $targetLocales = Locale::nonDefaultActive()->pluck('code')->toArray();

        foreach ($targetLocales as $locale) {
            $this->translateModel($model, $locale, $sourceLocale);
        }
    }

    public function getTranslation(Model $model, string $field, string $locale, ?string $fallbackValue = null): ?string
    {
        $translation = Translation::for(
            get_class($model),
            $model->getKey(),
            $locale,
            $field
        );

        if ($translation) {
            return $translation;
        }

        $defaultLocale = config('translation.default_locale', 'fr');
        if ($locale !== $defaultLocale) {
            $fallback = Translation::for(
                get_class($model),
                $model->getKey(),
                $defaultLocale,
                $field
            );

            if ($fallback) {
                return $fallback;
            }
        }

        return $fallbackValue ?? $model->{$field};
    }

    public function getTranslationsForModel(Model $model, string $locale): array
    {
        return Translation::allFor(get_class($model), $model->getKey(), $locale);
    }

    public function getModelWithTranslations(Model $model, string $locale): array
    {
        $data = $model->toArray();
        $modelConfig = $this->getModelConfig($model);

        if (!$modelConfig) {
            return $data;
        }

        $translations = $this->getTranslationsForModel($model, $locale);
        $defaultLocale = config('translation.default_locale', 'fr');

        foreach ($modelConfig['fields'] as $field) {
            if (isset($translations[$field])) {
                $value = $translations[$field];

                if ($modelConfig['is_json'] ?? false) {
                    $data[$field] = is_string($value) ? json_decode($value, true) : $value;
                } else {
                    $data[$field] = $value;
                }
            } elseif ($locale !== $defaultLocale) {
                $data['_translation_missing'] = true;
            }
        }

        if ($modelConfig['slug_field'] ?? false) {
            $slugField = $modelConfig['slug_field'];
            $slugs = $model->slugs ?? [];

            if (is_string($slugs)) {
                $slugs = json_decode($slugs, true) ?? [];
            }

            if (isset($slugs[$locale])) {
                $data['slug'] = $slugs[$locale];
            }
        }

        $data['_locale'] = $locale;

        return $data;
    }

    public function updateTranslation(Model $model, string $field, string $locale, string $value): void
    {
        $translation = Translation::where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();

        if ($translation) {
            $translation->update([
                'value' => $value,
                'source' => 'manual',
                'status' => 'completed',
                'translated_at' => now(),
            ]);
        } else {
            Translation::create([
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'locale' => $locale,
                'field' => $field,
                'value' => $value,
                'source' => 'manual',
                'status' => 'completed',
                'translated_at' => now(),
            ]);
        }

        Translation::clearCache(get_class($model), $model->getKey(), $locale);
    }

    public function createBatch(string $name, string $sourceLocale, string $targetLocale, array $models): TranslationBatch
    {
        $batch = TranslationBatch::create([
            'name' => $name,
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
            'total_items' => count($models),
        ]);

        foreach ($models as $model) {
            TranslationJob::create([
                'batch_id' => $batch->id,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'locale' => $targetLocale,
            ]);
        }

        return $batch;
    }

    public function getTranslationStatus(Model $model): array
    {
        $locales = Locale::activeCodes();
        $defaultLocale = config('translation.default_locale', 'fr');
        $status = [];

        foreach ($locales as $locale) {
            if ($locale === $defaultLocale) {
                continue;
            }

            $modelConfig = $this->getModelConfig($model);
            $totalFields = $modelConfig ? count($modelConfig['fields']) : 0;
            $translatedFields = Translation::where('model_type', get_class($model))
                ->where('model_id', $model->getKey())
                ->where('locale', $locale)
                ->where('status', 'completed')
                ->count();

            $status[$locale] = [
                'total' => $totalFields,
                'translated' => $translatedFields,
                'pending' => $totalFields - $translatedFields,
                'progress' => $totalFields > 0 ? round(($translatedFields / $totalFields) * 100, 2) : 0,
            ];
        }

        return $status;
    }

    protected function resolveTranslator(): TranslatorInterface
    {
        $provider = config('translation.provider', 'openai');

        return match ($provider) {
            'deepl' => new DeepLTranslator(),
            default => new OpenAITranslator(),
        };
    }

    protected function getModelConfig(Model $model): ?array
    {
        $models = config('translation.models', []);
        return $models[get_class($model)] ?? null;
    }
}
