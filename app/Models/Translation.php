<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'locale',
        'field',
        'value',
        'source',
        'status',
        'provider',
        'translated_at',
    ];

    protected function casts(): array
    {
        return [
            'translated_at' => 'datetime',
        ];
    }

    public function model(): BelongsTo
    {
        return $this->morphTo('model');
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class, 'locale', 'code');
    }

    public static function for(string $modelType, string $modelId, string $locale, string $field): ?string
    {
        $cacheKey = "translation:{$modelType}:{$modelId}:{$locale}:{$field}";

        return Cache::remember($cacheKey, config('translation.cache_ttl', 86400), function () use ($modelType, $modelId, $locale, $field) {
            $translation = static::where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('locale', $locale)
                ->where('field', $field)
                ->where('status', 'completed')
                ->first();

            return $translation?->value;
        });
    }

    public static function allFor(string $modelType, string $modelId, string $locale): array
    {
        $cacheKey = "translation:{$modelType}:{$modelId}:{$locale}:*";

        return Cache::remember($cacheKey, config('translation.cache_ttl', 86400), function () use ($modelType, $modelId, $locale) {
            return static::where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('locale', $locale)
                ->where('status', 'completed')
                ->pluck('value', 'field')
                ->toArray();
        });
    }

    public static function clearCache(string $modelType, string $modelId, ?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translation:{$modelType}:{$modelId}:{$locale}:*");
        }

        try {
            Cache::tags(['translations'])->flush();
        } catch (\Exception $e) {
            Cache::flush();
        }
    }

    public function markAsManual(): void
    {
        $this->update([
            'source' => 'manual',
            'translated_at' => now(),
        ]);
        self::clearCache($this->model_type, $this->model_id, $this->locale);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}
