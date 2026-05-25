<?php

namespace App\Traits;

use App\Jobs\TranslateModel;
use App\Models\Locale;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

trait Translatable
{
    protected static function bootTranslatable(): void
    {
        static::created(function (Model $model) {
            $model->queueTranslations();
        });

        static::updated(function (Model $model) {
            if ($model->isTranslationDirty()) {
                $model->queueTranslations();
            }
        });

        static::deleted(function (Model $model) {
            Translation::where('model_type', get_class($model))
                ->where('model_id', $model->getKey())
                ->delete();
        });
    }

    public function queueTranslations(): void
    {
        if (!config('translation.auto_translate', true)) {
            return;
        }

        $targetLocales = Locale::nonDefaultActive()->pluck('code')->toArray();

        foreach ($targetLocales as $locale) {
            TranslateModel::dispatch(
                get_class($this),
                $this->getKey(),
                $locale,
                config('translation.default_locale', 'fr')
            )->delay(now()->addSeconds(5));
        }
    }

    public function isTranslationDirty(): bool
    {
        $modelConfig = config('translation.models.' . get_class($this));

        if (!$modelConfig) {
            return false;
        }

        foreach ($modelConfig['fields'] as $field) {
            if ($this->isDirty($field)) {
                return true;
            }
        }

        return false;
    }

    public function getTranslation(string $field, string $locale, ?string $fallback = null): ?string
    {
        return app(\App\Services\Translation\TranslationService::class)
            ->getTranslation($this, $field, $locale, $fallback);
    }

    public function getTranslationsForLocale(string $locale): array
    {
        return app(\App\Services\Translation\TranslationService::class)
            ->getTranslationsForModel($this, $locale);
    }

    public function toArrayWithLocale(string $locale): array
    {
        return app(\App\Services\Translation\TranslationService::class)
            ->getModelWithTranslations($this, $locale);
    }

    public function getTranslationStatus(): array
    {
        return app(\App\Services\Translation\TranslationService::class)
            ->getTranslationStatus($this);
    }

    public function updateTranslation(string $field, string $locale, string $value): void
    {
        app(\App\Services\Translation\TranslationService::class)
            ->updateTranslation($this, $field, $locale, $value);
    }
}
